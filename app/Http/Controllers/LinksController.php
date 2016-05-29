<?php

namespace App\Http\Controllers;

use App\Link;

class LinksController extends Controller
{
    public function index()
    {
        return Link::all();
    }
}
