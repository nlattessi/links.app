<?php

namespace App\Http\Controllers;

use App\User;
use App\Transformers\UserTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UsersController extends Controller
{
    public function index()
    {
        return $this->collection(User::all(), new UserTransformer());
    }

    public function show($id)
    {
        return $this->item(User::findOrFail($id), new UserTransformer());
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|max:255|email|unique:users',
            'password' => 'required|max:255',
            // 'password_confirmation' => 'required|max:255',
        ], [
            'email.required' => 'The :attribute field is required.',
            'email.max' => 'The :attribute may not be greater than :max characters.',
            'email.email' => 'The :attribute must be valid.',
            'email.unique' => 'This :attribute is already registered.',
            
            'password.required' => 'The :attribute field is required.',
            'password.max' => 'The :attribute may not be greater than :max characters.',
        ]);

        $user = User::create([
            'email' => $request->input('email'),
            'password' => app('hash')->make($request->input('password')),
        ]);

        return response()->json(
            $this->item($user, new UserTransformer()),
            Response::HTTP_CREATED,
            ['Location' => route('users.show', ['id' => $user->id])]
        );
    }

    // public function update(Request $request, $id)
    // {
    //     $link = Link::findOrFail($id);

    //     $this->validate($request, [
    //         'title' => 'required|max:255',
    //         'url' => 'required|max:255',
    //     ], [
    //         'title.required' => 'The :attribute field is required.',
    //         'title.max' => 'The :attribute may not be greater than :max characters.',
            
    //         'url.required' => 'The :attribute field is required.',
    //         'url.max' => 'The :attribute may not be greater than :max characters.',
    //     ]);

    //     $link->fill($request->all());
    //     $link->save();

    //     return $this->item($link, new LinkTransformer());
    // }

    // public function destroy($id)
    // {
    //     $link = Link::findOrFail($id);

    //     $link->delete();

    //     return response(null, Response::HTTP_NO_CONTENT);
    // }
}
