<?php

namespace App\Http\Controllers;

use App\Link;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class LinksController extends Controller
{
    public function index()
    {
        return Link::all();
    }

    public function show($id)
    {
        try {
            return Link::findOrFail($id);    
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => [
                    'message' => 'Link not found',
                ],
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $link = Link::create($request->all());

        return response()->json($link, 201, ['Location' => "/links/{$link->id}"]);
    }

    public function update(Request $request, $id)
    {
        $link = Link::findOrFail($id);

        $link->fill($request->all());
        $link->save();

        return $link;
    }

    public function destroy($id)
    {
        $link = Link::findOrFail($id);

        $link->delete();

        return response(null, 200);
    }
}
