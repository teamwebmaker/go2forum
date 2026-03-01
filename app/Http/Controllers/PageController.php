<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\PublicDocument;

class PageController extends Controller
{
    public function home()
    {
        $banner = Banner::query()
            ->forKey(Banner::HOME_KEY)
            ->visible()
            ->first();

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

        return view('pages.home', compact('banner', 'categories', 'documents'));
    }
}
