<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use File;
use App\Item;
use App\Order;
use Storage;

class OrderController extends Controller
{
    //
    public function index($idPelanggan){
        //$orders = Order::all(); 
        $orders = Order::where([
                                ['statusPesan', '=' , 'Process'],
                                ['idPelanggan', '=' , $idPelanggan],
                            ])->get();
        if(count($orders)>0){
            return response([
                'message' => 'Retrieve All Success',
                'data' => $orders
            ],200);
        }
        
        return response([
            'message' => 'Empty',
            'data' => null
        ],404); 
    }

    public function indexByFinished($idPelanggan){
        //$orders = Order::all(); 
        //$orders = Order::where('statusPesan', '=' , 'Process')->get();
        $orders = Order::where([
            ['statusPesan', '=' , 'Finished'],
            ['idPelanggan', '=' , $idPelanggan],
        ])->get();

        if(count($orders)>0){
            return response([
                'message' => 'Retrieve All Success',
                'data' => $orders
            ],200);
        }
        
        return response([
            'message' => 'Empty',
            'data' => null
        ],404); 
    }

    public function indexByProcessInAdmin(){
        //$orders = Order::all(); 
        //$orders = Order::where('statusPesan', '=' , 'Process')->get();
        $orders = Order::where('statusPesan', '=' , 'Process',)->get();

        if(count($orders)>0){
            return response([
                'message' => 'Retrieve All Success',
                'data' => $orders
            ],200);
        }
        
        return response([
            'message' => 'Empty',
            'data' => null
        ],404); 
    }

    public function indexByFinishedInAdmin(){
        //$orders = Order::all(); 
        //$orders = Order::where('statusPesan', '=' , 'Process')->get();
        $orders = Order::where('statusPesan', '=' , 'Finished',)->get();

        if(count($orders)>0){
            return response([
                'message' => 'Retrieve All Success',
                'data' => $orders
            ],200);
        }
        
        return response([
            'message' => 'Empty',
            'data' => null
        ],404); 
    }

    public function show($id){
        $order = Order::find($id); 

        if(!is_null($order)){
            return response([
                'message' => 'Retrieve Order Success',
                'data' => $order
            ],200);
        } 

        return response([
            'message' => 'Order Not Found',
            'data' => null
        ],404); 
    }

    public function store(Request $request){
        $uploadFolder = 'fileUser';
        $storeData = $request->all(); 
        $validate = Validator::make($storeData,[
            'idPelanggan' => 'required',
            'namaItem' => 'required|max:60',
            'kategori' => 'required',
            'jumlah' => 'required|numeric',
            'jenisWarna' => 'required',
            'jenisServis' => 'required',
            'filePesan' => 'required|mimes:doc,docx,pdf,zip'
        ]); 

        if($validate->fails())
            return response(['message' => $validate->errors()],400); 

        // $fileName = time().$request->file('filePesan')->getClientOriginalName();
        // $path = base_path() . '/public/file/';
        // $file->move($path, $fileName);
        

        $kolom = 'namaItem';
        $item = Item::where($kolom, '=' , $request->namaItem)->first();

        if($request->jenisWarna == "Colored")
        {
            $totalHarga = ($item->harga * $request->jumlah) + (($item->harga * $request->jumlah)*0.5);
        }else{
            $totalHarga = $item->harga * $request->jumlah;
        }

        $newOrder = new Order;
        $newOrder->idPelanggan = $request->idPelanggan;
        $newOrder->idItem = $item->id;
        $newOrder->namaItem = $request->namaItem;
        $newOrder->kategori = $request->kategori;
        $newOrder->jumlah = $request->jumlah;
        $newOrder->total = $totalHarga;
        $newOrder->statusPesan = "Process";
        $newOrder->jenisWarna = $request->jenisWarna;
        $newOrder->jenisServis = $request->jenisServis;

        $file = $request->file('filePesan');
        if($file!=null){
            $file_upload_path = $file->store($uploadFolder,'public');
            $uploadImageResponse = array(
                "file_name" => basename($file_upload_path),
                "file_url" => Storage::disk('public')->url($file_upload_path),
                "mime" => $file->getClientMimeType()
            );
            $newOrder->filePesan = basename($file_upload_path);
        }
        $newOrder->save();

        return response([
            'message' => 'Add Order Success',
            'data' => $newOrder,
        ],200); 
    }

    public function destroy($id){
        $order = Order::find($id); 

        if(is_null($order)){
            return response([
                'message' => 'Order Not Found',
                'data' => null
            ],404);
        }

        if($order -> delete()){
            $file_path =  $order->filePesan; 
            if(File::exists($file_path)) {
                File::delete($file_path);
            }
            return response([
                'message' => 'Delete Order Success',
                'data' => $order,
            ],200);
        }
        
        return response([
            'message' => 'Delete Order Failed',
            'data' => null,
        ],400); 
    }

    public function update(Request $request,$id){
        $uploadFolder = 'fileUser';
        $order = Order::find($id);
        if(is_null($order)){
            return response([
                'message' => 'Order Not Found',
                'data' => null
            ],404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData,[
            'namaItem' => 'required|max:60',
            'kategori' => 'required',
            'jumlah' => 'required|numeric',
            'jenisWarna' => 'required',
            'jenisServis' => 'required',
            'filePesan' => 'mimes:doc,docx,pdf,zip' 
        ]); 

        if($validate->fails())
        {
            return response(['message' => $validate->errors()],400); 
        }
            
        $kolom = 'namaItem';
        $item = Item::where($kolom, '=' , $request->namaItem)->first();

        if($request->jenisWarna == "Colored")
        {
            $totalHarga = ($item->harga * $request->jumlah) + (($item->harga * $request->jumlah)*0.5);
        }else{
            $totalHarga = $item->harga * $request->jumlah;
        }

        $order->idItem = $item->id;
        $order->namaItem = $updateData['namaItem'];
        $order->kategori = $updateData['kategori'];
        $order->jumlah = $updateData['jumlah'];
        $order->jenisWarna = $updateData['jenisWarna'];
        $order->jenisServis = $updateData['jenisServis'];
        $order->total = $totalHarga;

        $file = $request->file('filePesan');
        if($file!=null)
        {
            // $file_path =  base_path() . '/public/file/'.$order->filePesan; 
            // if(File::exists($file_path)) {
            //     File::delete($file_path);
            // }

            // $fileName = time().$request->file('filePesan')->getClientOriginalName();
            // $path = base_path() . '/public/file/';
            // $file->move($path,$fileName);

            // $order->filePesan = $path . $fileName;
            $file_upload_path = $file->store($uploadFolder,'public');
            $uploadImageResponse = array(
                "file_name" => basename($file_upload_path),
                "file_url" => Storage::disk('public')->url($file_upload_path),
                "mime" => $file->getClientMimeType()
            );
            $order->filePesan = basename($file_upload_path);
        }
        
        if($order->save()){
            return response([
                'message' => 'Update Order Success',
                'data' => $order,
            ],200);
        }
        
        return response([
            'message' => 'Update Order Failed',
            'data' => null,
        ],400); 
    }  

    public function updateStatus(Request $request,$id){
        $order = Order::find($id);

        $updateData = $request->all();
        if(is_null($order)){
            return response([
                'message' => 'Order Not Found',
                'data' => null
            ],404);
        }

        $order->statusPesan = $updateData['statusPesan'];

        if($order->save()){
            return response([
                'message' => 'Update Order Success',
                'data' => $order,
            ],200);
        }
        
        return response([
            'message' => 'Update Order Failed',
            'data' => null,
        ],400); 
    }  
}
