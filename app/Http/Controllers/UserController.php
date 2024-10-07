<?php

namespace App\Http\Controllers;

class UserController extends Controller
{
    /**
     * Show the profile for a given user.
     */
    public function show(string $id)
    {
        return [
            'id' => $id,
            'username' => 'minche',
            'password' => 'bedaa5fa05ff75dbe7f92852c7d015f8'
        ];
    }
}
