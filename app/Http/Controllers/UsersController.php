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
            'email' => 'required|email|unique:users',
            'password' => 'required|max:255',
            // 'password_confirmation' => 'required|max:255',
        ], [
            'email.required' => 'The :attribute field is required.',
            'email.email' => 'The :attribute must be valid.',
            'email.unique' => 'This :attribute is already registered.',

            'password.required' => 'The :attribute field is required.',
            'password.max' => 'The :attribute may not be greater than :max characters.',
        ]);

        $request->merge([
            'password' => app('hash')->make($request->password)
        ]);
        $user = User::create($request->all());

        return response()->json(
            $this->item($user, new UserTransformer()),
            Response::HTTP_CREATED,
            ['Location' => route('users.show', ['id' => $user->id])]
        );
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'password' => 'required|max:255',
            // 'password_confirmation' => 'required|max:255',
        ], [
            'email.required' => 'The :attribute field is required.',
            'email.email' => 'The :attribute must be valid.',
            'email.unique' => 'This :attribute is already registered.',

            'password.required' => 'The :attribute field is required.',
            'password.max' => 'The :attribute may not be greater than :max characters.',
        ]);

        $request->merge([
            'password' => app('hash')->make($request->password)
        ]);
        $user->fill($request->all());
        $user->save();

        return $this->item($user, new UserTransformer());
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
