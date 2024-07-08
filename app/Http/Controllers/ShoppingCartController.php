<?php

namespace App\Http\Controllers;

use App\Models\shoppingCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Bills;
use App\Models\BillProducts;
use App\Services\BillDiscounts;
use App\Models\Product;
use App\Models\Business;

class ShoppingCartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Bills = $this->getBill();
        if (empty($Bills)) $Bills = $this->createBill();

        return $Bills;
    }

    private function getBill()
    {
        // Obtener la configuración del negocio
        $config = $this->getConfig();
        $user = Auth::user();
        // $WarehouseProductTable = '001_droi_p1_t1_warehouse_inventory';
        // $ProductTable = '001_droi_p1_t1_inventory_sele';
        $Bills = Bills::addTotal()
            ->with(['Products'])
            ->where('State', 'History')
            ->where('Code_Client', $user->Id)
            ->where('Id_Business', $config->Id)
            ->where('Id_Warehouse', $config->Id_Warehouse)
            ->first();

        // $Bills->products = BillDiscounts::getCurrentPrice($Bills->products);

        return $Bills;
    }

    private function getBillsProducts($idProduct)
    {
        $Bill = $this->index();
        $BillsProducts = BillProducts::where('Code_Product', $idProduct)
            ->where('Id_Bill', $Bill->Id)
            ->first();

        return $BillsProducts;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createBill()
    {
        // Obtener la configuración del negocio
        $config = $this->getConfig();

        $user = Auth::user();
        $bill = new Bills([
            "CUNI" => 'loc',
            "User_Code" => 1,
            "Id_Business" => $config->Id,
            "Id_Warehouse" => $config->Id_Warehouse,
            "Code_Space"  => 0,
            "Code_Mesa"  => 0,
            "Mesa" => "APP$user->Id",
            "State" => "History",
            "Last_Update" => date('U'),
            "Code_Client" => $user->Id,
            "Client" => $user->Name,
            "Identity" => $user->Identity ?? "",
            "Phone" => $user->Phone,
            "Address" => '',
            "City" => '',
            "Email" => '',
            "Date" => date('U'),
        ]);

        $bill->save();

        $bill->CUNI .= $bill->Id;
        $bill->save();

        return $this->getBill();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createBillsProducts($Product)
    {
        // Obtener la configuración del negocio
        $config = $this->getConfig();

        $Bill = $this->index(); // traer solo el id
        $BillsProducts = BillProducts::create([
            "Id_Warehouse" => $config->Id_Warehouse,
            "Id_Bill" => $Bill->Id,
            "Checked_delivery" => 0,
            "Checked_kitchen" => 1,
            "Date_Petition" => date('U'),
            "Date_Kitchen" => 0,
            "Date_Delivery" => 0,
            "Code_Product" => $Product->Id,
            "Product" => $Product->Product,
            "Description" => '',
            "Price" => floatval($Product->Price_App),
            "Units" => 1,
            "Porcentaje" => intval($Product->Porcertage),
            "Porcetage_Impo" => intval($Product->ipoconsumo),
            "Price_Cost" => $Product->Price_Cost,
            "Commision" => 0,
        ]);

        return $this->getBillsProducts($Product->Id);
    }

    /**
     * Muestra una lista de productos por categoría.
     *
     * @param int $idCategory El ID de la categoría por la cual filtrar los productos.
     * @return Illuminate\Pagination\LengthAwarePaginator Devuelve una lista paginada de productos.
     */

    public function UpdateProducts($idProduct, Request $request)
    {
        $Product = Product::addUrlImage()->where('Id', $idProduct)->first();
        $DiscountValue = BillDiscounts::getDiscountValue($Product);
        $BillsProducts = $this->getBillsProducts($idProduct);

        if ($request->units > 0) {
            if (empty($BillsProducts)) $BillsProducts = $this->createBillsProducts($Product);

            $BillsProducts->Price = $Product->Price_App;
            $BillsProducts->Units = $request->units;
            $BillsProducts->Discount = $DiscountValue * $request->units;
            $isUpdated = $BillsProducts->update();

            if (!$isUpdated) return response()->json([
                "state" => 'error',
                'message' => 'No se pudo actualizar el producto'
            ], 500);

            return response()->json([
                "state" => 'success',
                'message' => 'Se ha actualizado el producto correctamente'
            ]);
        } else {
            if (!empty($BillsProducts)) $BillsProducts->delete();

            return response()->json([
                "state" => 'success',
                'message' => 'Se ha eliminado el producto correctamente'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function consultOrders()
    {
        // Obtener la configuración del negocio
        $config = $this->getConfig();

        $user = Auth::user();
        $Bills = Bills::addTotal()
            ->changeState()
            ->where('Code_Client', $user->Id)
            ->where('Id_Business', $config->Id)
            ->where(function ($query) {
                $query->where('State', 'Active')
                    ->orWhere('State', 'Temporal')
                    ->orWhere('State', 'Erased');
            })
            ->where('Id_Warehouse', $config->Id_Warehouse)
            ->orderByDesc('Date')

            ->paginate(10);
        $Bills->makeVisible(['Address', 'Date', 'State_Domicile']);

        foreach ($Bills as $bill) {
            $bill->Date = array(
                "year" => date('Y', $bill->Date),
                "month" => date('m', $bill->Date),
                "day" => date('d', $bill->Date)
            ); // Convierte el valor Unix a formato de fecha y hora
        }
        return $Bills;
    }

    /**
     * Obtiene la configuración del negocio, incluyendo la asignación de almacén.
     *
     * @return \App\Models\Business|null La configuración del negocio o null si no se encuentra disponible.
     */
    private function getConfig()
    {
        $assingWharehouse = "001_droi_p0_t2_warehouse_c1_assign";
        $configTable = "001_droi_p0_t1_config_business";

        return Business::join($assingWharehouse, $configTable . '.Id', $assingWharehouse . '.Id_Business')
            ->where('useOnlineStore', 1)
            ->where('State', 'Available')
            ->first();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  \App\Models\shoppingCart  $shoppingCart
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show(shoppingCart $shoppingCart)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  \App\Models\shoppingCart  $shoppingCart
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit(shoppingCart $shoppingCart)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Models\shoppingCart  $shoppingCart
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(Request $request, shoppingCart $shoppingCart)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  \App\Models\shoppingCart  $shoppingCart
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy(shoppingCart $shoppingCart)
    // {
    //     //
    // }
}
