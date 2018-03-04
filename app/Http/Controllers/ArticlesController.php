<?php

namespace App\Http\Controllers;

use App\Article;

class ArticlesController extends Controller
{
    public function all()
    {
        return Article::all();
    }

    public function find($id)
    {
        return Article::find($id);
    }

    public function delete($id)
    {
        return Article::destroy($id);
    }
}
