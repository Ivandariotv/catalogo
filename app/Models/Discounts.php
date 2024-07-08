<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discounts extends Model
{
    protected $table = "001_droi_p3_t3_discounts";
    protected $primaryKey = 'Id';
    public $timestamps = false;
    use HasFactory;
}
