<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::where('is_published', true)
                     ->latest('published_at')
                     ->paginate(10);
                     
        return view('frontend.pages.blog', compact('posts'));
    }

    public function show($slug)
    {
        $post = Post::where('slug', $slug)
                    ->where('is_published', true)
                    ->firstOrFail();
                    
        return view('frontend.pages.standard-post-type', compact('post'));
    }
}
