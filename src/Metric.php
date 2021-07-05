<?php


namespace Kilingzhang\OpenTelemetry;


class Metric
{
    public static function metric($db, array $fields = [], array $tags = [], $timestamps = 0)
    {
        $microTime = microtime();
        $microTimeArr = explode(' ', $microTime);
        $sec = $microTimeArr[1];
        $micro = $microTimeArr[0];
        empty($timestamps) && $timestamp = intval($sec * 1000 + $micro * 1000);
        $level = 'metric';
        $data = [
            'level' => $level,
            'db' => $db,
            'trace_id' => \Kilingzhang\OpenTelemetry\OpenTelemetry::getTraceId(),
            'uid' => \Kilingzhang\OpenTelemetry\OpenTelemetry::getUserId(),
            'service_name' => \Kilingzhang\OpenTelemetry\OpenTelemetry::getServiceName(),
            'client_ip' => \Kilingzhang\OpenTelemetry\OpenTelemetry::getClientIp(),
            'server_ip' => \Kilingzhang\OpenTelemetry\OpenTelemetry::getServiceIp(),
            'timestamp' => $timestamp,
            'trace' => trace_debug(),
            'fields' => $fields,
            'tags' => $tags,
        ];

        $logName = OpenTelemetry::OTEL_LOG_DIR . $level . '.' . date('YmdH') . '.log';
        $message = json_encode($data, JSON_UNESCAPED_UNICODE);
        $strDirName = dirname($logName);
        if (!is_dir($strDirName)) {
            mkdir($strDirName, 0777, true);
        }
        error_log($message . "\n", 3, $logName);
    }
}