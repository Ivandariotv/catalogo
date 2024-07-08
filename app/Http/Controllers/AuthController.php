<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Client;
use App\Models\Business;
use App\Models\User;
use App\Models\Addresses;
use \stdClass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Rules\PhoneWithUseApp;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    /**
     * Crea un nuevo usuario con el nombre, número de teléfono y contraseña proporcionados, y devuelve un token de acceso.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP que contiene el nombre, número de teléfono y contraseña del usuario.
     * @return \Illuminate\Http\Response Devuelve una respuesta JSON que contiene los datos del nuevo usuario y un token de acceso, o un mensaje de error y un código de estado HTTP 401 si los datos de entrada no pasan la validación. validation.
     */
    public function register(Request $request)
    {
        // Obtener la configuración del negocio
        $config = $this->getConfig();

        $validator = Validator::make($request->all(), [
            'name' =>  'required|string|max:255',
            "phone" => [
                'required',
                'string',
                // 'unique:001_droi_p2_t1_clients',
                new PhoneWithUseApp(),
                'regex:/^\d{3}\d{3}\d{4}$/',
            ],//'required|string|unique:001_droi_p2_t1_clients|regex:/^\d{3}\d{3}\d{4}$/',
            "password" =>   'required|string| min:6|max:6',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 401);

        $PhoneWithUseApp = new PhoneWithUseApp();
        $user = $PhoneWithUseApp->useApp($request->phone);

        if (!empty($user) && !$user->useApp) {
            // return 'Registrado, no usa app';
            $user->name = $request->name;
            $user->Phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->useApp = 1;
            $user->Date_Update = Carbon::now()->timestamp;
            $user->save();
        }else{
            // return 'No registrado';
            $user = user::create([
                "Id_Business" => $config->Id,
                "name" => $request->name,
                "Phone" => $request->phone,
                "password" => Hash::make($request->password),
                "useApp" => 1,
                "Date_Register" => Carbon::now()->timestamp
            ]);
        }  
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "accessToken" => $token,
            "expiresIn" => -1,
        ]);
    }

    /**
     * Inicia sesión del usuario con el número de teléfono y la contraseña proporcionados, y devuelve un token de acceso.
     *
     * @param Illuminate\Http\Request $request La solicitud HTTP que contiene el número de teléfono y la contraseña del usuario
     * @return Illuminate\Http\Response Retorna una respuesta JSON que contiene un mensaje de éxito y un token de acceso si el usuario se autentica, o un mensaje de error y un código de estado HTTP 401 si la autenticación falla.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('phone', 'password');    
        
        // Obtener el usuario por número de teléfono y verificar si useApp es igual a 1
        $user = User::where('phone', $credentials['phone'])->where('useApp', 1)->first();
        if (!$user) return response()->json(["message" => 'Unauthorized'], 401);

        if (!Auth::attempt($credentials)) return response()->json(["message" => 'Unauthirized'], 401);

        $user = Auth::user();
        $userAgent = $request->header('User-Agent');
        $token = $user->createToken($userAgent)->plainTextToken;

        return response()->json([
            "accessToken" => $token,
            "expiresIn" => -1,
        ]);
    }

    /**
     * Logout the authenticated user and delete their API tokens.
     * @return array Returns a JSON response containing a success message and the number of deleted tokens.
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();;

        return [
            "message" => 'You Have successfully logged out and the token was successfully deleted',
        ];
    }

    /**
     * Recupera la contraseña del usuario con el número de teléfono proporcionado.
     *
     * @param Illuminate\Http\Request $request La solicitud HTTP que contiene el número de teléfono del usuario.
     * @return Illuminate\Http\Response Devuelve una respuesta JSON que contiene un mensaje de éxito si el usuario existe, o un mensaje de error y un código de estado HTTP 401 si el usuario no existe.
     */
    public function recoverPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // "phone" => 'required|string|exists:001_droi_p2_t1_clients|regex:/^\d{3}\d{3}\d{4}$/',
            "phone" => [
                'required',
                'string',
                // 'exists:001_droi_p2_t1_clients',
                new PhoneWithUseApp(),
                'regex:/^\d{3}\d{3}\d{4}$/',
            ],
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 401);
        $user = user::where('Phone', $request->phone)->first();

        $numericRandom = '';
        $characters = '0123456789';

        for ($i = 0; $i < 4; $i++) {
            $numericRandom .= $characters[rand(0, strlen($characters) - 1)];
        }

        $user->verificationCode = Hash::make($numericRandom);
        $user->save();

        $url = "https://api103.hablame.co/api/sms/v3/send/priority";
        $account = "10017783";
        $apiKey = "pSx3k4rLqOFS2Rxfxd3r0GvNfieGzd";
        $token = "3fe461b1a9b270b64c3977b6bc05d8df";
        $business = Business::where('useOnlineStore', 1)->first();

        $data = [
            "toNumber" => $user->Phone,
            "sms" => "$numericRandom es tu codigo de verificacion temporal de $business->Name_Business. no la compartas con nadie.",
            "flash" => "0",
            "sc" => "890202",
            "request_dlvr_rcpt" => "0"
        ];

        $options = [
            "http" => [
                "header" => "Account: $account\r\n" .
                    "ApiKey: $apiKey\r\n" .
                    "Token: $token\r\n" .
                    "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);

        try {
            $response = file_get_contents($url, false, $context);
            $data = json_decode($response, true);
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }

        return response()->json(["message" => "El código de verificación se ha enviado correctamente."]);
    }

    /**
     * Valida el código de verificación del usuario con el número de teléfono proporcionado.
     *
     * @param Illuminate\Http\Request $request La solicitud HTTP que contiene el número de teléfono y el código de verificación del usuario.
     * @return Illuminate\Http\Response Devuelve una respuesta JSON que contiene un mensaje de éxito y un token de acceso si el código de verificación es correcto, o un mensaje de error y un código de estado HTTP 401 si el código de verificación es incorrecto.
     */
    public function validateVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "phone" => 'required|string|exists:001_droi_p2_t1_clients|regex:/^\d{3}\d{3}\d{4}$/',
            "verificationCode" => 'required|string|min:4|max:4',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 401);

        $user = user::where('Phone', $request->phone)->first();

        if (Hash::check($request->verificationCode, $user->verificationCode)) {
            $user->verificationCode = null;
            $user->save();

            $userAgent = $request->header('User-Agent');
            $token = $user->createToken($userAgent)->plainTextToken;
            $user->verificationCode = $token;

            return response()->json([
                "accessToken" => $token,
                "expiresIn" => -1,
            ]);
        } else {
            return response()->json(["message" => "El código de verificación es incorrecto."], 401);
        }
    }

    public function newPin(Request $request)
    {
        $userId = Auth::Id();
        $user = User::find($userId);

        $request->validate([
            "new_pin" => 'required|string|min:6|max:6'
        ]);

        // Hashear una contraseña
        $user->password = Hash::make($request->new_pin);
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "accessToken" => $token,
            "expiresIn" => -1,
        ]);
    }

    /**
     * Elimina la cuenta del usuario autenticado.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "password" =>   'required|string| min:6|max:6',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 401);

        $user = Auth::user();
        
        if (!Hash::check($request->password, $user->password)) return response()->json(["message" => 'Unauthirized123'], 401);
    
        $user->password = null;
        $user->photograph = null;
        $user->useApp = 0;
        $user->Date_Update = Carbon::now()->timestamp;
        $user->save();

        $address = Addresses::where('client_id', $user->Id)->delete(); 
        auth()->user()->tokens()->delete();

        return [
            "message" => 'Your account has been successfully deleted.',
        ];
    }
}
