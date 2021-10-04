<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class HereTriggerHelper {
    
    public static function reportScore($data)
    {
        Log::info(__FUNCTION__);
        return 750;
    }
    public static function reportTotalBalances($data)
    {
        Log::info(__FUNCTION__);
        return 500;
    }
    public static function reportNewNegativeItems($data)
    {
        Log::info(__FUNCTION__);
        return 5;
    }
    public static function reportAverageScore($data)
    {
        Log::info(__FUNCTION__);
        return false;
    }
    public static function userCreatedAt($data)
    {
        Log::info(__FUNCTION__);
        return Carbon::now()->subMonths(3);
    }
    public static function userOverriddenDecline($data)
    {
        Log::info(__FUNCTION__);
        return false;
    }
}