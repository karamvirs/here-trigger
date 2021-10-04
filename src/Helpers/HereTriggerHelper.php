<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class HereTriggerHelper {
    
    public static function userAge($data)
    {
        Log::info(__FUNCTION__);
        return 35;
    }
    public static function userNextBirthday($data)
    {
        Log::info(__FUNCTION__);
        return Carbon::now()->addMonth();
    }
    public static function userPrevOrderDate($data)
    {
        Log::info(__FUNCTION__);
        return Carbon::now()->subDays(8);
    }
    public static function userTotalSpendTillDate($data)
    {
        Log::info(__FUNCTION__);
        return 1400;
    }
    public static function userTotalOrderCount($data)
    {
        Log::info(__FUNCTION__);
        return 5;
    }
    public static function orderTotal($data)
    {
        Log::info(__FUNCTION__);
        return false;
    }
    public static function wishlistItemsCount($data)
    {
        Log::info(__FUNCTION__);
        return false;
    }
}