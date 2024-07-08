<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MethodPay extends Model
{
    protected $table = "001_droi_p3_t1_bills_c1_method_pay";
    protected $primaryKey = 'Id';
    public $timestamps = false;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "Id_Source", 
        "Id_Method", 
        "Value" 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // protected $hidden = [
    //     "CUNI",
    //     "Id_Warehouse",
    //     "Id_Bill"
    // ];
    
    // /**
    //  * Get a custom query builder for the model's table.
    //  *
    //  * @return \Illuminate\Database\Eloquent\Builder
    //  */
    // public function newEloquentBuilder($query): Builder
    // {
    //     return new class($query) extends Builder
    //     {
    //         public function addTaxes(): Builder
    //         {
    //             return $this->selectRaw('
                    
    //             ');

    //         }
            
    //     };
    // }
}
