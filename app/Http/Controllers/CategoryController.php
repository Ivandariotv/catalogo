<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Categories = Category::addUrlImage()
            ->hasSubcategory()
            ->where('Id_Parent', 0)
            ->orWhereNull('Id_Parent')
            ->orderBy('Display_Order', 'asc')
            ->paginate(100);

        return $Categories;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showSubcategory($idCategory)
    {
        $Subcategories = Category::addUrlImage()
            ->where('Id_Parent', $idCategory)
            ->paginate(10);

        return $Subcategories;
    }
}
