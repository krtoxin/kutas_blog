<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Models\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::with('parentCategory')->get();
        return response()->json($categories);
    }

    public function show($id)
    {
        $category = BlogCategory::with('parentCategory')->findOrFail($id);
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug',
            'parent_id' => 'nullable|exists:blog_categories,id',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $originalSlug = $validated['slug'];
        $counter = 1;
        while (BlogCategory::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $category = BlogCategory::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug,' . $id,
            'parent_id' => 'nullable|exists:blog_categories,id',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $originalSlug = $validated['slug'];
        $counter = 1;
        while (BlogCategory::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $category->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = BlogCategory::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Категорію видалено']);
    }
}
