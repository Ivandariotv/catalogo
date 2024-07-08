<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseProduct extends Model
{
    protected $table = "001_droi_p1_t1_warehouse_inventory";
    protected $primaryKey = 'Id';
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "Id",
        "Id_Warehouse",
        "Id_Inventory",
        "Id_Provider",
        "Name",
        "Barcode_batch",
        "Unit",
        'Units_Pack',
        "Weight_batch",
        "Price_Base",
        "Price_Suggest",
        'Price_Cost',
        'Price_Wholesale',
        'Price_Minimum',
        "Iva_Perc",
        "Impo_Perc",
        'Expiration_Date',
        'State'
    ];
}
