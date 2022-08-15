<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LumoUser;
use Illuminate\Support\Facades\Response;

class AcademyController extends Controller
{
    public function index()
    {

    }

    public function register(Request $request)
    {
        $name = $request['name'];
        $email = $request['email'];
        $body = $request['body'];

        $user = new LumoUser();

        if ($user->fill($request->all())) {
            return $user;
        }

        return Response(500);
    }
}
