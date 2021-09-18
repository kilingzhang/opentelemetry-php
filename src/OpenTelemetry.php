<?php

namespace Kilingzhang\OpenTelemetry;

class OpenTelemetry
{
    private static $OTEL_LOG_DIR = '/var/log/opentelemetry/'; //日志目录
    private static $tenant_id = 'vip'; //租户设置

    private static $debug = false;

    private static $traces = [];

    /**
     * map [trace_id] : uids
     * 在此uid内的用户所有请求都是必记录 且会上报debug信息
     * @var array
     */
    private static $allowDebugUids = [];

    public static function bootstrap()
    {
        Tracer::addResourceAttribute('deployment.tenant_id', self::getTenantId());
    }

    public static function getAllowDebugUids()
    {
        $traceId = Tracer::getTraceId();
        return empty(self::$allowDebugUids[$traceId]) ? [] : self::$allowDebugUids[$traceId];
    }

    public static function isAllowDebugUid()
    {
        return in_array(Tracer::getUserId(), self::getAllowDebugUids());
    }

    /**
     * @param array $uids
     * @return bool
     */
    public static function setAllowDebugUids($uids = [])
    {
        $traceId = Tracer::getTraceId();
        if (empty($traceId)) {
            return false;
        }
        self::$allowDebugUids[$traceId] = $uids;
        self::isAllowDebugUid() && Tracer::alwaysSample();
        return true;
    }

    /**
     * @param $path
     */
    public static function setLogPath($path)
    {
        self::$OTEL_LOG_DIR = $path;
    }

    /**
     * @param $path
     */
    public static function setTenantId($tenant_id)
    {
        self::$tenant_id = $tenant_id;

    }

    /**
     * @param $path
     */
    public static function getTenantId()
    {
        return self::$tenant_id;
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
        } else {
            Tracer::init();
            Tracer::parseTraceParent($traceParent);
            $traceState = explode(',', $traceState);
            foreach ($traceState as $item) {
                if (empty($item)) {
                    continue;
                }
                list($k, $v) = explode('=', $item);
                Tracer::addTraceState($k, $v);
            }
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
        } else {
            Tracer::reset();
        }
        if (self::debug()) {
            echo "\n", 'endTracer memory : ', memory_get_usage() / 1024 / 1024, 'M', "\n\n";
        }
        return true;
    }

    /**
     * @return string
     */
    public static function getServiceName()
    {
        if (function_exists('opentelemetry_get_service_name')) {
            return opentelemetry_get_service_name();
        } else {
            $serviceName = ini_get('opentelemetry.service_name');
            if (!empty($serviceName)) {
                return $serviceName;
            }
            $serviceName = $_SERVER['SERVER_NAME'];
            if (!empty($serviceName)) {
                return $serviceName;
            }
            return "cron";
        }
    }

    /**
     * @return string
     */
    public static function getServiceOperation()
    {
        if (!is_cli()) {
            return $_SERVER['SCRIPT_URL'];
        } else {
            return $_SERVER['SCRIPT_NAME'];
        }
    }

    /**
     * @return string
     */
    public static function getServiceIp()
    {
        if (function_exists('opentelemetry_get_service_ip')) {
            return opentelemetry_get_service_ip();
        } else {
            return get_server_ip();
        }
    }

    /**
     * @return mixed|string
     */
    public static function getClientIp()
    {
        return get_client_ip();
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

    public static function getUnixNano()
    {
        if (function_exists('opentelemetry_get_unix_nano')) {
            return opentelemetry_get_unix_nano();
        }
        $microTime = microtime();
        $microTimeArr = explode(' ', $microTime);
        $sec = $microTimeArr[1];
        $micro = $microTimeArr[0];
        return intval($sec * 1000 + $micro * 1000);
    }

    public static function getEnvironment()
    {
        if (function_exists('opentelemetry_get_environment')) {
            return opentelemetry_get_environment();
        }
        return 'production';
    }

    public static function isProEnv()
    {
        return self::getEnvironment() === 'production';
    }
}