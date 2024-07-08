<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Bills;
use App\Models\MethodPay;
use App\Models\Addresses;
use App\Http\Controllers\AddressesController;

class PayUController extends Controller
{
    /**
     * Obtiene los bancos de pse.
     *
     * @return array lista de bancos
     */
    public function banckList()
    {
        $client = new Client();

        $response = $client->post(
            'https://' . env('PAYU_ENV') . '.payulatam.com/payments-api/4.0/service.cgi',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    "language" => "es",
                    "command" => "GET_BANKS_LIST",
                    "merchant" => [
                        "apiLogin" => env('PAYU_API_LOGIN'),
                        "apiKey" => env('PAYU_API_KEY')
                    ],
                    "test" => false,
                    "bankListInformation" => [
                        "paymentMethod" => "PSE",
                        "paymentCountry" => "CO"
                    ]
                ],
            ],
        );

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Consultar transaccion por transactionId.
     *
     * @return string Calculate the md5 hash of a string
     */
    public function queryByTransactionId(Request $request)
    {
        $client = new Client();

        $response = $client->post(
            'https://' . env('PAYU_ENV') . '.payulatam.com/reports-api/4.0/service.cgi',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    "test" => false,
                    "language" => "es",
                    "command" => "TRANSACTION_RESPONSE_DETAIL",
                    "merchant" => [
                        "apiLogin" => env('PAYU_API_LOGIN'),
                        "apiKey" => env('PAYU_API_KEY')
                    ],
                    "details" => [
                        "transactionId" => $request->transactionId,
                    ]
                ],
            ],
        );

        $user = Auth::user();
        $user->makeVisible(['Email', 'Address', 'Identity']);

        $Bill = Bills::addTotal()->addTaxes()
            ->where('State', 'History')
            ->where('Code_Client', $user->Id)
            ->first();

        if ($response->result->payload->state == "APPROVED") {
    
            $MethodPay = MethodPay::create([
                "Id_Source" => $Bill->Id, 
                "Id_Method" => 13, 
                "Value" => $Bill->total
            ]);
    
            $Bill->State = "Temporal";
            $Bill->Date_Petition = date('U');
        }else {
            $Bill->State = "Erased";
        }
        
        $isUpdated = $Bill->update();

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Realiza la solicitud de Pago - PSE
     */
    public function paymentRequestPSE(Request $request)
    {
        session_start();
        $user = Auth::user();
        $user->makeVisible(['Email', 'Address', 'Identity']);

        $Bill = Bills::addTotal()->addTaxes()
            ->where('State', 'History')
            ->where('Code_Client', $user->Id)
            ->first();

        if ($request['delivery']['scheduleDelivery'] == "immediate") {
            $Bill->Comment_Bill = "Entrega inmediato";
        } else {
            $Bill->Comment_Bill = "Entrega programada: " . $request['delivery']['scheduleDelivery'];
        }

        if ($request['delivery']['deliveryMethod'] == "domicile") {
            $address = Addresses::findOrFail($request['delivery']['addressId']);
            $Bill->Address = $address->street1;
            $Bill->City = $address->city;

            $addressesController = new AddressesController;
            $Bill->Domicile_Value = $addressesController->calculateDeliveryPrice($address->street1, $address->city, $address->state, $address->country);
        }
        
        $isUpdated = $Bill->update();

        $request->merge(['paymentMethod' => 'PSE']);
        // return $this->getJSONPaymentRequest($request);
        $client = new Client();
        $response = $client->post(
            'https://' . env('PAYU_ENV') . '.payulatam.com/payments-api/4.0/service.cgi',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $this->getJSONPaymentRequest($request, $user, $Bill, false),
            ],
        );

        
        
        // $MethodPay = MethodPay::create([
        //     "Id_Source" => $Bill->Id, 
        //     "Id_Method" => 13, 
        //     "Value" => $Bill->total
        // ]);

        // $Bill->State = "Temporal";
        // $isUpdated = $Bill->update();

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Realiza la solicitud de Pago - Credit Card
     */
    public function paymentRequestCreditCard(Request $request)
    {
        session_start();
        $user = Auth::user();
        $user->makeVisible(['Email', 'Address', 'Identity']);

        $Bill = Bills::addTotal()->addTaxes()
            ->where('State', 'History')
            ->where('Code_Client', $user->Id)
            ->first();


        $client = new Client();
        $response = $client->post(
            'https://' . env('PAYU_ENV') . '.payulatam.com/payments-api/4.0/service.cgi',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $this->getJSONPaymentRequest($request, $user, $Bill, true),
            ],
        );
        
        $MethodPay = MethodPay::create([
            "Id_Source" => $Bill->Id, 
            "Id_Method" => 13, 
            "Value" => $Bill->total
        ]);

        $Bill->State = "Temporal";
        $isUpdated = $Bill->update();

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Realiza la solicitud de Pago - Credit Card
     */
    public function paymentRequestOnDelivery(Request $request)
    {
        $user = Auth::user();
        $user->makeVisible(['Email', 'Address', 'Identity']);

        $Bill = Bills::addTotal()->addTaxes()
            ->where('State', 'History')
            ->where('Code_Client', $user->Id)
            ->first();

        $Bill->State = "Temporal";

        if ($request['delivery']['scheduleDelivery'] == "immediate") {
            $Bill->Comment_Bill = "Entrega inmediato";
        } else {
            $Bill->Comment_Bill = "Entrega programada: " . $request['delivery']['scheduleDelivery'];
        }

        if ($request['delivery']['deliveryMethod'] == "domicile") {
            $address = Addresses::findOrFail($request['delivery']['addressId']);
            $Bill->Address = $address->street1;
            $Bill->City = $address->city;

            $addressesController = new AddressesController;
            $Bill->Domicile_Value = $addressesController->calculateDeliveryPrice($address->street1, $address->city, $address->state, $address->country);
        }

        $Bill->update();

        return response()->json([
            "code" => "SUCCESS",
            "error" => null,
        ]);
    }

    /**
     * generar una firma digital basada en los parámetros de referencia de la transacción,
     * utilizada para asegurar la integridad y autenticidad de los datos transmitidos.
     *
     * @return string Calculate the md5 hash of a string
     */
    private function getSignature($referenceCode, $tx_value, $currency)
    {
        return md5(
            env('PAYU_API_KEY') . "~" . env('PAYU_MERCHANT_ID') . "~$referenceCode~$tx_value~$currency"
        );
    }

    private function getJSONPaymentRequest(Request $request, $user, $Bill, bool $useCreditCard)
    {
        $currency = "COP";
        $referenceCode = "GES_$Bill->Id";
        // $referenceCode = "TESTGES1$Bill->Id";

        $arrPaymentRequest = [
            "language" => "es",
            "command" => "SUBMIT_TRANSACTION",
            "merchant" => [
                "apiLogin" => env('PAYU_API_LOGIN'),
                "apiKey" => env('PAYU_API_KEY'),
            ],
            "transaction" => [
                "order" => [
                    "accountId" => env('PAYU_ACCOUNT_ID'),
                    "referenceCode" => $referenceCode,
                    "description" => "Payment test description",
                    "language" => "es",
                    "signature" => $this->getSignature($referenceCode, intval($Bill->total + $Bill->Domicile_Value), 'COP'),
                    // "notifyUrl" => "https://gesadmin.co/",
                    "additionalValues" => [
                        "TX_VALUE" => [
                            "value" => "" . intval($Bill->total + $Bill->Domicile_Value) . "",
                            "currency" => $currency
                        ],
                        "TX_TAX" => [
                            "value" => $Bill->Total_iva,
                            "currency" => $currency
                        ],
                        "TX_TAX_RETURN_BASE" => [
                            "value" => ($Bill->Total_iva != 0) ? $Bill->Subtotal + $Bill->Domicile_Value : 0,
                            "currency" => $currency
                        ]
                    ],
                    "buyer" => [
                        "merchantBuyerId" => $user->Id,
                        "fullName" =>  $user->Name,
                        "emailAddress" => $user->Email,
                        "contactPhone" => $user->Phone,
                        "dniNumber" => $user->Identity,
                        // "shippingAddress" => [
                        //     "street1" => "Cr 23 No. 53-50",
                        //     "street2" => "5555487",
                        //     "city" => "Bogotá",
                        //     "state" => "Bogotá D.C.",
                        //     "country" => "CO",
                        //     "postalCode" => "000000",
                        //     "phone" => "7563126"
                        // ]
                    ],
                ],
                "payer" => $request->payer,
                "extraParameters" => $request->extraParameters,
                "type" => "AUTHORIZATION_AND_CAPTURE",
                "paymentMethod" => $request->paymentMethod,
                "paymentCountry" => "CO",
                "deviceSessionId" => md5(session_id() . microtime()),
                "ipAddress" => $request->ip(),
                // "cookie" => "pt1t38347bs6jc9ruv2ecpv7o2",
                "userAgent" =>  $request->header('User-Agent'),
            ],
            "test" => false,
        ];

        if ($useCreditCard) $arrPaymentRequest['transaction']['creditCard'] = $request->creditCard;

        return $arrPaymentRequest;
    }
}
