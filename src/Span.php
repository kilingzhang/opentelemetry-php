<?php

namespace Kilingzhang\OpenTelemetry;

class Span
{
    private $name;
    private $kind;
    private $startTimeUnixNano;
    private $attributes = [];

    public function __construct($name, $kind = SpanKind::kInternal)
    {
        $this->name = $name;
        $this->kind = $kind;
        $this->startTimeUnixNano = OpenTelemetry::getUnixNano();
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function addAttribute($key, $value)
    {
        // 非字符串数字类型 则通过json转换成字符串
        !is_numeric($value) && !is_string($value) && $value = json_encode($value, JSON_UNESCAPED_UNICODE);

        if (empty($value)) {
            return false;
        }

        $this->attributes[] = [
            'key' => $key,
            'value' => $value,
        ];
        return true;
    }

    public function okEnd()
    {
        $this->end(SpanStatus::ok);
    }

    public function errorEnd($message)
    {
        $this->end(SpanStatus::error, $message);
    }

    private function end($statusCode, $statusMessage = '')
    {
        if (function_exists('opentelemetry_add_span')) {
            opentelemetry_add_span($this->name, $this->kind, $this->startTimeUnixNano, $statusCode, $statusMessage, $this->attributes);
        } else {
            Log::debug('opentelemetry_add_span', [
                'name' => $this->name,
                'start_time_unix_nano' => $this->startTimeUnixNano,
                'end_time_unix_nano' => OpenTelemetry::getUnixNano(),
                'attributes' => $this->attributes,
                'status' => [
                    'code' => $statusCode,
                    'message' => $statusMessage,
                ],
            ]);
        }
    }
}