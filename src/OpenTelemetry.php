<?php

namespace Kilingzhang\OpenTelemetry;

class OpenTelemetry
{
    const OTEL_LOG_DIR = '/var/log/opentelemetry/'; //日志目录

    private static $debug = false;

    private static $traces = [];

    public static function debug(): bool
    {
        return self::$debug;
    }

    public static function setDebug($debug)
    {
        self::$debug = $debug;
    }

    public static function startTracer(string $traceParent = "", string $traceState = "", int $spanKind = SpanKind::kConsumer): bool
    {
        if (self::debug()) {
            echo "\n", 'startTracer memory : ', memory_get_usage() / 1024 / 1024, 'M', "\n\n";
        }
        if (function_exists('opentelemetry_start_cli_tracer')) {
            opentelemetry_start_cli_tracer($traceParent, $traceState, $spanKind);
        }
        return true;
    }

    public static function endTracer(): bool
    {
        if (function_exists('opentelemetry_shutdown_cli_tracer')) {
            opentelemetry_shutdown_cli_tracer();
        }
        if (self::debug()) {
            echo "\n", 'endTracer memory : ', memory_get_usage() / 1024 / 1024, 'M', "\n\n";
        }
        return true;
    }

    public static function getTraceId(): string
    {
        if (function_exists('opentelemetry_get_trace_id')) {
            return opentelemetry_get_trace_id();
        }
        return '';
    }

    public static function getServiceName(): string
    {
        if (function_exists('opentelemetry_get_service_name')) {
            return opentelemetry_get_service_name();
        }
        return "";
    }

    public static function getServiceIp(): string
    {
        if (function_exists('opentelemetry_get_service_ip')) {
            return opentelemetry_get_service_ip();
        }
        return "";
    }

    public static function getClientIp(): string
    {
        return get_client_ip();
    }

    public static function getUserId(): string
    {
        if (function_exists('opentelemetry_get_user_id')) {
            return opentelemetry_get_user_id();
        }
        return "";
    }

    public static function setUserId(string $userId)
    {
        if (function_exists('opentelemetry_set_user_id')) {
            opentelemetry_set_user_id($userId);
        }
    }

    public static function getPPid(): int
    {
        if (function_exists('opentelemetry_get_ppid')) {
            return opentelemetry_get_ppid();
        }
        return 0;
    }
}