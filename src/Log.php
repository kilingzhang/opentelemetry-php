<?php


namespace Kilingzhang\OpenTelemetry;


class Log
{

    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    /**
     * 可任意级别记录日志。 禁止外部直接调用
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    private static function log($level, $message, array $context = array())
    {
        !is_array($context) && $context = [];
        $context['service_operation'] = OpenTelemetry::getServiceOperation();
        $data = [
            'level' => $level,
            'message' => $message,
            'trace_id' => Tracer::getTraceId(),
            'uid' => Tracer::getUserId(),
            'service_name' => OpenTelemetry::getServiceName(),
            'client_ip' => OpenTelemetry::getClientIp(),
            'server_ip' => OpenTelemetry::getServiceIp(),
            'timestamp' => OpenTelemetry::getUnixNano(),
            'trace' => trace_debug(),
            'context' => $context,
        ];
        $logName = rtrim(OpenTelemetry::getLogPath(), "/") . DIRECTORY_SEPARATOR . $level . '.' . date('YmdH') . '.log';
        $message = json_encode($data, JSON_UNESCAPED_UNICODE);
        $strDirName = dirname($logName);
        if (!is_dir($strDirName)) {
            mkdir($strDirName, 0777, true);
        }
        error_log($message . "\n", 3, $logName);
    }

    /**
     * 必须立即采取行动。
     * 系统无法使用。例如：fatal,panic,服务不可用,资源不可达
     * 立即触发告警 告警通道（短信,IVR,邮件等）默认且必须立刻告警，无需业务自行订阅。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function emergency($message, array $context = array())
    {
        self::log(self::EMERGENCY, $message, $context);
        Tracer::addAttribute("logging.emergency.message", $message);
    }

    /**
     * 业务层面的需要进行告警通知的日志。例如: 红包余额不足,账户余额不足等
     * 需要业务自己根据告警信息进行订阅告警。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function alert($message, array $context = array())
    {
        self::log(self::ALERT, $message, $context);
        Tracer::addAttribute("logging.alert.message", $message);
    }


    /**
     * 运行时错误不需要马上处理，
     * 但通常应该被记录和监控。
     * 支付失败、权益下发失败
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function error($message, array $context = array())
    {
        self::log(self::ERROR, $message, $context);
        Tracer::addAttribute("logging.error.message", $message);
    }

    /**
     * 例外事件不是错误。
     *
     * 例如: 使用过时的API，API使用不当，不合理的东西不一定是错误。
     * 接口请求业务码不符合预期、方法参数不正确
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
     * 例如: 权益,支付
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
     * 可用于记录事件节点。标记事件发生。
     * 例如: 用户登录,SQL日志。
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
     * 例如: 接口的请求及响应,方法的输入输出
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function debug($message, array $context = array())
    {
        self::log(self::DEBUG, $message, $context);
    }
}