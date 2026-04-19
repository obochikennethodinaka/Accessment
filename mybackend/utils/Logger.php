<?php

namespace Utils;

class Logger
{
    private static $logFile = __DIR__ . '/../logs/app.log';

    public static function log($level, $message)
    {
        $dir = dirname(self::$logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $date = date('Y-m-d H:i:s');
        $formattedMessage = "[$date] [$level] $message" . PHP_EOL;

        file_put_contents(self::$logFile, $formattedMessage, FILE_APPEND);
    }

    public static function error($message)
    {
        self::log("ERROR", $message);
    }

    public static function info($message)
    {
        self::log("INFO", $message);
    }
}
