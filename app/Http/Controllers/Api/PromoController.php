<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use App\Promo;
use File;
use Storage;

class PromoController extends Controller
{
    public function index(){
        $promos = Promo::all(); 
        
        if(count($promos)>0){
            return response([
                'message' => 'Retrieve All Success',
                'data' => $promos
            ],200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); 
    }

    public function show($id){
        $promo = Promo::find($id); 

        if(!is_null($promo)){
            return response([
                'message' => 'Retrieve Promo Success',
                'data' => $promo
            ],200);
        } 

        return response([
            'message' => 'Promo Not Found',
            'data' => null
        ],404); 
    }

    public function store(Request $request){
        $uploadFolder = 'gambarPromo';
        $storeData = $request->all(); 
        $validate = Validator::make($storeData,[
            'judul' => 'required|max:60|unique:promos',
            'deskripsi' => 'required',
            'promoURL' => 'required|mimes:jpg,jpeg,png',
        ]); 

        if($validate->fails())
        {
            return response(['message' => $validate->errors()],400); 

        }

        $newPromo = new Promo;
        $newPromo->judul = $request->judul;
        $newPromo->deskripsi = $request->deskripsi;

        $file = $request->file('promoURL');
        if($file!=null){
            $file_upload_path = $file->store($uploadFolder,'public');
            $uploadImageResponse = array(
                "file_name" => basename($file_upload_path),
                "file_url" => Storage::disk('public')->url($file_upload_path),
                "mime" => $file->getClientMimeType()
            );
            $newPromo->promoURL = basename($file_upload_path);
        }
        $newPromo->save();

        return response([
            'message' => 'Add Promo Success',
            'data' => $newPromo,
        ],200); 
    }

    public function destroy($id){
        $promo = Promo::find($id); 

        if(is_null($promo)){
            return response([
                'message' => 'Promo Not Found',
                'data' => null
            ],404);
        }

        if($promo -> delete()){
            Storage::disk('public')->delete('gambarPromo/' . $promo->promoURL);
            
            return response([
                'message' => 'Delete Promo Success',
                'data' => $promo,
            ],200);
        }
        
        return response([
            'message' => 'Delete Promo Failed',
            'data' => null,
        ],400); 
    }

    public function update(Request $request,$id){
        $uploadFolder = 'gambarPromo';
        $promo = Promo::find($id);
        if(is_null($promo)){
            return response([
                'message' => 'Promo Not Found',
                'data' => null
            ],404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData,[
            'judul' => ['max:60', Rule::unique('promos')->ignore($promo)],
            'deskripsi' => 'required',
            'promoURL' => 'mimes:jpg,jpeg,png',
        ]); 

        if($validate->fails())
            return response(['message' => $validate->errors()],400); 
        
        $promo->judul = $updateData['judul'];
        $promo->deskripsi = $updateData['deskripsi'];

        $file = $request->file('promoURL');
        if($file!=null)
        {
            $file_upload_path = $file->store($uploadFolder,'public');
            $uploadImageResponse = array(
                "file_name" => basename($file_upload_path),
                "file_url" => Storage::disk('public')->url($file_upload_path),
                "mime" => $file->getClientMimeType()
            );
            Storage::disk('public')->delete('gambarPromo/' . $promo->promoURL);
            $promo->promoURL = basename($file_upload_path);
        }
        
        if($promo->save()){
            return response([
                'message' => 'Update Promo Success',
                'data' => $promo,
            ],200);
        }
        
        return response([
            'message' => 'Update Promo Failed',
            'data' => null,
        ],400); 
    }  
}
