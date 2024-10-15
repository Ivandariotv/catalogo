<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventorySerial extends Model
{
    protected $table = "001_droi_p1_t2_inventory_serial";
    protected $primaryKey = 'Id';
    use HasFactory;

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id', 'id');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id', 'id');
    }

}
