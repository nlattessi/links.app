<?php

namespace App\Http\Controllers;

use App\Link;
use App\Transformers\LinkTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class LinksController extends Controller
{
    public function index()
    {
        return $this->collection(Link::all(), new LinkTransformer());
    }

    public function show($id)
    {
        return $this->item(Link::findOrFail($id), new LinkTransformer());
    }

    public function store(Request $request)
    {
        $link = Link::create($request->all());

        return response()->json(
            $this->item($link, new LinkTransformer()),
            201,
            ['Location' => "/links/{$link->id}"]
        );
    }

    public function update(Request $request, $id)
    {
        $link = Link::findOrFail($id);

        $link->fill($request->all());
        $link->save();

        return ['data' => $link];
    }

    public function destroy($id)
    {
        $link = Link::findOrFail($id);

        $link->delete();

        return response(null, 204);
    }
}
