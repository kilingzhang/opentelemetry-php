<?php


namespace Kilingzhang\OpenTelemetry;


class Tracer
{
    /**
     * @var array
     */
    private static $traces = [];

    /**
     *
     */
    public static function alwaysSample()
    {
        self::setSampleRatioBased(1);
    }

    /**
     *
     */
    public static function neverSample()
    {
        self::setSampleRatioBased(0);
    }

    /**
     * @param $ratioBased
     */
    public static function setSampleRatioBased($ratioBased)
    {
        if (function_exists('opentelemetry_set_sample_ratio_based')) {
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
    public static function getSampledTraceParent()
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
     * @param $attributes
     * @param int $timestamp
     */
    public static function addEvent($name, $attributes, $timestamp = 0)
    {
        empty($timestamp) && $timestamp = time();
        if (function_exists('opentelemetry_add_event')) {
            opentelemetry_add_event($name, $attributes, $timestamp);
        }
    }
}