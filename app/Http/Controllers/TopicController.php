<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Category;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function category(Category $category, Request $request)
    {
        $search = $request->input('search');
        $scope = $request->input('scope', 'all');
        $user = $request->user();

        // Only allow "mine" scope when authenticated; otherwise fall back to "all"
        $isMineScope = $scope === 'mine' && $user;

        $topics = Topic::query()
            ->with(['user', 'category:id,name'])
            ->visible()
            ->where('category_id', $category->id)
            ->when($isMineScope, fn($q) => $q->where('user_id', $user->id))
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%"))
            ->orderByDesc('pinned')
            ->orderByDesc('messages_count')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('topics.category', compact('topics', 'search', 'category', 'scope'));
    }

    public function show(Topic $topic)
    {
        return view('topics.show', compact('topic'));
    }
}
