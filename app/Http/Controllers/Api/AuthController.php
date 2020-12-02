<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\user;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Storage;
use File;


class AuthController extends Controller
{
    //
    public function register(Request $request){
        $registrationData = $request->all();
        $validate = Validator::make($registrationData,[
            'name' => 'required|max:60',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required'
        ]);//membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()],400);

        $registrationData['password'] = bcrypt($request->password);
        $registrationData['status'] = 'user';
        $user = User::create($registrationData);
        $user->sendEmailVerificationNotification();
        return response([
            'message' => 'Register Success',
            'user' => $user,
        ],200);
    }

    public function registerAdmin(Request $request){
        $registrationData = $request->all();
        $validate = Validator::make($registrationData,[
            'name' => 'required|max:60',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required'
        ]);//membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()],400);

        $registrationData['password'] = bcrypt($request->password);
        $registrationData['status'] = 'admin';
        $user = User::create($registrationData);
        return response([
            'message' => 'Register Success',
            'user' => $user,
        ],200);
    }

    public function login(Request $request){
        $loginData = $request->all();
        $validate = Validator::make($loginData,[
            'email' => 'required|email:rfc,dns',
            'password' => 'required'
        ]); //membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input

        if(!Auth::attempt($loginData))
            return response(['message' => 'Invalid Credentials'],401); //return error gagal login

        $user = Auth::user();

        if($user->email_verified_at == null)
            return response(['message' => 'You need to verify your email'],401);

        else{
            $token = $user->createToken('Authentication Token')->accessToken; //generate token

            return response([
                'message' => 'Authenticated',
                'user' => $user,
                'token_type' => 'Bearer',
                'access_token' => $token
            ]); //return data user dan token dalam bentuk json
        }
    }

    public function loginAdmin(Request $request){
        $loginData = $request->all();
        $validate = Validator::make($loginData,[
            //'name' => 'required|max:60',
            'email' => 'required|email:rfc,dns',
            'password' => 'required'
        ]); //membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input

        if(!Auth::attempt($loginData))
            return response(['message' => 'Invalid Credentials'],401); //return error gagal login

        $user = Auth::user();
        $token = $user->createToken('Authentication Token')->accessToken; //generate token

        return response([
            'message' => 'Authenticated',
            'user' => $user,
            'token_type' => 'Bearer',
            'access_token' => $token
        ]); //return data user dan token dalam bentuk json
    }

    public function logout(Request $request){
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out' 
        ]);
    }

    public function user(Request $request){
        return response()->json($request->user());
    }

    public function show($id){
        $user = User::find($id); //mencari data product berdasarkan id

        if(!is_null($user)){
            return response([
                'message' => 'Retrieve User Success',
                'data' => $user
            ],200);
        } //return data product yang ditemukan dalam bentuk json

        return response([
            'message' => 'User Not Found',
            'data' => null
        ],404); //return message saat data product tidak ditemukan
    }

    public function update(Request $request,$id){
        $uploadFolder = 'gambarUser';

        $user = User::find($id);
        if(is_null($user)){
            return response([
                'message' => 'User Not Found',
                'data' => null
            ],404);
        }

        $updateData = $request->all();
        if($request->password!=null || $request->newPassword!=null || $request->newPasswordConfirm!=null){
            $validate = Validator::make($updateData,[
                'email' => ['max:60|email:rfc,dns', Rule::unique('users')->ignore($user)],
                'name' => 'required',
                'password' => 'required',
                'newPassword' => 'required',
                'newPasswordConfirm' => 'required|same:newPassword',
                'fileUpload' => 'mimes:jpg,jpeg,png'
            ]); 

            if($validate->fails())
            {
                return response(['message' => $validate->errors()],400);
            }else if(Hash::check($updateData['password'], $user->password)){
                $updateData['newPassword'] = bcrypt($request->newPassword);
                $user->name = $updateData['name'];
                $user->email = $updateData['email'];
                $user->password = $updateData['newPassword'];

                $file = $request->file('fileUpload');
                if($file!=null){
                    // if(File::exists($user->fileUpload)) {
                    //     File::delete($user->fileUpload);
                    // }

                    // $fileName = time().$request->file('fileUpload')->getClientOriginalName();
                    // $path = base_path() . '/public/gambar user/';
                    // $file->move($path,$fileName);

                    // $user->fileUpload = $path . $fileName;

                    $file_upload_path = $file->store($uploadFolder,'public');
                    $uploadImageResponse = array(
                        "image_name" => basename($file_upload_path),
                        "image_url" => Storage::disk('public')->url($file_upload_path),
                        "mime" => $file->getClientMimeType()
                    );

                    $user->fileUpload = basename($file_upload_path);
                }
            }
            // else if($request->email!=null && $request->name!=null){
            //     $validate = Validator::make($updateData,[
            //         'email' => ['max:60|email:rfc,dns', Rule::unique('users')->ignore($user)],
            //         'name' => 'required',
            //     ]); 
            //     if($validate->fails())
            //     {
            //         return response(['message' => $validate->errors()],400);
            //     }else{
            //         $user->name = $updateData['name'];
            //         $user->email = $updateData['email'];
            //     }

            // }
            else{
                return response(['message' => 'Old Password is not matched in database !'],400);
            }

        }

        else{
            $validate = Validator::make($updateData,[
                'email' => ['max:60|email:rfc,dns', Rule::unique('users')->ignore($user)],
                'name' => 'required',
                'fileUpload' => 'mimes:jpg,jpeg,png'
            ]); 

            if($validate->fails())
            {
                return response(['message' => $validate->errors()],400);
            }else{
                $user->name = $updateData['name'];
                $user->email = $updateData['email'];

                $file = $request->file('fileUpload');
                if($file!=null)
                {
                    //if(is_file($user->fileUpload)){
                        //unlink(storage_path('gambarUser/'.$user->fileUpload));
                        //Storage::delete($user->fileUpload);
                    //}
                    $file_upload_path = $file->store($uploadFolder,'public');
                    $uploadImageResponse = array(
                        "image_name" => basename($file_upload_path),
                        "image_url" => Storage::disk('public')->url($file_upload_path),
                        "mime" => $file->getClientMimeType()
                    );

                    $user->fileUpload = basename($file_upload_path);
                }
            }
        }

        if($user->save()){
            return response([
                'message' => 'Update User Success',
                'data' => $user,
            ],200);
        }
        
        return response([
            'message' => 'Update User Failed',
            'data' => null,
        ],400); 
    }   
}
