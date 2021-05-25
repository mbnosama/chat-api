<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use App\Http\Resources\AuthorResource;
use App\Models\User;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {


    // register user
    public function register(Request $request){
        $validators=Validator::make($request->all(),[
            'name'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required'
        ]);
        if($validators->fails()){
            return Response::json(['errors'=>$validators->getMessageBag()->toArray()]);
        }else{
            $user=new User();
            $user->name=$request->name;
            $user->email=$request->email;
            $user->password=bcrypt($request->password);
            $user->api_token=Str::random(80);
            $user->save();
            return Response::json(['success'=>'Registration done successfully !','user'=>$user,'token'=>$user->api_token]);
        }
    }

    // login user
    public function login(Request $request){
        $validators=Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required'
        ]);
        if($validators->fails()){
            return Response::json(['errors'=>$validators->getMessageBag()->toArray()]);
        }else{
            if(Auth::attempt(['email'=>$request->email,'password'=>$request->password])){
                $user=$request->user();
                $user->api_token=Str::random(80);
                $user->save();
                return Response::json(['success'=>'Login was successfully !','user'=>Auth::user(),'token'=>Auth::user()->api_token]);
            }else{
                return Response::json(['errors'=>'Login failed ! Wrong credentials.']);
            }
        }
    }

    // log the user out
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->api_token = NULL;
        $user->save();
        return Response::json(['message' => 'Logged out!']);
    }
}
