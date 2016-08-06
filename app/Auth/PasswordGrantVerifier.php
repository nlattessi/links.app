<?php

namespace App\Auth;

use Illuminate\Support\Facades\Auth;

class PasswordGrantVerifier
{
    public function verify($email, $password)
    {
        $user = \App\User::where('email', $email)->first();

        if ($user && app('hash')->check($password, $user->password)) {
            return $user->id;
        }

        return false;
    }
}
