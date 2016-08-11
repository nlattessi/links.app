<?php

namespace App\Http\Controllers;

use App\Category;
use App\Transformers\CategoryTransformer;
// use Illuminate\Database\Eloquent\ModelNotFoundException;
// use Illuminate\Http\Request;
// use Illuminate\Http\Response;

class CategoriesController extends Controller
{
    public function index()
    {
        return $this->collection(
            Category::all(),
            new CategoryTransformer()
        );
    }

    public function show($id)
    {
        return $this->item(
            Category::findOrFail($id),
            new CategoryTransformer()
        );
    }
}