<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\WarehouseProduct;
use App\Models\Bills;
use App\Models\BillProducts;
use App\Models\Business;
use App\Models\historyUnits;
use App\Services\BillDiscounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;


class ProductController extends Controller
{
    /**
     * Muestra una lista de productos home.
     *
     * @param int $idCategory El ID de la categoría por la cual filtrar los productos.
     * @return Illuminate\Pagination\LengthAwarePaginator Devuelve una lista paginada de productos.
     */

    public function index(Request $request) //: LengthAwarePaginator
    {
        // Obtener la configuración del negocio
        $config = $this->getConfig();

        // Obtener el token de autenticación de la solicitud
        $token = $request->bearerToken();

        // Verificar la autenticación del usuario a través del token
        $user = $this->isAuthenticated($token);

        // El token no es válido, maneja el error o la respuesta correspondiente
        if ($user['state'] == "Unauthorized") return response()->json([
            "message" => $user['state'] . "."
        ]);

        // Obtener la lista de productos que cumplen con los criterios
        $Product = $this->getProducts($config, $user, "rules:general");

        // Aplicar descuentos y devolver la lista paginada de productos
        return BillDiscounts::getCurrentPrice($Product);
    }

    public function showProduct(Request $request, $idProduct)
    {
        // Obtener la configuración del negocio
        $config = $this->getConfig();

        // Obtener el token de autenticación de la solicitud
        $token = $request->bearerToken();

        // Verificar la autenticación del usuario a través del token
        $user = $this->isAuthenticated($token);

        // El token no es válido, maneja el error o la respuesta correspondiente
        if ($user['state'] == "Unauthorized") return response()->json([
            "message" => $user['state'] . "."
        ]);

        $Product = Product::with([
            'productImages',
        ])->addUrlServeImage()->addUnitsGesadmin();

        $WarehouseProductTable = '001_droi_p1_t1_warehouse_inventory';
        $ProductTable = '001_droi_p1_t1_inventory_sele';

        if (isset($rules) && $rules == "general") {
            $Product = $Product->where($ProductTable . '.Price_App', '<>', 0)
                ->where($WarehouseProductTable . '.Id_Warehouse', $config->Id_Warehouse)
                ->where($WarehouseProductTable . '.State', 'Active')
                ->where(function ($query) use ($config) {
                    $query->where(function ($query) use ($config) {
                        $query->where('001_droi_p1_t1_inventory_sele.Type', 'Recurrent')
                            ->where(function ($query) use ($config) {
                                $query->selectRaw('
                                COALESCE(
                                    COUNT(*),
                                    0
                                ) AS units
                            ')
                                    ->from('001_droi_p1_t2_inventory_serial')
                                    ->where('001_droi_p1_t2_inventory_serial.State', 'Storage')
                                    ->whereRaw('001_droi_p1_t2_inventory_serial.Code_Item = 001_droi_p1_t1_inventory_sele.Id');
                            }, '>', 0);
                    })->orWhere('001_droi_p1_t1_inventory_sele.Type', '!=', 'Recurrent');
                });
        }

        $Product = $Product->where('Id', $idProduct)->first();

        return BillDiscounts::getCurrentPriceProduct($Product);
    }

    /**
     * Muestra una lista de productos por categoría.
     *
     * @param int $idCategory El ID de la categoría por la cual filtrar los productos.
     * @return Illuminate\Pagination\LengthAwarePaginator Devuelve una lista paginada de productos.
     */

    public function indexByCategory(Request $request, $idCategory) //: LengthAwarePaginator
    {
        // Validar el parámetro de entrada
        $validator = $this->validator([
            'idCategory' => [
                'input' => $idCategory,
                'rules' => 'required|numeric'
            ]
        ]);
        // Comprobar si la validación falla y devolver errores si es así
        if ($validator->fails()) return response()->json([
            'error' => $validator->errors()
        ], 400);
        // Obtener la configuración del negocio
        $config = $this->getConfig();
        // Obtener el token de autenticación de la solicitud
        $token = $request->bearerToken();
        // Verificar la autenticación del usuario a través del token
        $user = $this->isAuthenticated($token);
        // El token no es válido, maneja el error o la respuesta correspondiente
        if ($user['state'] == "Unauthorized") return response()->json([
            "message" => $user['state'] . "."
        ]);
        // Obtener la lista de productos que cumplen con los criterios
        $Product = $this->getProducts($config, $user, "rules:general", "idCategory:$idCategory");
        // Aplicar descuentos y devolver la lista paginada de productos
        return BillDiscounts::getCurrentPrice($Product);
    }

    /**
     * Muestra una lista de productos que coinciden con una palabra clave dada.
     *
     * @param string $keyword La palabra clave para buscar productos.
     * @return Illuminate\Pagination\LengthAwarePaginator Devuelve una lista paginada de productos.
     */

    public function indexByKeyword(Request $request, $keyword) //: LengthAwarePaginator
    {
        // Validar el parámetro de entrada
        $validator = $this->validator([
            'keyword' => [
                'input' => $keyword,
                'rules' => 'required|string'
            ]
        ]);

        // Comprobar si la validación falla y devolver errores si es así
        if ($validator->fails()) return response()->json([
            'error' => $validator->errors()
        ], 400);

        // Obtener la configuración del negocio
        $config = $this->getConfig();

        // Obtener el token de autenticación de la solicitud
        $token = $request->bearerToken();

        // Verificar la autenticación del usuario a través del token
        $user = $this->isAuthenticated($token);

        // El token no es válido, maneja el error o la respuesta correspondiente
        if ($user['state'] == "Unauthorized") return response()->json([
            "message" => $user['state'] . "."
        ]);

        // Obtener la lista de productos que cumplen con los criterios
        $Product = $this->getProducts($config, $user, "rules:general", "keyword:$keyword");

        // Aplicar descuentos y devolver la lista paginada de productos
        return BillDiscounts::getCurrentPrice($Product);
    }

    /**
     * Muestra una lista de productos recomendados basados en un producto específico.
     *
     * @param Request $request La solicitud HTTP.
     * @param int $idProduct El ID del producto para el cual se generarán recomendaciones.
     * @return array Devuelve un array de productos recomendados.
     */
    public function recommendProduct(Request $request, $idProduct)
    {
        // Validar el parámetro de entrada
        $validator = $this->validator([
            'idProduct' => [
                'input' => $idProduct,
                'rules' => 'required|numeric'
            ]
        ]);
        // Comprobar si la validación falla y devolver errores si es así
        if ($validator->fails()) return response()->json([
            'error' => $validator->errors()
        ], 400);
        // Obtener la configuración del negocio
        $config = $this->getConfig();
        // Obtener el token de autenticación de la solicitud
        $token = $request->bearerToken();
        // Verificar la autenticación del usuario a través del token
        $user = $this->isAuthenticated($token);
        // El token no es válido, maneja el error o la respuesta correspondiente
        if ($user['state'] == "Unauthorized") return response()->json([
            "message" => $user['state'] . "."
        ]);

        $recommendProduct = $this->getRecommendProducts($config, $user, "idProduct:$idProduct", "nItems:1");

        if ($recommendProduct->isEmpty()) {
            $Product = $this->getProducts($config, $user, "rules:general", "nItems:1", "order:rand");
            foreach ($Product as $value) {
                $value->randon = true;
            }

            return BillDiscounts::getCurrentPrice($Product);
        }

        $idsProducts = $recommendProduct->pluck('Id');
        $Product = $this->getProducts($config, $user, "productsIDs:$idsProducts");
        foreach ($Product as $value) {
            $value->randon = false;
        }

        return BillDiscounts::getCurrentPrice($Product);
    }

    /**
     * Muestra una lista de productos recomendados.
     *
     * @param Request $request La solicitud HTTP.
     * @return array Devuelve un array de productos recomendados.
     */
    public function recommendProducts(Request $request)
    {
        // Obtener la configuración del negocio
        $config = $this->getConfig();
        // Obtener el token de autenticación de la solicitud
        $token = $request->bearerToken();
        // Verificar la autenticación del usuario a través del token
        $user = $this->isAuthenticated($token);
        // El token no es válido, maneja el error o la respuesta correspondiente
        if ($user['state'] == "Unauthorized") return response()->json([
            "message" => $user['state'] . "."
        ]);

        $recommendProduct = $this->getRecommendProducts($config, $user, "nItems:2");

        if ($recommendProduct->isEmpty()) {
            $Product = $this->getProducts($config, $user, "rules:general", "nItems:2", "order:rand");
            foreach ($Product as $value) {
                $value->randon = true;
            }

            return BillDiscounts::getCurrentPrice($Product);
        } elseif ($recommendProduct->count() === 1) {
            $ProductRandon = $this->getProducts($config, $user, "rules:general", "nItems:1", "order:rand");
            foreach ($ProductRandon as $value) {
                $value->randon = true;
            }

            $idsProducts = $recommendProduct->pluck('Id');
            $Product = $this->getProducts($config, $user, "productsIDs:$idsProducts");
            foreach ($Product as $value) {
                $value->randon = false;
            }

            $Products2 = $Product->merge($ProductRandon);

            return BillDiscounts::getCurrentPrice($Products2);
        }

        $idsProducts = $recommendProduct->pluck('Id');
        $Product = $this->getProducts($config, $user, "productsIDs:$idsProducts");
        foreach ($Product as $value) {
            $value->randon = false;
        }

        return BillDiscounts::getCurrentPrice($Product);
    }

    /**
     * Realiza la validación de parámetros de entrada y personaliza los mensajes de error.
     *
     * @param array $param Un array que contiene las reglas de validación y los valores de entrada.
     * @return \Illuminate\Contracts\Validation\Validator El objeto validador.
     */
    private function validator($param)
    {
        [$input, $rules] = array_reduce(
            array_keys($param),
            function ($carry, $field) use ($param) {
                $carry[0][$field] = $param[$field]['input'];
                $carry[1][$field] = $param[$field]['rules'];
                return $carry;
            },
            [[], []]
        );

        $validator = Validator::make($input, $rules);

        $attributeNames = [
            'idCategory' => 'ID de la categoria',
            'keyword' => 'palabra clave',
            'idProduct' => 'ID del producto'
        ];

        $validator->setAttributeNames($attributeNames);

        $customMessages = [
            'idCategory.required' => 'El :attribute es obligatorio.',
            'idCategory.numeric' => 'El :attribute debe ser un número.',
            'keyword.required' => 'El :attribute es obligatorio.',
            'keyword.string' => 'El :attribute debe ser un número.',
            'idProduct.required' => 'El :attribute debe ser un número.',
            'idProduct.numeric' => 'El :attribute debe ser un número.'
        ];

        $validator->setCustomMessages($customMessages);

        return $validator;
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
     * Verifica la autenticación del usuario a través del token proporcionado.
     *
     * @param string|null $token El token de autenticación.
     * @return array Un arreglo con el estado de la autenticación y, si es válido, los datos del usuario.
     */
    private function isAuthenticated($token)
    {
        // ["authenticated.","Unauthenticated.","Unauthorized"]
        if ($token) {
            $user = Auth::guard('sanctum')->user();
            // El token no es válido
            if (!$user) return [
                "state" => "Unauthorized"
            ];
            // El usuario se autenticó correctamente
            else return [
                "state" => "authenticated",
                "data" => $user
            ];
        }
        return [
            "state" => "Unauthenticated"
        ];
    }

    /**
     * Obtiene una lista de productos que cumplen con los criterios especificados.
     *
     * @param object $config La configuración del negocio.
     * @param object $user El usuario autenticado.
     * @param string ...$otherParams Otros parámetros para filtrar y ordenar productos.
     * @return Illuminate\Database\Eloquent\Collection La lista de productos.
     */
    private function getProducts($config, $user, ...$otherParams)
    {
        foreach ($otherParams as $otherParam) {
            $nameVar = explode(':', $otherParam)[0];
            if ($nameVar == "idCategory") $$nameVar = explode(':', $otherParam)[1];
            if ($nameVar == "keyword") $$nameVar = explode(':', $otherParam)[1];
            if ($nameVar == "nItems") $$nameVar = explode(':', $otherParam)[1];
            if ($nameVar == "order") $$nameVar = explode(':', $otherParam)[1];
            if ($nameVar == "rules") $$nameVar = explode(':', $otherParam)[1];
            if ($nameVar == "productsIDs") $$nameVar = explode(':', $otherParam)[1];
        }

        $WarehouseProductTable = '001_droi_p1_t1_warehouse_inventory';
        $ProductTable = '001_droi_p1_t1_inventory_sele';

        $Product = Product::addUrlImage()
            ->addUnitsGesadmin();

        if (isset($rules) && $rules == "general") $Product = $Product->join(
            $WarehouseProductTable,
            $ProductTable . '.Id',
            $WarehouseProductTable . '.Id_Inventory'
        );
        if (isset($idCategory)) $Product = $Product->where($ProductTable . '.Code_Group', $idCategory);
        if (isset($keyword)) $Product = $Product->where('Product', 'LIKE', "%$keyword%");
        if (isset($productsIDs)) $Product = $Product->whereIn('Id', json_decode($productsIDs));

        if (isset($rules) && $rules == "general") {
            $Product = $Product->where($ProductTable . '.Price_App', '<>', 0)
                ->where($WarehouseProductTable . '.Id_Warehouse', $config->Id_Warehouse)
                ->where($WarehouseProductTable . '.State', 'Active')
                ->where(function ($query) use ($config) {
                    $query->where(function ($query) use ($config) {
                        $query->where('001_droi_p1_t1_inventory_sele.Type', 'Recurrent')
                            ->where(function ($query) use ($config) {
                                $query->selectRaw('
                                COALESCE(
                                    COUNT(*),
                                    0
                                ) AS units
                            ')
                                    ->from('001_droi_p1_t2_inventory_serial')
                                    ->where('001_droi_p1_t2_inventory_serial.State', 'Storage')
                                    ->whereRaw('001_droi_p1_t2_inventory_serial.Code_Item = 001_droi_p1_t1_inventory_sele.Id');
                            }, '>', 0);
                    })->orWhere('001_droi_p1_t1_inventory_sele.Type', '!=', 'Recurrent');
                });
        }

        if (isset($nItems)) $Product = $Product->limit($nItems);
        if (isset($order)) $Product = $Product->orderByRaw('RAND()');

        $Product = (isset($nItems) || isset($productsIDs))
            ? $Product->get()
            : $Product->orderByDesc('001_droi_p1_t1_inventory_sele.Id')->paginate(40);

        return $Product;
    }

    /**
     * Obtiene una lista de productos recomendados basados en los criterios especificados.
     *
     * @param object $config La configuración del negocio.
     * @param array $user La información del usuario autenticado.
     * @param string ...$otherParams Otros parámetros para filtrar y limitar productos recomendados.
     * @return Illuminate\Database\Eloquent\Collection La lista de productos recomendados.
     */
    private function getRecommendProducts($config, $user, ...$otherParams)
    {
        foreach ($otherParams as $otherParam) {
            $nameVar = explode(':', $otherParam)[0];
            if ($nameVar == "idProduct") $$nameVar = explode(':', $otherParam)[1];
            if ($nameVar == "nItems") $$nameVar = explode(':', $otherParam)[1];
        }

        $BTable = "001_droi_p3_t1_bills";
        $BPTable = "001_droi_p3_t1_bills_c1_products";
        $PTable = '001_droi_p1_t1_inventory_sele';

        $recommendProduct = Bills::addColumnsrecommend($PTable)
            ->join($BPTable, "$BTable.Id", "=", "$BPTable.Id_Bill")
            ->join($PTable, "$BPTable.Code_Product", '=', "$PTable.Id")

            ->where($PTable . '.Price_App', '<>', 0)

            ->where(function ($query) use ($config) {
                $query->where(function ($query) use ($config) {
                    $query->Where('001_droi_p1_t1_inventory_sele.Type', 'Recurrent')
                        ->where(function ($query) use ($config) {
                            $query->selectRaw('
                                COALESCE(
                                    SUM(
                                        IF(Type = "Add", Unit, 0) - IF(Type = "Remove", Unit, 0)
                                    ),
                                    0
                                ) AS units
                            ')
                                ->from('001_droi_p1_t1_inventory_sale_c2_products_history_units')
                                ->where('001_droi_p1_t1_inventory_sale_c2_products_history_units.Id_Warehouse', $config->Id_Warehouse)
                                ->whereRaw('001_droi_p1_t1_inventory_sale_c2_products_history_units.Code_Item = 001_droi_p1_t1_inventory_sele.Id');;
                        }, '>', 0);
                })->orWhere('001_droi_p1_t1_inventory_sele.Type', '!=', 'Recurrent');
            });

        if (isset($idProduct)) {
            $recommendProduct = $recommendProduct->whereIn(
                "$BTable.Id",
                function ($query) use ($idProduct, $BPTable) {
                    $query->select('Id_Bill')
                        ->from($BPTable)
                        ->where('Code_Product', $idProduct);
                }
            )->where("$PTable.Id", "<>", $idProduct);
        }

        if ($user['state'] == 'authenticated') {
            $recommendProduct = $recommendProduct->where('Code_Client', $user['data']->Id);
        }

        $recommendProduct = $recommendProduct->groupBy("$PTable.Product", "$PTable.Id")
            ->orderByDesc('total_compras')
            ->limit($nItems)
            ->get();

        return $recommendProduct;
    }
}
