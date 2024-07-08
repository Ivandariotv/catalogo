<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    protected $table = "001_droi_p2_t1_clients";
    protected $primaryKey = 'Id';
    // protected $rememberTokenName = 'mi_token';
    public $timestamps = false;

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Id_Business',
        'name',
        'Phone',
        'password',
        'useApp',
        'Date_Register',
        'Date_Update',
        'sendNotification'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "Id_Business",
        "Id_Group",
        "Id_adviser",
        "Type_Price",
        "Spokesman",
        "Identity",
        "Email",
        "password",
        "photograph",
        "Address",
        "City",
        "Number_Card",
        "Date_Register",
        "Date_Update",
        "Birthday",
        "dateBirthday",
        "TypeCC",
        "Job",
        "Fund",
        "bill_x_day",
        "Remision_x_day",
        "Type_Client",
        "Geo_L",
        "Geo_A",
        "client_points",
        "Id_List_Price",
        "User_Register",
        "Adviser_Asign",
        "Type_Person",
        "Responsable",
        "Self_Retainer",
        "Phone2",
        "File",
        "Observation",
        "State",
        "sendNotification",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Get a custom query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query): Builder
    {
        return new class($query) extends Builder
        {
            public function addUrlImage(): Builder
            {
                return $this->selectRaw(
                    "*, 
                    if(
                        COALESCE(`photograph`, '') = '', 
                        NULL, 
                        CONCAT('" . env('ASSETS_GESADMIN') . "', '/Clients/', `photograph`)
                    ) AS  `UrlImage`"
                );
            }
        };
    }
}
