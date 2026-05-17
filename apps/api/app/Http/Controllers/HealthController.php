<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $database = 'disconnected';

        try {
            DB::connection()->getPdo();
            $database = 'connected';
        } catch (\Throwable) {
            // Health endpoint stays reachable while DB is starting; status reflects readiness.
        }

        $isConnected = $database === 'connected';

        return response()->json([
            'status' => $isConnected ? 'ok' : 'degraded',
            'service' => 'api',
            'database' => $database,
        ], $isConnected ? 200 : 503);
    }
}
