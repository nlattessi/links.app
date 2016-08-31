<?php

namespace App\Http\Controllers;

use Auth;
use App\Link;
use App\Category;
use App\User;
use App\Transformers\LinkTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserLinksController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $links = $user->categories->flatMap(function ($category) {
            return $category->links;
        });

        return $this->collection(
            $links,
            new LinkTransformer()
        );
    }

    public function show($uuid)
    {
        $user = Auth::user();

        $ids = $user->categories->map(function ($category) {
            return $category->id;
        })->all();

        return $this->item(
            Link::where('uuid', $uuid)->whereIn('category_id', $ids)->firstOrFail(),
            new LinkTransformer()
        );
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $this->validateLink($request);

        $category = Category::where('uuid', $request->input('category'))
            ->where('user_id', $user->id)
            ->firstOrFail();

        $link = $category->links()->create(
            $this->getLinkData($request, $user)
        );

        return response()->json(
            $this->item($link, new LinkTransformer()),
            Response::HTTP_CREATED,
            ['Location' => route('UserLinks.show', ['uuid' => $link->uuid])]
        );
    }

    // public function update(Request $request, $uuid)
    // {
    //     $user = Auth::user();

    //     $this->validateUpdateCategory($request);

    //     $category = $user->categories()->where('uuid', $uuid)->firstOrFail();

    //     $category->fill(
    //         $request->all()
    //     );
    //     $category->save();

    //     return response()->json(
    //         $this->item($category, new LinkTransformer()),
    //         Response::HTTP_OK
    //     );
    // }

    // public function destroy($uuid)
    // {
    //     $user = Auth::user();

    //     $user->categories()->where('uuid', $uuid)->firstOrFail()->delete();

    //     return response(null, Response::HTTP_NO_CONTENT);
    // }

    private  function getLinkData(Request $request, User $user)
    {
        return [
            'title' => $request->input('title'),
            'url' => $request->input('url'),
        ];
    }

    private function validateLink(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'url' => 'required|max:255',
            'category' => 'required|regex:/^' . env('UUID_REGEX') . '$/|exists:categories,uuid',
        ]);
    }

    // private function validateUpdateCategory(Request $request)
    // {
    //     $this->validate($request, [
    //         'name' => 'max:255',
    //     ]);
    // }
}