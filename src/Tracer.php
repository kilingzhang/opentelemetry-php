<?php


namespace Kilingzhang\OpenTelemetry;


class Tracer
{
    /**
     * @var array
     */
    private static $traces = [];

    public static function alwaysSample()
    {
        self::setSampleRatioBased(1);
    }

    public static function neverSample()
    {
        self::setSampleRatioBased(0);
    }

    public static function setSampleRatioBased(int $ratioBased)
    {
        if (function_exists('opentelemetry_set_sample_ratio_based')) {
            opentelemetry_set_sample_ratio_based($ratioBased);
        }
    }

    /**
     * @param string $name
     * @param array $attributes key => value
     * @param int $timestamp
     */
    public static function addEvent(string $name, array $attributes, int $timestamp = 0)
    {
        empty($timestamp) && $timestamp = time();
        if (function_exists('opentelemetry_add_event')) {
            opentelemetry_add_event($name, $attributes, $timestamp);
        }
    }
}