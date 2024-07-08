<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Banner extends Model
{
    protected $table = "001_droi_p3_t9_settings_adds";
    protected $primaryKey = 'Id';
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'Time',
        'Position',
        'Img'
    ];

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
                        COALESCE(`Img`, '') = '', 
                        NULL, 
                        CONCAT('" . env('ASSETS_GESADMIN') . "', '/001_droi_p3_t9_settings_adds/', `Img`)
                    ) AS  `UrlImage`"
                );
            }
        };
    }
}
