<?php

namespace App\Http\Controllers;

use App\Category;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

    public function store(Request $request)
    {
        $this->validateCategory($request);

        $category = Category::create($request->all());

        return response()->json(
            $this->item($category, new CategoryTransformer()),
            Response::HTTP_CREATED,
            ['Location' => route('categories.show', ['id' => $category->id])]
        );
    }

    public function update(Request $request, $id)
    {
        $this->validateCategory($request);

        $category = Category::findOrFail($id);

        $category->fill($request->all());
        $category->save();

        return response()->json(
            $this->item($category, new CategoryTransformer()),
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function validateCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ]);
    }
}