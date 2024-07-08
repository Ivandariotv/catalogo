<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class PhoneWithUseApp implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Verifica si el número de teléfono existe y si useApp es igual a 1
        return !(User::where('Phone', $value)->where('useApp', 1)->exists());
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El campo phone ya ha sido tomado';
    }

    public function useApp($phone) // : Returntype 
    {
        return User::where('Phone', $phone)
        ->first();
    }
}
