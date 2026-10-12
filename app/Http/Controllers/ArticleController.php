<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function publicIndex(): View
    {
        $articles = Article::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->with('author')
            ->latest('published_at')
            ->paginate(12);

        return view('storefront.articles.index', compact('articles'));
    }

    public function publicShow(string $slug): View
    {
        $article = Article::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->with('author')
            ->firstOrFail();

        $relatedArticles = Article::query()
            ->where('id', '!=', $article->id)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->with('author')
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('storefront.articles.show', compact('article', 'relatedArticles'));
    }

    public function adminIndex(): View
    {
        $articles = Article::query()->with('author')->latest()->paginate(20);

        return view('admin.articles.index', compact('articles'));
    }

    public function create(): View
    {
        return view('admin.articles.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateArticle($request);
        $image = $data['image'] ?? null;
        unset($data['image'], $data['is_published']);

        $isPublished = $request->boolean('is_published');
        $data['author_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['image_path'] = $image?->store('articles', 'public');
        $data['is_published'] = $isPublished;
        $data['published_at'] = $isPublished ? now() : null;

        Article::create($data);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil dibuat.');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.form', compact('article'));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $data = $this->validateArticle($request);
        $image = $data['image'] ?? null;
        unset($data['image'], $data['is_published']);

        $isPublished = $request->boolean('is_published');
        if ($image) {
            if ($article->image_path) {
                Storage::disk('public')->delete($article->image_path);
            }
            $data['image_path'] = $image->store('articles', 'public');
        }
        if ($article->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $article);
        }
        $data['is_published'] = $isPublished;
        $data['published_at'] = $isPublished ? ($article->published_at ?? now()) : null;
        $article->update($data);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        if ($article->image_path) {
            Storage::disk('public')->delete($article->image_path);
        }
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil dihapus.');
    }

    private function validateArticle(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:50000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $title, ?Article $article = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $suffix = 2;

        while (Article::query()->where('slug', $slug)->when($article, fn ($query) => $query->where('id', '!=', $article->id))->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}