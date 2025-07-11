<?php

if (!function_exists('apiResponse')) {
    function apiResponse($data, $message = '', $code = 200)
    {
        return response()->json([
            'status' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
