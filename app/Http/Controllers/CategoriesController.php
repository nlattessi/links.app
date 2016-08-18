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

    public function show($uuid)
    {
        return $this->item(
            Category::where('uuid', $uuid)->firstOrFail(),
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
            ['Location' => route('categories.show', ['uuid' => $category->uuid])]
        );
    }

    public function update(Request $request, $uuid)
    {
        $this->validateCategory($request);

        $category = Category::where('uuid', $uuid)->firstOrFail();

        $category->fill($request->all());
        $category->save();

        return response()->json(
            $this->item($category, new CategoryTransformer()),
            Response::HTTP_OK
        );
    }

    public function destroy($uuid)
    {
        Category::where('uuid', $uuid)->firstOrFail()->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function validateCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ]);
    }
}