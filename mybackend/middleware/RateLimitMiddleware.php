<?php

namespace Middleware;

use Core\Response;

class RateLimitMiddleware
{
    private static $limit = 100; // max requests
    private static $window = 3600; // time window in seconds (1 hour)
    private static $dir = __DIR__ . '/../logs/rate_limit/';

    public static function handle()
    {
        if (!is_dir(self::$dir)) {
            mkdir(self::$dir, 0777, true);
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $file = self::$dir . md5($ip) . '.json';
        $currentTime = time();
        $requests = [];

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            // Filter out old requests
            $requests = array_filter($data, function ($timestamp) use ($currentTime) {
                return ($currentTime - $timestamp) < self::$window;
            });
        }

        if (count($requests) >= self::$limit) {
            Response::error("Too many requests from this IP", 429);
        }

        $requests[] = $currentTime;
        file_put_contents($file, json_encode(array_values($requests)));
    }
}
