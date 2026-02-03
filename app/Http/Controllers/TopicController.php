<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Category;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function category(Category $category, Request $request)
    {
        abort_unless($category->visibility, 404);

        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'scope' => ['nullable', 'in:all,mine'],
        ]);

        $search = $data['search'] ?? null;
        $scope = $data['scope'] ?? 'all';
        $user = $request->user();

        $isMineScope = $scope === 'mine' && $user;

        $topics = Topic::query()
            ->with([
                'user:id,name,surname,is_expert,is_top_commentator',
                'category:id,name',
            ])
            ->visible()
            ->where('category_id', $category->id)
            ->when($isMineScope, fn($q) => $q->where('user_id', $user->id))
            ->when($search, function ($q) use ($search) {
                $escaped = addcslashes($search, '%_\\');
                $q->where('title', 'like', "%{$escaped}%");
            })
            ->orderByDesc('pinned')
            ->orderByDesc('messages_count')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('topics.category', compact('topics', 'search', 'category', 'scope'));
    }

    public function show(Topic $topic)
    {
        // Load category
        $topic->load('category');

        // Abort if topic or category is not visible
        abort_unless($topic->visibility, 404);
        abort_unless($topic->category?->visibility, 404);

        return view('topics.show', compact('topic'));
    }

    public function store(Category $category, Request $request)
    {
        // Route already has auth + verified.full
        abort_unless($category->visibility, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $topic = Topic::create([
            'user_id' => $request->user()->id,
            'category_id' => $category->id,
            'title' => $data['title'],
        ]);

        return redirect()->route('topics.show', $topic->slug);
    }
}
