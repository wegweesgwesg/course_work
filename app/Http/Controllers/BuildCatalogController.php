<?php

namespace App\Http\Controllers;

use App\Models\PublishedBuild;
use App\Models\BuildVote;
use App\Models\BuildComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BuildCatalogController extends Controller
{
    // Catalog page
    public function index()
    {
        return view('builds.index');
    }

    // Build detail page
    public function show(int $id)
    {
        $build = PublishedBuild::with(['user', 'comments.user'])->findOrFail($id);
        $build->score = $build->votes()->sum('vote');
        $build->vote_count = $build->votes()->count();
        $userVote = null;
        if (Auth::check()) {
            $v = BuildVote::where('published_build_id', $id)->where('user_id', Auth::id())->first();
            $userVote = $v ? $v->vote : null;
        }

        $buildData = $build->build_data;
        if (is_array($buildData)) {
            $productIds = array_values(array_filter(array_map(
                fn($item) => $item['product_id'] ?? null,
                $buildData
            )));
            if ($productIds) {
                $imagePaths = \App\Models\Product::whereIn('product_id', $productIds)
                    ->pluck('main_image_path', 'product_id');
                foreach ($buildData as $slot => $item) {
                    if (empty($item['main_image_path']) && !empty($item['product_id'])) {
                        $buildData[$slot]['main_image_path'] = $imagePaths[$item['product_id']] ?? '';
                    }
                }
                $build->build_data = $buildData;
            }
        }

        return view('builds.show', compact('build', 'userVote'));
    }

    // GET /api/builds
    public function apiIndex(Request $request)
    {
        $search = $request->input('search');
        $component = $request->input('component');
        $sort = $request->input('sort', 'newest');
        $section = $request->input('section');

        $query = PublishedBuild::query()
            ->select('published_builds.*')
            ->addSelect(DB::raw('(SELECT COALESCE(SUM(bv.vote), 0) FROM build_votes bv WHERE bv.published_build_id = published_builds.id) as score'));

        if ($search) {
            $query->where('published_builds.name', 'like', '%' . $search . '%');
        }

        if ($component) {
            foreach (array_map('trim', explode(',', $component)) as $pid) {
                $query->whereRaw("published_builds.build_data LIKE ?", ['%"product_id":"' . $pid . '"%']);
            }
        }

        if ($section === 'admin') {
            $query->join('users', 'users.id', '=', 'published_builds.user_id')
                ->whereIn('users.role', ['admin', 'content_manager', 'warehouse_manager']);
        }

        if ($sort === 'popular' || $section === 'popular') {
            $query->orderByDesc(DB::raw('(SELECT COALESCE(SUM(bv2.vote), 0) FROM build_votes bv2 WHERE bv2.published_build_id = published_builds.id)'));
        } else {
            $query->orderByDesc('published_builds.created_at');
        }

        $limit = $request->input('limit', 30);
        $builds = $query->limit((int) min($limit, 100))->get();

        $result = $builds->map(function ($b) {
            return [
                'id' => $b->id,
                'name' => $b->name,
                'description' => $b->description,
                'build_data' => $b->build_data,
                'total_price' => $b->total_price,
                'score' => (int) $b->score,
                'comments_count' => $b->comments()->count(),
                'user' => [
                    'id' => $b->user->id ?? null,
                    'first_name' => $b->user->first_name ?? '',
                    'role' => $b->user->role ?? 'user',
                ],
                'created_at' => $b->created_at->toDateTimeString(),
            ];
        });

        return response()->json($result);
    }

    // POST /api/builds
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'build_data' => 'required|array',
            'total_price' => 'required|integer|min:0',
        ]);

        $build = PublishedBuild::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'build_data' => $validated['build_data'],
            'total_price' => $validated['total_price'],
        ]);

        return response()->json(['success' => true, 'id' => $build->id], 201);
    }

    // DELETE /api/builds/{id}
    public function destroy(int $id)
    {
        $build = PublishedBuild::findOrFail($id);
        $build->delete();
        return response()->json(['success' => true]);
    }

    // POST /api/builds/{id}/vote
    public function vote(Request $request, int $id)
    {
        $validated = $request->validate([
            'vote' => 'required|integer|in:-1,1',
        ]);

        PublishedBuild::findOrFail($id);

        $existing = BuildVote::where('published_build_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            if ($existing->vote === $validated['vote']) {
                $existing->delete();
            } else {
                $existing->update(['vote' => $validated['vote']]);
            }
        } else {
            BuildVote::create([
                'published_build_id' => $id,
                'user_id' => Auth::id(),
                'vote' => $validated['vote'],
            ]);
        }

        $newScore = BuildVote::where('published_build_id', $id)->sum('vote');
        $userVote = BuildVote::where('published_build_id', $id)->where('user_id', Auth::id())->value('vote');

        return response()->json(['score' => (int) $newScore, 'user_vote' => $userVote]);
    }

    // POST /api/builds/{id}/comment
    public function comment(Request $request, int $id)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:2000',
        ]);

        PublishedBuild::findOrFail($id);

        $comment = BuildComment::create([
            'published_build_id' => $id,
            'user_id' => Auth::id(),
            'text' => $validated['text'],
        ]);

        $comment->load('user');

        return response()->json([
            'id' => $comment->id,
            'text' => $comment->text,
            'user' => [
                'id' => $comment->user->id,
                'first_name' => $comment->user->first_name,
            ],
            'created_at' => $comment->created_at->toDateTimeString(),
        ], 201);
    }

    // DELETE /api/builds/comments/{id}
    public function destroyComment(int $id)
    {
        $comment = BuildComment::findOrFail($id);
        $comment->delete();
        return response()->json(['success' => true]);
    }
}
