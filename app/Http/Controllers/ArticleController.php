<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Article::class, 'article');
    }
    /**
     * Display a listing of the resource.
     */
    public function index():View
    {
        $articles = Article::with(['user', 'tags'])->latest()->simplePaginate(10);

        return view('articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create():View
    {

        return view('articles.create', $this->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request)
    {
        // $article = Article::create([
        //     'title' => $request->title,
        //     'excerpt' => $request->excerpt,
        //     'description' => $request->description,
        //     'slug' => Str::slug($request->title),
        //     'user_id' => auth()->id(),
        //     'status' => $request->status === 'on',
        //     'category_id' => $request->category_id

        // ]);

        $article = Article::create([
            'slug' => Str::slug($request->title),
            'user_id' => auth()->id(),
            'status' => $request->status === 'on',
        ] + $request->validated());

        $article->tags()->attach($request->tags);

        return redirect(route('articles.index'))
                    ->with('message', 'Article has successfully been created');

    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article):View
    {
         return view('articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {

        return view('articles.edit', array_merge(compact('article'), $this->getFormData()));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $article->update($request->validated() + [
            'slug' => Str::slug($request->title)
        ]);

        $article->tags()->sync($request->tags);

        return redirect(route('dashboard'))->with('message', 'Article has successfully been updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();
        return redirect(route('dashboard'))->with('message', 'Aritcle has successfully been deleted');
    }

    private function getFormData ():array
    {
        $categories = Category::pluck('name', 'id');
        $tags = Tag::pluck('name', 'id');

        return compact('categories', 'tags');
    }
}
