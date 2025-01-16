<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $businessId = env('BUSINESS_ID', 1);
        $Banners = Banner::where('business_id', $businessId)
            ->addUrlImage()->get();
        return $Banners;
    }
}
