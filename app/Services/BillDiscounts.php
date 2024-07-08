<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Discounts;

class BillDiscounts
{
    public static function getCurrentPrice($products)
    {
        $discounts = BillDiscounts::getDiscounts();
        
        // return $discounts;
        $idGroups = array_column($discounts["group"]->toArray(), 'Type_Value');
        $idItems = array_column($discounts["item"]->toArray(), 'Type_Value');
 
        foreach ($products as $product){

            $IndexDiscByGroup = array_search($product['Code_Group'], $idGroups);
            $IndexDiscByItem = array_search($product['Id'], $idItems);

            $hasDiscToAll = !($discounts["all"]->isEmpty());
            $hasDiscByGroup = ($IndexDiscByGroup !== false);
            $hasDiscByItem = ($IndexDiscByItem !== false);

            $applyDiscountToAll = $hasDiscToAll && !$hasDiscByGroup && !$hasDiscByItem;
            $ApplyDiscountByGroup = $hasDiscByGroup && !$hasDiscByItem;

            if ($applyDiscountToAll) $discountToApply = $discounts["all"][0];
            elseif ($ApplyDiscountByGroup) $discountToApply = $discounts["group"][$IndexDiscByGroup];
            elseif ($hasDiscByItem) $discountToApply = $discounts["item"][$IndexDiscByItem];

            $product->Current_Price = (isset($discountToApply) && !is_null($discountToApply)) 
                ? BillDiscounts::calculateCurrentPrice($product, $discountToApply) 
                : $product->Previous_Price;            
        }

        return $products;
    }

    private static function getDiscounts()
    {
        $Discounts = Discounts::where("Apply", "=", "app")
            ->where("Activation", "=", "automatic")
            ->where("Discount", "!=", "quantity")
            ->where("State", "=", "enabled")
            ->get();

        $Discounts = BillDiscounts::checkDiscountValidity($Discounts);

        $discAll = $Discounts->where('Type', 'all')->values();
        $discByGroup = $Discounts->where('Type', 'group')->values();
        $discByItem = $Discounts->where('Type', 'item')->values();

        return [
            "all" => $discAll,
            "group" => $discByGroup,
            "item" => $discByItem
        ];
    }

    private static function checkDiscountValidity($Discounts)
    {
        $excludedIds = [];
        foreach ($Discounts as $key => $Discount) {
            switch ($Discount->Repeated) {
                case 'date':
                    if ($Discount->Repeat_Value < time()) {
                        $Discount->State = "disabled";
                        $Discount->save();

                        array_push($excludedIds, $Discount->Id);
                    }
                    break;
                case 'daily':
                    # code...
                    break;
                case 'personalized':
                    $currentDate = Carbon::now();
                    $dayOfWeekNumeric = $currentDate->dayOfWeek;
                    $daysDiscount = json_decode("[".$Discount->Repeat_Value."]");
                    $hasDicountToday = array_search($dayOfWeekNumeric, $daysDiscount);

                    if ($hasDicountToday == false) array_push($excludedIds, $Discount->Id);
                    break;
            }
        }
        
        $filteredDiscounts = $Discounts->except($excludedIds);
        
       return $filteredDiscounts;
    }

    private static function calculateCurrentPrice($product, $discountsItem)
    {
        switch ($discountsItem['Discount']) {
            case 'porcentage':
                $discountPercentage = $discountsItem['Discount_Value_1'];
                $discountAmount  = intval($product->Previous_Price) * ($discountPercentage / 100);
                $Current_Price = number_format(
                    intval($product->Previous_Price) - $discountAmount, 3,  '.', ''
                ) ;
                break;
            case 'value':
                $discountAmount  = intval($discountsItem['Discount_Value_1']);
                $Current_Price = number_format(
                    intval($product->Previous_Price) - $discountAmount, 3, '.', ''
                ) ;
                break;
        }

        return $Current_Price;
    }

    public static function getDiscountValue($product)
    {
        $discounts = BillDiscounts::getDiscounts();

        $idGroups = array_column($discounts["group"]->toArray(), 'Type_Value');
        $idItems = array_column($discounts["item"]->toArray(), 'Type_Value');

        $IndexDiscByGroup = array_search($product['Code_Group'], $idGroups);
        $IndexDiscByItem = array_search($product['Id'], $idItems);

        $hasDiscToAll = !($discounts["all"]->isEmpty());
        $hasDiscByGroup = ($IndexDiscByGroup !== false);
        $hasDiscByItem = ($IndexDiscByItem !== false);

        $applyDiscountToAll = $hasDiscToAll && !$hasDiscByGroup && !$hasDiscByItem;
        $ApplyDiscountByGroup = $hasDiscByGroup && !$hasDiscByItem;

        if ($applyDiscountToAll) $discountToApply = $discounts["all"][0];
        elseif ($ApplyDiscountByGroup) $discountToApply = $discounts["group"][$IndexDiscByGroup];
        elseif ($hasDiscByItem) $discountToApply = $discounts["item"][$IndexDiscByItem];

        $product->Current_Price = (isset($discountToApply) && !is_null($discountToApply)) 
                ? BillDiscounts::calculateCurrentPrice($product, $discountToApply) 
                : $product->Previous_Price;

        return $product->Previous_Price - $product->Current_Price;
    }
}