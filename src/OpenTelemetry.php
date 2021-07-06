<?php

namespace Kilingzhang\OpenTelemetry;

class OpenTelemetry
{
    private static $OTEL_LOG_DIR = '/var/log/opentelemetry/'; //日志目录

    private static $debug = false;

    private static $traces = [];

    /**
     * @param $path
     */
    public static function setLogPath($path)
    {
        self::$OTEL_LOG_DIR = $path;
    }

    /**
     * @return string
     */
    public static function getLogPath()
    {
        return self::$OTEL_LOG_DIR;
    }

    /**
     * @return bool
     */
    public static function debug()
    {
        return self::$debug;
    }

    /**
     * @param $debug
     */
    public static function setDebug($debug)
    {
        self::$debug = $debug;
    }

    /**
     * @param string $traceParent
     * @param string $traceState
     * @param int $spanKind
     * @return bool
     */
    public static function startTracer($traceParent = "", $traceState = "", $spanKind = SpanKind::kConsumer)
    {
        if (self::debug()) {
            echo "\n", 'startTracer memory : ', memory_get_usage() / 1024 / 1024, 'M', "\n\n";
        }
        if (function_exists('opentelemetry_start_cli_tracer')) {
            opentelemetry_start_cli_tracer($traceParent, $traceState, $spanKind);
        }
        return true;
    }

    /**
     * @return bool
     */
    public static function endTracer()
    {
        if (function_exists('opentelemetry_shutdown_cli_tracer')) {
            opentelemetry_shutdown_cli_tracer();
        }
        if (self::debug()) {
            echo "\n", 'endTracer memory : ', memory_get_usage() / 1024 / 1024, 'M', "\n\n";
        }
        return true;
    }

    /**
     * @return string
     */
    public static function getTraceParent()
    {
        if (function_exists('opentelemetry_get_traceparent')) {
            return opentelemetry_get_traceparent();
        }
        return '';
    }

    /**
     * @return string
     */
    public static function getTraceState()
    {
        if (function_exists('opentelemetry_get_tracestate')) {
            return opentelemetry_get_tracestate();
        }
        return '';
    }

    /**
     * @return string
     */
    public static function getTraceId()
    {
        if (function_exists('opentelemetry_get_trace_id')) {
            return opentelemetry_get_trace_id();
        }
        return '';
    }

    /**
     * @return string
     */
    public static function getServiceName()
    {
        if (function_exists('opentelemetry_get_service_name')) {
            return opentelemetry_get_service_name();
        }
        return "";
    }

    /**
     * @return string
     */
    public static function getServiceIp()
    {
        if (function_exists('opentelemetry_get_service_ip')) {
            return opentelemetry_get_service_ip();
        }
        return "";
    }

    /**
     * @return mixed|string
     */
    public static function getClientIp()
    {
        return get_client_ip();
    }

    /**
     * @return string
     */
    public static function getUserId()
    {
        if (function_exists('opentelemetry_get_user_id')) {
            return opentelemetry_get_user_id();
        }
        return "";
    }

    /**
     * @param $userId
     */
    public static function setUserId($userId)
    {
        if (function_exists('opentelemetry_set_user_id')) {
            opentelemetry_set_user_id($userId);
        }
    }

    /**
     * @return int
     */
    public static function getPPid()
    {
        if (function_exists('opentelemetry_get_ppid')) {
            return opentelemetry_get_ppid();
        }
        return 0;
    }
}