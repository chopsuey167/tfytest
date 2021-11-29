<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'name_and_surname' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),400);
        }

        $nombrecompleto = trim($request->name_and_surname);
        $arrayNombre = explode(' ', $nombrecompleto, 2);
        
        $user = User::create(array_merge(
            $validator->validate(),
            ['nombre' => $arrayNombre[0],
             'apellido' =>$arrayNombre[1],
             'password' => bcrypt($request->password),   
            ]
        ));
        
        return response()->json([
            'message' => 'Se registro usuario exitosamente',
            'user' => $user,
        ], 201);
    }

    public function infouser($id){
        $user = User::find($id);
        if(is_null($user)){
            return response()->json([ 'message' => 'Usuario no existe'], 404);
        }
         
        return response()->json([
            'user' => $user,
        ], 200);
    }
}