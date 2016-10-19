<?php

namespace App\Http\Controllers;

use Auth;
use App\Category;
use App\User;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserCategoriesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->has('order')) {
            // TODO: Validate sort(array) params
            preg_match_all('/\(([^\)]+)\)?/', $request->input('order'), $sortArr);
            list($orderCol, $orderBy) = explode('|', $sortArr[1][0]);

            $categories = $user->categories()
                ->orderBy($orderCol, $orderBy)
                ->get();
        }

        return $this->collection(
            $categories ?? $user->categories,
            new CategoryTransformer()
        );
    }

    public function show($uuid)
    {
        $user = Auth::user();

        return $this->item(
            $user->categories()->where('uuid', $uuid)->firstOrFail(),
            new CategoryTransformer()
        );
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $this->validateCategory($request);

        $category = $user->categories()->create([
            'name' => $request->input('name'),
        ]);

        return response()->json(
            $this->item($category, new CategoryTransformer()),
            Response::HTTP_CREATED,
            ['Location' => route('userCategories.show', ['uuid' => $category->uuid])]
        );
    }

    public function update(Request $request, $uuid)
    {
        $user = Auth::user();

        $this->validateUpdateCategory($request);

        $category = $user->categories()->where('uuid', $uuid)->firstOrFail();

        $category->fill(
            $request->all()
        );
        $category->save();

        return response()->json(
            $this->item($category, new CategoryTransformer()),
            Response::HTTP_OK
        );
    }

    public function destroy($uuid)
    {
        $user = Auth::user();

        $user->categories()->where('uuid', $uuid)->firstOrFail()->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function validateCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ]);
    }

    private function validateUpdateCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'max:255',
        ]);
    }
}
