<?php

namespace Core;

class Response {
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        if (is_array($data) && !isset($data['status'])) {
            $status = ($statusCode >= 200 && $statusCode < 300) ? 'success' : 'error';
            $data = ['status' => $status, 'data' => $data];
        }

        echo json_encode($data);
        exit();
    }

    public static function error($message, $statusCode = 400) {
        self::json(['status' => 'error', 'message' => $message], $statusCode);
    }
}
