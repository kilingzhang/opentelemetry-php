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
    public static function getSampledTraceParent()
    {
        list($version, $traceId, $spanId, $flag) = self::parseTraceParent();
        return "$version-$traceId-$spanId-01";
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
     * @return array
     */
    public static function parseTraceStates()
    {
        $traceStates = [];
        $traceState = self::getTraceState();
        $traceState = explode(',', $traceState);
        foreach ($traceState as $item) {
            list($k, $v) = explode('=', $item);
            $traceStates[$k] = $v;
        }
        return $traceStates;
    }

    /**
     * @param array $states
     * @return string
     */
    public static function getTraceStateWithValues($states = [])
    {
        $traceState = '';
        $traceStates = self::parseTraceStates();
        $traceStates = array_merge($traceStates, $states);
        foreach ($traceStates as $k => $v) {
            $traceState = "$k=$v,";
        }
        return trim($traceState, ',');
    }

    /**
     * @param $key
     * @return string
     */
    public static function getTraceStateValue($key)
    {
        $traceStates = self::parseTraceStates();
        return empty($traceStates[$key]) ? '' : $traceStates[$key];
    }


    /**
     * version trace 版本 w3c 协议 默认 00
     * trace id
     * span id
     * flag 是否收集trace记录 00 不收集 01 收集
     * @return array
     */
    public static function parseTraceParent($traceParent = '')
    {
        empty($traceParent) && $traceParent = self::getTraceParent();
        $version = '00';
        $traceId = '';
        $spanId = '';
        $flag = '00';
        $parentTrace = self::getTraceParent();
        $parentTrace = explode('-', $parentTrace);
        if (!empty($parentTrace) && count($parentTrace) == 4) {
            list($version, $traceId, $spanId, $flag) = $parentTrace;
        }
        return [$version, $traceId, $spanId, $flag];
    }

    /**
     * @return string
     */
    public static function getTraceId()
    {
        list($version, $traceId, $spanId, $flag) = self::parseTraceParent();
        return $traceId;
    }

    /**
     * @return string
     */
    public static function getSpanId()
    {
        list($version, $traceId, $spanId, $flag) = self::parseTraceParent();
        return $spanId;
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        list($version, $traceId, $spanId, $flag) = self::parseTraceParent();
        return $version;
    }

    /**
     * @return string
     */
    public static function isSampled()
    {
        list($version, $traceId, $spanId, $flag) = self::parseTraceParent();
        return $flag == '01';
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