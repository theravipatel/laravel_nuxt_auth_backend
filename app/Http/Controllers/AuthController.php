<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request) {
        return $request;
    }

    public function register(Request $request) {
        return $request;
    }

    public function logout() {
        return "logout==";
    }
}
