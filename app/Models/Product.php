<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $table = "001_droi_p1_t1_inventory_sele";
    protected $primaryKey = 'Id';
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "CUNI",
        "Display_Order",
        "Barcode",
        "Code_Group",
        "Image",
        "Make",
        "Provider",
        // "Units",
        "Units_Pack",
        "Price",
        "Price_Cost",
        "Price_Sugerido",
        "Price_Wholesale",
        "Price_Wholesale_Neto",
        "Price_Distributor",
        "Price_Distributor_Neto",
        "Price_App",
        "Price_App_Neto",
        "Price_Minimum",
        "Commission",
        "type_iva",
        "type_iva_2",
        "type_iva_3",
        "type_iva_4",
        "Porcertage",
        "Iva",
        "ipoconsumo",
        "valor_ipoconsumo",
        "impobolsa",
        "Iva_Wholesale",
        "Valor_Iva_Wholesale",
        "ipoconsumo_Wholesale",
        "Valor_ipoconsumo_Wholesale",
        "Iva_Distributor",
        "Valor_Iva_Distributor",
        "ipoconsumo_Distributor",
        "Valor_ipoconsumo_Distributor",
        "Iva_App",
        "Valor_Iva_App",
        "ipoconsumo_App",
        "Valor_ipoconsumo_App",
        "Measure",
        "LibrasTotales",
        "ValorLibra",
        "Type",
        "product_points",
        "product_acum_points",
        "Min_Units",
        "Expiration_Date",
        "Id_Compl_Prod",
        "Compl_Value",
        "Id_Templ_Relation",
        "State",
        "Last_Update",
        "Id_Woocommerce",

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
                $ProductTable = '001_droi_p1_t1_inventory_sele';
                return $this->selectRaw(
                    "*,
                    Price_App AS Previous_Price,
                    0.00 AS Current_Price,
                    $ProductTable.Id,
                    if(
                        COALESCE(`Image`, '') = '', 
                        NULL, 
                        CONCAT('" . env('ASSETS_GESADMIN') . "', '/Items/', `Image`)
                    ) AS  `UrlImage`"
                );
            }

            public function addUnitsCar($Id_Business, $user): Builder
            {
                switch ($user['state']) {
                    case 'authenticated':
                        $Code_Client = $user['data']->Id;

                        $bills = "001_droi_p3_t1_bills";
                        $billProducts = '001_droi_p3_t1_bills_c1_products';
                        $invSale = "001_droi_p1_t1_inventory_sele";
                        $subquery = "(SELECT bp.Units FROM $billProducts bp JOIN $bills b ON b.Id = bp.Id_Bill WHERE bp.Code_Product = $invSale.Id AND b.Id_Business = $Id_Business AND b.Code_Client = $Code_Client AND b.State = 'History')";

                        return $this->selectRaw(
                            "if(
                                COALESCE( @units := $subquery, '' ) = '',
                                '0.000',
                                @units
                            ) AS Units
                            "
                        );
                        break;
                    
                    case 'Unauthenticated':
                        return $this->selectRaw(
                            "'0.000' AS Units"
                        );
                        break;
                }
            }

            public function addUnitsCarNoLogin(): Builder
            {
                
            }
        };
    }
}
