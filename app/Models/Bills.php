<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BillProducts;
use Illuminate\Database\Eloquent\Builder;

class Bills extends Model
{
    protected $table = "001_droi_p3_t1_bills";
    protected $primaryKey = 'Id';
    
    public $timestamps = false;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "CUNI",
        "User_Code",
        "Id_Business",
        "Id_Warehouse",
        "Code_Space",
        "Code_Mesa",
        "Mesa",
        "Code_Waiter",
        "State",
        "Last_Update",
        "Code_Client",
        "Client",
        "Identity",
        "Phone",
        "Address",
        "City",
        "Email",
        "Date",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "DocumentID",
        "CUNI",
        "Sub_Id",
        "Id_Business",
        "Id_Warehouse",
        "Id_Parent",
        "Id_AddPoints",
        "Id_Marker",
        "Code_Waiter",
        "Code_Order",
        "Code_Mesa",
        "Code_Space",
        "User_Code",
        "Code_Courier",
        "Code_Client",
        "Client_Business",
        "Client_Business_Name",
        "Bill",
        "Prefix_Bill",
        "Bill_Resolution",
        "Code_Confirmation",
        "Device",
        "Reference_DB",
        "Reference_One",
        "Reference_Two",
        "Mesa",
        "prebill",
        "Number_People",
        "Client",
        "Identity",
        "Phone",
        "Address",
        "City",
        "Email",
        "Send_Email",
        "Send_Msj",
        "State_Domicile",
        "Waiter",
        "Send_Courier",
        "Delivery_Courier",
        "Electronic_Bill",
        "Date_Electronic",
        "OC",
        "OV",
        "Paid",
        "MethodPaid",
        "Tarjeta",
        "Points",
        "Fund",
        "Rete_ICA",
        "Rete_Fuente",
        "Markers_in_bill",
        "Porcentage_tip",
        "PorcentageDatafono",
        "Propina",
        "CostoDatafono",
        "Domicile_Value",
        "Domicile_Consecutive",
        "PagaCon",
        "PercentageInterest",
        "PercentageMora",
        "PercentageCobranza",
        "Comment_Bill",
        "Comment_Discount",
        "Comment_Cancel",
        "Comment_Internal",
        "Location",
        "Date",
        "Expiration_Date",
        "Date_Kitchen",
        "Date_Delivery",
        "Date_KitchenIn",
        "Date_Delivered",
        "Price_Cost",
        "Last_Update",
        "State",
        "State_sms",
        "State_Dispatch",
        "Date_Dispatch",
        "User_Dispatch",
        "Type",
        "Type_Bill",
        "Header_Quotation",
        "Footer_Quotation",
        "Number_Order",
        "Date_Order",
    ];

    /**
     * Get the client that owns the bill.
     */
    
     public function Products()
     {
        $BPTable = '001_droi_p3_t1_bills_c1_products';
        $ProductTable = '001_droi_p1_t1_inventory_sele';
        return $this->hasMany(BillProducts::class, 'Id_Bill')
            ->addUrlImage()
            ->orderByDesc('001_droi_p3_t1_bills_c1_products.Id');
     }

     /**
     * Get a custom query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query): Builder
    {
        return new class($query) extends Builder
        {
            public function addTotal(): Builder
            {
                return $this->selectRaw("*, calcule_total_bill(`Id`) as `total`");
            }
            
            public function addColumnsrecommend($PTable): Builder
            {
                return $this->selectRaw("$PTable.Id, $PTable.Product, COUNT(*) as `total_compras`");
            }
            public function addTaxes(): Builder
            {
                // $suggestedPrice = '( Price - Discount / Units /( ( Porcentaje + Porcetage_Impo ) / 100 + 1  )  )';
                // $iva = $suggestedPrice.' *(Porcentaje / 100)';
                // $ipoconsumo = $suggestedPrice.' *(Porcetage_Impo / 100)';
                $billsTable = '001_droi_p3_t1_bills';

                return $this->selectRaw(
                    "
                    (
                        SELECT SUM(ROUND(
                            (
                                bp.Price - bp.Discount / bp.Units /(
                                    (
                                        bp.Porcentaje + bp.Porcetage_Impo
                                    ) / 100 + 1
                                )
                            ) * bp.Units,
                            2
                        )) 
                        FROM 001_droi_p3_t1_bills_c1_products bp 
                        WHERE bp.Id_Bill = $billsTable.Id
                    ) AS Subtotal,
                    ROUND(calculate_iva(`Id`), 2) AS `Total_iva`,
                    ROUND(calculate_impoconsumo(`Id`), 2) AS `Total_impoconsumo`"
                );

            }
            public function changeState(): Builder
            {
                return $this->selectRaw(
                    "CASE 
                        WHEN State = 'Temporal' THEN 'In progress' 
                        WHEN State = 'Active' AND State_Domicile = 'Kitchen' THEN 'In progress' 
                        WHEN State = 'Active' AND State_Domicile = 'In_Kitchen' THEN 'In progress' 
                        WHEN State = 'Active' AND State_Domicile = 'Run' THEN 'In progress' 
                        WHEN State = 'Active' AND State_Domicile = 'Finish' THEN 'Delivered'
                        WHEN State = 'Active' AND State_Domicile = 'n' THEN 'Delivered' 
                        WHEN State = 'Erased' THEN 'canceled' 
                    END as state"            
                );
            }
            
            
        };
    }
}
