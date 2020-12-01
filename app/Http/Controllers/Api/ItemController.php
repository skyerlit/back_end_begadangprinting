<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use App\Item;

class ItemController extends Controller
{
    //
    public function index(){
        $items = Item::all(); 
        
        if(count($items)>0){
            return response([
                'message' => 'Retrieve All Success',
                'data' => $items
            ],200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); 
    }

    public function indexByNamaItem(){
        //$items = Item::all(); 
        $items = Item::pluck('namaItem');
        
        if(count($items)>0){
            return response([
                'message' => 'Retrieve All Success',
                'data' => $items
            ],200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); 
    }

    public function show($id){
        $item = Item::find($id); 

        if(!is_null($item)){
            return response([
                'message' => 'Retrieve Item Success',
                'data' => $item
            ],200);
        } 

        return response([
            'message' => 'Item Not Found',
            'data' => null
        ],404); 
    }

    public function store(Request $request){
        $storeData = $request->all(); 
        $validate = Validator::make($storeData,[
            'namaItem' => 'required|max:60|unique:items',
            'kategori' => 'required',
            'satuan' => 'required|alpha',
            'stok' => 'required|numeric',
            'harga' => 'required|numeric',
        ]); 

        if($validate->fails())
            return response(['message' => $validate->errors()],400); 


        $newItem = new Item;
        $newItem->namaItem = $request->namaItem;
        $newItem->kategori = $request->kategori;
        $newItem->satuan = $request->satuan;
        $newItem->stok = $request->stok;
        $newItem->harga = $request->harga;
        $newItem->save();

        return response([
            'message' => 'Add Item Success',
            'data' => $newItem,
        ],200); 
    }

    public function destroy($id){
        $item = Item::find($id); 

        if(is_null($item)){
            return response([
                'message' => 'Item Not Found',
                'data' => null
            ],404);
        }

        if($item -> delete()){
            return response([
                'message' => 'Delete Item Success',
                'data' => $item,
            ],200);
        }
        
        return response([
            'message' => 'Delete Item Failed',
            'data' => null,
        ],400); 
    }

    public function update(Request $request,$id){
        $item = Item::find($id);
        if(is_null($item)){
            return response([
                'message' => 'Item Not Found',
                'data' => null
            ],404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData,[
            'namaItem' => ['max:60', Rule::unique('items')->ignore($item)],
            'kategori' => 'max:60',
            'satuan' => 'max:60',
            'stok' => 'numeric',
            'harga' => 'numeric',   
        ]); 

        if($validate->fails())
            return response(['message' => $validate->errors()],400); 
        
        $item->namaItem = $updateData['namaItem'];
        $item->kategori = $updateData['kategori'];
        $item->satuan = $updateData['satuan'];
        $item->stok = $updateData['stok'];
        $item->harga = $updateData['harga'];
        
        if($item->save()){
            return response([
                'message' => 'Update Item Success',
                'data' => $item,
            ],200);
        }
        
        return response([
            'message' => 'Update Item Failed',
            'data' => null,
        ],400); 
    }  
}
