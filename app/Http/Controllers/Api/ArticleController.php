<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['source', 'category', 'author']);

        // Apply Search
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('content', 'like', "%{$keyword}%");
            });
        }

        // Apply Filters
        if ($request->filled('date')) {
            // Expected format YYYY-MM-DD
            $query->whereDate('published_at', $request->input('date'));
        }

        if ($request->filled('category')) {
            $category = $request->input('category');
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        if ($request->filled('source')) {
            $source = $request->input('source');
            $query->whereHas('source', function ($q) use ($source) {
                $q->where('name', $source);
            });
        }

        $articles = $query->latest('published_at')->paginate(15);

        return response()->json($articles);
    }

    public function metadata()
    {
        $sources = \App\Models\Source::pluck('name', 'id');
        $categories = \App\Models\Category::pluck('name', 'id');
        $authors = \App\Models\Author::pluck('name', 'id');

        return response()->json([
            'sources' => $sources,
            'categories' => $categories,
            'authors' => $authors
        ]);
    }
}
