<?php

namespace App\Http\Controllers;

use App\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class ArticlesController
 * @package App\Http\Controllers
 */
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
        $article = new Article;
        $article->user_id = Auth::user()->id;
        $article->title = $request->input('title');
        $article->text = $request->input('text');

        $article->save();

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
