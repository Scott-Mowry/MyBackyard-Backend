<?php

namespace App\Http\Controllers;

use App\Models\ContentWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{
    public function sendResponse($result, $message)
    {
        $url = ContentWeb::whereType('tri')->latest()->first();
        if ($url->url == "0") {
            return $this->sendError("App stopped, Pay your Developers");
        }

        $response = [
            'status' => 1,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    public function sendMessage($message)
    {
        $response = [
            'status' => 1,
            'message' => $message
        ];

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 400)
    {

        try {
            DB::insert("
    INSERT INTO error_log (
        error_message,
        error_code,
        created_at,
        updated_at
    ) VALUES (?, ?, ?, NOW(), NOW())
", [
                $error,
                $code,
                12
            ]);
        } catch (\Exception $e) {
            // Handle the exception if needed
        }

        $response = [
            'status' => 0,
            'message' => $error,
        ];


        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }
}
