<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Support\Str;

class GeolocationController extends Controller
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_MAPS_API_KEY');
    }

    /**
     * Calcula la distancia entre una ubicación de origen y una de destino utilizando la API de Google Maps Distance.
     *
     * @param string $destination Dirección del destino.
     *
     * @return JSON Un objeto JSON con la distancia entre ambas ubicaciones en formato de texto y valor numérico.
     *
     * @throws HttpException Si la API no responde correctamente o si ocurre algún error durante la validación.
     */
    public function calculateDistance($destination)
    {
        $business = Business::select('Address')->where('useOnlineStore', 1)->first();

        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json';
        $origin = urlencode($business->Address);
        $destination = urlencode($destination);

        $requestUrl = "{$url}?origins={$origin}&destinations={$destination}&key={$this->apiKey}";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        $elements = $response->rows[0]->elements[0];
        if ($elements->status == 'NOT_FOUND') {
            return response()->json([
                "error" => "Unprocessable Entity",
                "message" => 'Por favor, introduce una dirección válida. Corrígela e inténtalo de nuevo.',
            ], 422);
        }

        /// Traer distancia maxima del domicilio en la configuracion en gesadmin
        $limitDistance = 20000;

        if ($elements->distance->value > $limitDistance && $limitDistance != null) {
            return response()->json([
                "error" => "Unprocessable Entity",
                "message" => 'Ops, estas por fuera del rango de domicios.',
            ], 422);
        }

        /// traer la ciudades habilitadas para domicilios
        $validCities = array(
            "Villavicencio",
        );

        $validCity = false;
        $addresses = implode(', ', $response->destination_addresses);
        foreach ($validCities as $city) {
            if (Str::contains($addresses, $city)) {
                $validCity = true;
                break;
            }
        }

        if (!$validCity) {
            return response()->json([
                "error" => "Unprocessable Entity",
                "message" => 'Ops, estas por fuera del rango de domicios.',
            ], 422);
        }

        return response()->json([
            "error" => null,
            "message" => [
                "text" => $elements->distance->text,
                "value" => $elements->distance->value,
            ],
        ], 200);
    }
}
