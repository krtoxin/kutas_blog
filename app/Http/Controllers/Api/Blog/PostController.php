<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index()
    {
        return BlogPost::with(['user', 'category'])->orderBy('created_at', 'desc')->get();
    }

    public function show($id)
    {
        $post = BlogPost::with(['user', 'category'])->findOrFail($id);
        return response()->json($post);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'slug' => [
                'nullable',
                'string',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('blog_posts', 'slug')
            ],
            'category_id' => 'required|exists:blog_categories,id',
            'excerpt' => 'nullable|string',
            'content_raw' => 'required|string|min:10',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = $this->generateSlug($validated['title']);
        }

        $validated['user_id'] = 1;

        if ($validated['is_published'] && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);
        $post->load(['user', 'category']);

        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        $post = BlogPost::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'slug' => [
                'nullable',
                'string',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('blog_posts', 'slug')->ignore($post->id)
            ],
            'category_id' => 'required|exists:blog_categories,id',
            'excerpt' => 'nullable|string',
            'content_raw' => 'required|string|min:10',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = $this->generateSlug($validated['title'], $post->id);
        }

        if ($validated['is_published'] && !$post->is_published && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post->update($validated);
        $post->load(['user', 'category']);

        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        return response()->json(['message' => 'Пост успішно видалено']);
    }

    private function generateSlug($title, $ignoreId = null)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = BlogPost::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
