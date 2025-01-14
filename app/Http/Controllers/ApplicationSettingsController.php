<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class ApplicationSettingsController extends Controller
{
    /**
     * Obtiene los bancos de pse.
     *
     * @return array lista de bancos
     */
    public function index()
    {
        // obtiene BUSINESS_ID del .env
        $businessId = env('BUSINESS_ID', 1);
        $business = Business::where('id', $businessId)->first();

        $data = [
            "payment_methods" => [
                "cash_payment" => $business['cash_payment'] == 0 ? false : true,
                "pse_payment" => $business['pse_payment'] == 0 ? false : true,
                "credit_card_payment" => $business['credit_card_payment'] == 0 ? false : true,
            ],
            "whatsapp" => [
                "number" => $business['my_whatsapp'] ? "{$business['my_whatsapp']}" : null,
                "message" => $business['message_whatsapp'],
            ],
            "applications" => [
                "name" => $business['Name_Business'],

                "logo" => $business['LogoInterfaz']
                    ? env('ASSETS_GESADMIN') . "/001_droi_p0_t1_config_business/{$business['LogoInterfaz']}"
                    : null,

                "welcome_video" => $business['welcome_video']
                    ? env('ASSETS_GESADMIN') . "/Media_Application/{$business['welcome_video']}"
                    : null,

                "welcome_message" => $business['welcome_message'],
                "primary_button_background" => $business['primary_button_background'],
                "primaryButtonColor" => $business['primary_button_color'],
                "secondaryButtonBackground" => $business['secondary_button_background'],
                "secondaryButtonColor" => $business['secondary_button_color'],
                "colorRefreshIndicator" => $business['color_refresh_indicator'],
                "selectedOptionColor" => $business['selected_option_color'],
            ]
        ];

        return response()->json($data, 200);
    }
}
