<?php

namespace App\Http\Controllers;

use App\Link;

class LinksController extends Controller
{
    public function index()
    {
        return Link::all();
    }

    public function show($id)
    {
        return Link::findOrFail($id);
    }

    public function destroy($id)
    {
        $link = Link::findOrFail($id);

        $link->delete();

        return response(null, 200);
    }
}
