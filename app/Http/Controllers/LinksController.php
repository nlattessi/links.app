<?php

namespace App\Http\Controllers;

use App\Category;
use App\Link;
use App\Transformers\LinkTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LinksController extends Controller
{
    public function index()
    {
        return $this->collection(
            Link::all(),
            new LinkTransformer()
        );
    }

    public function show($uuid)
    {
        return $this->item(
            Link::where('uuid', $uuid)->firstOrFail(),
            new LinkTransformer()
        );
    }

    public function store(Request $request)
    {
        $this->validateLink($request);

        $link = Link::create(
            $this->getLinkData($request)
        );

        return response()->json(
            $this->item($link, new LinkTransformer()),
            Response::HTTP_CREATED,
            ['Location' => route('links.show', ['uuid' => $link->uuid])]
        );
    }

    public function update(Request $request, $uuid)
    {
        $this->validateLink($request);

        $link = Link::where('uuid', $uuid)->firstOrFail();

        $link->fill(
            $this->getLinkData($request)
        );
        $link->save();

        return response()->json(
            $this->item($link, new LinkTransformer()),
            Response::HTTP_OK
        );
    }

    public function destroy($uuid)
    {
        Link::where('uuid', $uuid)->firstOrFail()->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function validateLink(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'url' => 'required|max:255',
            'category_id' => 'required|regex:/^' . env('UUID_REGEX') . '$/|exists:categories,uuid'
        ], [
            'title.required' => 'The :attribute field is required.',
            'title.max' => 'The :attribute may not be greater than :max characters.',
            
            'url.required' => 'The :attribute field is required.',
            'url.max' => 'The :attribute may not be greater than :max characters.',
        ]);
    }

    private  function getLinkData(Request $request)
    {
        return [
            'title' => $request->input('title'),
            'url' => $request->input('url'),
            'description' => $request->input('description'),
            'category_id' => Category::where('uuid', $request->input('category_id'))->firstOrFail()->id,
        ];
    }
}
