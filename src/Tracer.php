<?php


namespace Kilingzhang\OpenTelemetry;


class Tracer
{
    /**
     *
     */
    public static function alwaysSample()
    {
        !OpenTelemetry::isAllowDebugUid() && self::setSampleRatioBased(1);
    }

    /**
     *
     */
    public static function neverSample()
    {
        !OpenTelemetry::isAllowDebugUid() && self::setSampleRatioBased(0);
    }

    /**
     * @param $ratioBased
     */
    public static function setSampleRatioBased($ratioBased)
    {
        if (!OpenTelemetry::isAllowDebugUid() && function_exists('opentelemetry_set_sample_ratio_based')) {
            opentelemetry_set_sample_ratio_based($ratioBased);
        }
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
    public static function getAlwaysSampledTraceParent()
    {
        list($version, $traceId, $spanId, $flag) = self::parseTraceParent();
        return "$version-$traceId-$spanId-01";
    }

    /**
     * @param $key
     * @param $value
     * @return false
     */
    public static function addTraceState($key, $value)
    {
        if (function_exists('opentelemetry_add_tracestate')) {
            return opentelemetry_add_tracestate($key, $value);
        }
        return false;
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
    public static function getUserId()
    {
        return self::getTraceStateValue('uid');
    }

    /**
     * @param $userId
     * @return false
     */
    public static function setUserId($userId)
    {
        return self::addTraceState('uid', $userId);
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
     * @param $name
     * @param  $attributes
     */
    public static function addEvent($name, $attributes = [])
    {
        if (is_array($attributes) && function_exists('opentelemetry_add_event')) {
            opentelemetry_add_event($name, $attributes);
        }
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public static function addResourceAttribute($key, $value)
    {
        // 非字符串数字类型 则通过json转换成字符串
        !is_numeric($value) && !is_string($value) && $value = json_encode($value, JSON_UNESCAPED_UNICODE);

        if (empty($value)) {
            return false;
        }

        if (function_exists('opentelemetry_add_resource_attribute')) {
            opentelemetry_add_resource_attribute($key, $value);
        }
        return true;
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public static function addAttribute($key, $value)
    {
        //非字符串数字类型 则通过json转换成字符串
        !is_numeric($value) && !is_string($value) && $value = json_encode($value, JSON_UNESCAPED_UNICODE);

        if (empty($value)) {
            return false;
        }

        if (function_exists('opentelemetry_add_attribute')) {
            opentelemetry_add_attribute($key, $value);
        }
        return true;
    }
}