<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PublicDocument;

class PageController extends Controller
{
    public function home()
    {
        $categories = Category::with([
            'ad' => fn($q) => $q->visible(),
        ])
            ->visible()
            ->orderBy('order')
            ->get();

        $documents = PublicDocument::query()
            ->visible()
            ->orderBy('order')
            ->get();

        return view('pages.home', compact('categories', 'documents'));
    }
}
