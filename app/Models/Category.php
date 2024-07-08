<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    protected $table = "001_droi_p1_t1_inventory_sele_c1_products_groups";
    protected $primaryKey = 'Code';
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'Display_Order',
        'Id_Business',
        'Id_Parent',
        "Printer",
        "Printer_Copy_1",
        "Printer_Copy_2",
        "Copy1_Status",
        "Copy2_Status",
        "Monitor",
        "Iva",
        "Iva_Porcentage",
        "Show_Interactive",
        'Image'

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
                return $this->selectRaw("
                    *, 
                    if(
                        COALESCE(`Image`, '') = '', 
                        NULL, 
                        CONCAT('" . env('ASSETS_GESADMIN') . "', '/Groups/', `Image`)
                    ) AS  `UrlImage`
                ");
            }
            
            public function hasSubcategory(): Builder
            {
                $table = "001_droi_p1_t1_inventory_sele_c1_products_groups";
                return $this->selectRaw("
                    IF (
                        (
                            SELECT COUNT(g1.code) 
                            FROM 001_droi_p1_t1_inventory_sele_c1_products_groups g1 
                            WHERE g1.Id_Parent = 001_droi_p1_t1_inventory_sele_c1_products_groups.Code
                        )  > 0, 
                        'true', 
                        'false'
                    )as `hasSubcategory`
                ");
            }
        };
    }
}
