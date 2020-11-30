<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Item extends Model
{
    //
    protected $fillable = [
        'namaItem', 'kategori', 'satuan', 'stok', 'harga'
    ];

    public function getCreatedAtAttribute(){
        if(!is_null($this->attributes['created_at'])){
            return Carbon::parse($this->attributes['created_at'])->format('d-m-Y H:i:s');
        }
    }

    public function getUpdatedAttribute(){
        if(!is_null($this->attributes['updated_at'])){
            return Carbon::parse($this->attributes['updated_at'])->format('d-m-Y H:i:s');
        }
    }
}
