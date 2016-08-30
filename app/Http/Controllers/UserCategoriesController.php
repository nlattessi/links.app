<?php

namespace App\Http\Controllers;

use App\Category;
use App\User;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserCategoriesController extends Controller
{
    public function index($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        // TODO: CHECK USER FOR TOKEN AND SEE IF MATCH

        return $this->collection(
            $user->categories()->get(),
            new CategoryTransformer()
        );
    }

    public function show($uid, $cid)
    {
        $user = User::where('uuid', $uid)->firstOrFail();

        // TODO: CHECK USER FOR TOKEN AND SEE IF MATCH

        $category = $user->categories()->where('uuid', $cid)->firstOrFail();

        return $this->item(
            $category,
            new CategoryTransformer()
        );
    }

    // public function store(Request $request)
    // {
    //     $this->validateCategory($request);

    //     $category = Category::create($request->all());

    //     return response()->json(
    //         $this->item($category, new CategoryTransformer()),
    //         Response::HTTP_CREATED,
    //         ['Location' => route('categories.show', ['uuid' => $category->uuid])]
    //     );
    // }

    // public function update(Request $request, $uuid)
    // {
    //     $this->validateCategory($request);

    //     $category = Category::where('uuid', $uuid)->firstOrFail();

    //     $category->fill($request->all());
    //     $category->save();

    //     return response()->json(
    //         $this->item($category, new CategoryTransformer()),
    //         Response::HTTP_OK
    //     );
    // }

    // public function destroy($uuid)
    // {
    //     Category::where('uuid', $uuid)->firstOrFail()->delete();

    //     return response(null, Response::HTTP_NO_CONTENT);
    // }

    // private function validateCategory(Request $request)
    // {
    //     $this->validate($request, [
    //         'name' => 'required|max:255',
    //     ]);
    // }
}