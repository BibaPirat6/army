<?php

namespace App\Http\Controllers;


class UserController extends Controller
{
    public function index()
    {
        return view("user.structure");
    }

    public function profile()
    {
        return view("user.profile");
    }
}
