<?php

namespace App\Http\Controllers;

use Auth;
use App\Link;
use App\Category;
use App\User;
use App\Transformers\LinkTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserLinksController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $links = $this->getLinksFromUser($user);

        return $this->collection(
            $links,
            new LinkTransformer()
        );
    }

    public function show($uuid)
    {
        $user = Auth::user();

        $link = $this->getLinkFromUserFilterByUuid($user, $uuid);

        return $this->item(
            $link,
            new LinkTransformer()
        );
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $this->validateLink($request);

        $category = $this->getCategoryFromUserFilterByUuid(
            $user,
            $request->input('category')
        );

        $link = $category->links()->create([
            'title' => $request->input('title'),
            'url' => $request->input('url'),
        ]);

        return response()->json(
            $this->item($link, new LinkTransformer()),
            Response::HTTP_CREATED,
            ['Location' => route('UserLinks.show', ['uuid' => $link->uuid])]
        );
    }

    public function update(Request $request, $uuid)
    {
        $user = Auth::user();

        $this->validateUpdateLink($request);

        $link = $this->getLinkFromUserFilterByUuid($user, $uuid);

        $link->fill(
            $request->except(['category'])
        );

        if ($request->input('category')) {
            $category = $this->getCategoryFromUserFilterByUuid(
                $user,
                $request->input('category')
            );

            $link->category()->associate($category);
        }

        $link->save();

        return response()->json(
            $this->item($link, new LinkTransformer()),
            Response::HTTP_OK
        );
    }

    public function destroy($uuid)
    {
        $user = Auth::user();

        $link = $this->getLinkFromUserFilterByUuid($user, $uuid);

        $link->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function getLinksFromUser(User $user)
    {
        return $user->categories->flatMap(function ($category) {
            return $category->links;
        });
    }

    private function getLinkFromUserFilterByUuid(User $user, $uuid)
    {
        $links = $this->getLinksFromUser($user);

        $link = $links->where('uuid', $uuid)->first();

        if (! $link) {
            throw new ModelNotFoundException();
        }

        return $link;
    }

    private function getCategoryFromUserFilterByUuid(User $user, $uuid)
    {
        return $user->categories()->where('uuid', $uuid)->firstOrFail();
    }

    private function validateLink(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'url' => 'required|max:255',
            'category' => 'required|regex:/^' . env('UUID_REGEX') . '$/|exists:categories,uuid',
        ]);
    }

    private function validateUpdateLink(Request $request)
    {
        $this->validate($request, [
            'title' => 'max:255',
            'url' => 'max:255',
            'category' => 'regex:/^' . env('UUID_REGEX') . '$/|exists:categories,uuid',
        ]);
    }
}
