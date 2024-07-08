<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BillProducts extends Model
{
    protected $table = "001_droi_p3_t1_bills_c1_products";
    protected $primaryKey = 'Id';
    public $timestamps = false;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "Id_Warehouse", 
        "Id_Bill", 
        "Checked_delivery", 
        "Checked_kitchen", 
        "Date_Petition", 
        "Date_Kitchen", 
        "Date_Delivery", 
        "Code_Product", 
        "Product", 
        "Description", 
        "Price", 
        "Units", 
        "Porcentaje", 
        "Porcetage_Impo", 
        "Price_Cost", 
        "Commision", 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "CUNI",
        "Id_Warehouse",
        "Id_Bill",
        "Code_Waiter",
        "Code_Product",
        "Lote",
        // "Description",
        "Price_Cost",
        "Price",
        "Data_Batch",
        "Suggest_Batch",
        "Porcentaje",
        "Porcetage_Impo",
        "taxBag",
        "taxBag_value",
        "Discount",
        "Commision",
        "Checked_delivery",
        "Checked_kitchen",
        // "Date_Petition",
        "Date_Kitchen",
        "Date_Delivery"
    ];
    
    /**
     * Get a custom query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query): Builder
    {
        return new class($query) extends Builder
        {
            public function addUrlImage(): Builder
            {
                $suggestedPrice = '( (Price - (Discount / Units)) * ( ( Porcentaje + Porcetage_Impo ) / 100 + 1  )  )';
                return $this->selectRaw(
                    "*, 
                    Code_Product AS Id,
                    ROUND(Price) AS Previous_Price,
                    ROUND(".$suggestedPrice.") AS Current_Price,
                    (SELECT inv.Description FROM 001_droi_p1_t1_inventory_sele inv WHERE inv.Id = Code_Product) AS Description,
                    IF(
                        COALESCE(
                            @image := (
                                SELECT inv.Image FROM 001_droi_p1_t1_inventory_sele inv WHERE inv.Id = Code_Product
                            ), 
                            ''
                        ) = '', 
                        NULL, 
                        CONCAT('" . env('ASSETS_GESADMIN') . "', '/Items/', @image)
                    ) AS UrlImage"
                );
            }

            public function addTaxes(): Builder
            {
                $suggestedPrice = '( Price - Discount / Units /( ( Porcentaje + Porcetage_Impo ) / 100 + 1  )  )';
                $iva = $suggestedPrice.' *(Porcentaje / 100)';
                $ipoconsumo = $suggestedPrice.' *(Porcetage_Impo / 100)';

                return $this->selectRaw('
                    ROUND( '.$iva.', 3 ) AS Iva,
                    ROUND( '.$ipoconsumo.', 3 ) AS Ipoconsumo, 
                    ROUND((('.$suggestedPrice.')+('.$iva.')+('.$ipoconsumo.') - Discount) * Units) as total
                ');

            }
            
        };
    }
}
