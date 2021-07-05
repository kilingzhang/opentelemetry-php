<?php


namespace Kilingzhang\OpenTelemetry;


class Log
{

    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    /**
     * 可任意级别记录日志。
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    private static function log($level, $message, array $context = array())
    {
        $microTime = microtime();
        $microTimeArr = explode(' ', $microTime);
        $sec = $microTimeArr[1];
        $micro = $microTimeArr[0];
        $timestamp = intval($sec * 1000 + $micro * 1000);
        $data = [
            'level' => $level,
            'message' => $message,
            'trace_id' => OpenTelemetry::getTraceId(),
            'uid' => OpenTelemetry::getUserId(),
            'service_name' => OpenTelemetry::getServiceName(),
            'client_ip' => OpenTelemetry::getClientIp(),
            'server_ip' => OpenTelemetry::getServiceIp(),
            'timestamp' => $timestamp,
            'trace' => trace_debug(),
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
        ];

        $logName = trim(OpenTelemetry::getLogPath(), "/") . DIRECTORY_SEPARATOR . $level . '.' . date('YmdH') . '.log';
        $message = json_encode($data, JSON_UNESCAPED_UNICODE);
        $strDirName = dirname($logName);
        if (!is_dir($strDirName)) {
            mkdir($strDirName, 0777, true);
        }
        error_log($message . "\n", 3, $logName);
    }

    /**
     * 系统无法使用。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function emergency($message, array $context = array())
    {
        self::log(self::EMERGENCY, $message, $context);
    }

    /**
     * 必须立即采取行动。
     *
     * 例如: 整个网站宕机了，数据库挂了，等等。 这应该
     * 发送短信通知警告你.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function alert($message, array $context = array())
    {
        self::log(self::ALERT, $message, $context);
    }

    /**
     * 临界条件。
     *
     * 例如: 应用组件不可用，意外的异常。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function critical($message, array $context = array())
    {
        self::log(self::CRITICAL, $message, $context);
    }

    /**
     * 运行时错误不需要马上处理，
     * 但通常应该被记录和监控。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function error($message, array $context = array())
    {
        self::log(self::ERROR, $message, $context);
    }

    /**
     * 例外事件不是错误。
     *
     * 例如: 使用过时的API，API使用不当，不合理的东西不一定是错误。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function warning($message, array $context = array())
    {
        self::log(self::WARNING, $message, $context);
    }

    /**
     * 正常但重要的事件.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function notice($message, array $context = array())
    {
        self::log(self::NOTICE, $message, $context);
    }

    /**
     * 有趣的事件.
     *
     * 例如: 用户登录，SQL日志。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function info($message, array $context = array())
    {
        self::log(self::INFO, $message, $context);
    }

    /**
     * 详细的调试信息。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function debug($message, array $context = array())
    {
        self::log(self::DEBUG, $message, $context);
    }
}