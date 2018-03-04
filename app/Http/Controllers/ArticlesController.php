<?php

namespace App\Http\Controllers;

use App\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(Article::all());
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function find($id)
    {
        return response()->json(Article::find($id));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $article = Article::create($request->all());

        return response()->json($article);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $article = Article::find($id);
        $article->title = $request->input('title');
        $article->text = $request->input('text');
        $article->save();

        return response()->json($article);
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
        Article::destroy($id);
    }
}
