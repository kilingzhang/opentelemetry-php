<?php

/**
 * @param array $data
 * @param bool $isExit
 */
function dd($data = [], $isExit = true)
{
    if (isset($_SERVER['SHELL']) || (PHP_SAPI === 'cli')) {
        echo "\n";
    } else {
        echo "<pre>";
    }
    var_dump($data);
    if (isset($_SERVER['SHELL']) || (PHP_SAPI === 'cli')) {
        echo "\n";
    } else {
        echo "</pre>";
    }
    $isExit && exit();
}

/**
 * @return string
 */
function trace_debug()
{
    $function_caller = '';
    $backtraces = debug_backtrace();
    $backtraces = array_reverse($backtraces);
    foreach ($backtraces as $backtrace) {
        $class = empty($backtrace['class']) ? '' : $backtrace['class'];
        $type = empty($backtrace['type']) ? '' : $backtrace['type'];
        $function = empty($backtrace['function']) ? '' : $backtrace['function'];
        if (strstr($class, "Kilingzhang\OpenTelemetry") !== false || $function === 'trace_debug') {
            continue;
        }
        $function_caller .= "{$class}{$type}{$function}\\";
    }
    return trim($function_caller, "\\");
}

/**
 * @param $ip
 * @return string
 */
function ip_to_long($ip)
{
    $ip_chunks = explode('.', $ip, 4);
    foreach ($ip_chunks as $i => $v) {
        $ip_chunks[$i] = abs(intval($v));
    }
    return sprintf('%u', ip2long(implode('.', $ip_chunks)));
}

/**
 * 判断是否是内网ip
 * @param string $ip
 * @return boolean
 */
function is_private_ip($ip)
{
    $ip_value = ip_to_long($ip);
    return ($ip_value & 0xFF000000) === 0x0A000000 //10.0.0.0-10.255.255.255
        || ($ip_value & 0xFFF00000) === 0xAC100000 //172.16.0.0-172.31.255.255
        || ($ip_value & 0xFFFF0000) === 0xC0A80000 //192.168.0.0-192.168.255.255
        ;
}

/**
 * @return mixed|string
 */
function get_client_ip()
{
    $forwarded = empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : $_SERVER['HTTP_X_FORWARDED_FOR'];
    if ($forwarded) {
        $ip_chains = explode(',', $forwarded);
        $proxies_client_ip = $ip_chains ? trim(array_pop($ip_chains)) : '';
    }
    if (is_private_ip(empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR']) && isset($proxies_client_ip)) {
        $real_ip = $proxies_client_ip;
    } else {
        $real_ip = empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
    }
    return $real_ip;
}

function is_cli()
{
    return preg_match("/cli/i", php_sapi_name());
}

/**
 * 获取服务端ip
 */
function get_server_ip()
{
    $server_ip = '';
    if (!is_cli()) {
        $server_ip = $_SERVER['SERVER_ADDR'];
    } else {
        $strCmd = 'ifconfig  eth0';
        $resFp = popen($strCmd, 'r');
        $value = fread($resFp, 4096);
        pclose($resFp);
        $ips = '';
        $pattern = '/inet addr\:(\d+\.\d+\.\d+\.\d+)/';
        if (preg_match_all($pattern, $value, $matches, PREG_SET_ORDER)) {
            if ($matches[0][1] != '127.0.0.1') {
                $ips .= $matches[0][1] . ',';
                $server_ip = $matches[0][1];
            }
        }
    }
    return $server_ip;
}