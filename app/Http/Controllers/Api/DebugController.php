<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DebugController extends Controller
{
    public function debugTimezone()
    {
        try {
            // Cek timezone MySQL
            $mysqlTimezone = DB::select("SELECT @@global.time_zone as global_tz, @@session.time_zone as session_tz")[0];
            
            // Cek waktu MySQL
            $mysqlTime = DB::select("SELECT NOW() as mysql_now, UTC_TIMESTAMP() as mysql_utc")[0];
            
            // Cek timezone PHP
            $phpTimezone = date_default_timezone_get();
            $phpTime = date('Y-m-d H:i:s');
            
            // Cek Carbon
            $carbonTime = Carbon::now()->format('Y-m-d H:i:s');
            $carbonWib = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
            
            return response()->json([
                'mysql' => [
                    'global_timezone' => $mysqlTimezone->global_tz,
                    'session_timezone' => $mysqlTimezone->session_tz,
                    'mysql_now' => $mysqlTime->mysql_now,
                    'mysql_utc' => $mysqlTime->mysql_utc,
                ],
                'php' => [
                    'timezone' => $phpTimezone,
                    'time' => $phpTime,
                ],
                'carbon' => [
                    'default' => $carbonTime,
                    'wib' => $carbonWib,
                ],
                'laravel_config' => config('app.timezone'),
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}