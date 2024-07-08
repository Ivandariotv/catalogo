<?php

namespace App\Http\Controllers;

use App\Models\Addresses;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\GeolocationController;


class AddressesController extends Controller
{
    /**
     * Obtiene las direcciones del usuario.
     *
     * @return Array Contiene las direcciones del usuario.
     */
    public function show()
    {
        $user = Auth::user();

        $addresses = Addresses::where('client_id', $user->Id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->paginate();

        $addresses->getCollection()->transform(function ($value) {
            $value->delivery_price = $this->calculateDeliveryPrice($value->street1, $value->city, $value->state, $value->country);
            return $value;
        });

        return $addresses;
    }

    /**
     * Calcula el precio de envio de una dirección.
     * @param string $street1 Dirección de la calle.
     * @param string $city Ciudad.
     * @param string $state Departamento.
     * @param string $country País.
     */
    public function calculateDeliveryPrice($street1, $city, $state, $country)
    {
        $destination_addresses = "$street1, $city, $state, $country";
        $geolocationController = new GeolocationController;
        $distance = $geolocationController->calculateDistance($destination_addresses);
        $delivery_price = null;

        if ($distance->getStatusCode() == 200) {
            $business = Business::where('useOnlineStore', 1)->first();

            /// Traer datos de gesadmin
            $minimumPrice = $business['minimum_fee'];

            /// valida si la hora ronda entre las 6:00 am y las 7:00 pm
            if (date('H') >= 6 && date('H') <= 19) {
                $pricePerKilometer = $business['daytime_kilometer_rate'];
            } else {
                $pricePerKilometer = $business['kilometer_night_fee'];
            }

            $distance = $distance->original['message']['value'];

            $price = intval(($distance / 1000) * $pricePerKilometer);
            $redondeado = round($price / 100) * 100;

            if ($redondeado >= $minimumPrice) {
                $delivery_price = $redondeado;
            } else {
                $delivery_price = $minimumPrice;
            }
        }

        return $delivery_price;
    }

    /**
     * crea una dirección al usuario.
     *
     * @return bool Contiene las direcciones creada.
     */
    public function store(Request $request)
    {
        $destination_addresses = "{$request->input('street1')}, {$request->input('city')}, {$request->input('state')}, {$request->input('country')}";

        $geolocationController = new GeolocationController;
        $distance = $geolocationController->calculateDistance($destination_addresses);

        if ($distance->getStatusCode() != 200) {
            return $distance;
        }

        $user = Auth::user();

        $address = new Addresses();
        $address->client_id = $user->Id;
        $address->street1 = $request->input('street1');
        $address->street2 = $request->input('street2');
        $address->city = $request->input('city');
        $address->state = $request->input('state');
        $address->country = $request->input('country');
        $address->postalCode = $request->input('postalCode');
        $address->phone = $request->input('phone');
        $address->save();

        $address->save();

        return response()->json([
            "error" => null,
            "message" => $address,
        ], 201);
    }

    /**
     * actualiza una dirección del usuario.
     *
     * @return bool Contiene las direcciones actualizada,
     */
    public function update(Request $request, $id)
    {
        $destination_addresses = "{$request->input('street1')}, {$request->input('city')}, {$request->input('state')}, {$request->input('country')}";

        $geolocationController = new GeolocationController;
        $distance = $geolocationController->calculateDistance($destination_addresses);

        if ($distance->getStatusCode() != 200) {
            return $distance;
        }

        $address = Addresses::findOrFail($id);
        $address->street1 = $request->input('street1');
        $address->street2 = $request->input('street2');
        $address->city = $request->input('city');
        $address->state = $request->input('state');
        $address->country = $request->input('country');
        $address->postalCode = $request->input('postalCode');
        $address->phone = $request->input('phone');
        $address->save();

        return response()->json([
            "error" => null,
            "message" => $address,
        ], 200);
    }

    /**
     * Elimina una dirección del usuario.
     *
     * @return bool Contiene las direcciones actualizada,
     */
    public function delete($id)
    {
        $address = Addresses::find($id);
        $address->status = 'erased';
        $address->save();
    }
}
