<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;

class PostController extends Controller
{
    public function index()
    {
        return BlogPost::with(['user', 'category'])->get();
    }
}
