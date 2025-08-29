<?php

namespace Core;

class Logger
{
    private static $logPath;
    private static $logLevel = 'info';

    // Log levels
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const CRITICAL = 'critical';

    private static $levels = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::WARNING => 2,
        self::ERROR => 3,
        self::CRITICAL => 4
    ];

    public static function init($logPath = null, $logLevel = 'info')
    {
        self::$logPath = $logPath ?: BASE_PATH . 'storage/logs/app.log';
        self::$logLevel = $logLevel;
        
        // Create log directory if it doesn't exist
        $logDir = dirname(self::$logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public static function debug($message, array $context = [])
    {
        self::log(self::DEBUG, $message, $context);
    }

    public static function info($message, array $context = [])
    {
        self::log(self::INFO, $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        self::log(self::WARNING, $message, $context);
    }

    public static function error($message, array $context = [])
    {
        self::log(self::ERROR, $message, $context);
    }

    public static function critical($message, array $context = [])
    {
        self::log(self::CRITICAL, $message, $context);
    }

    public static function log($level, $message, array $context = [])
    {
        // Check if we should log this level
        if (!self::shouldLog($level)) {
            return;
        }

        // Initialize if not done
        if (!self::$logPath) {
            self::init();
        }

        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        
        // Format message with context
        $formattedMessage = self::formatMessage($message, $context);
        
        // Add request info for web requests
        $requestInfo = self::getRequestInfo();
        
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            $timestamp,
            $levelUpper,
            $formattedMessage,
            $requestInfo
        );

        // Write to log file
        error_log($logEntry, 3, self::$logPath);

        // Also log critical errors to system log
        if ($level === self::CRITICAL || $level === self::ERROR) {
            error_log($formattedMessage);
        }
    }

    private static function shouldLog($level)
    {
        $currentLevel = self::$levels[self::$logLevel] ?? 1;
        $messageLevel = self::$levels[$level] ?? 1;
        
        return $messageLevel >= $currentLevel;
    }

    private static function formatMessage($message, array $context = [])
    {
        // Replace placeholders in message with context values
        $formatted = $message;
        
        foreach ($context as $key => $value) {
            $placeholder = '{' . $key . '}';
            $replacement = self::contextValueToString($value);
            $formatted = str_replace($placeholder, $replacement, $formatted);
        }

        // Add remaining context as JSON if any
        if (!empty($context)) {
            $formatted .= ' Context: ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }

        return $formatted;
    }

    private static function contextValueToString($value)
    {
        if (is_null($value)) {
            return 'null';
        }
        
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        
        if (is_scalar($value)) {
            return (string) $value;
        }
        
        return json_encode($value, JSON_UNESCAPED_SLASHES);
    }

    private static function getRequestInfo()
    {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            return '[CLI]';
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $ip = self::getClientIp();
        
        return sprintf('[%s %s from %s]', $method, $uri, $ip);
    }

    private static function getClientIp()
    {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs (X-Forwarded-For can have multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }

        return 'unknown';
    }

    public static function logException(\Exception $exception, $level = self::ERROR)
    {
        $message = sprintf(
            'Exception: %s in %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        $context = [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        self::log($level, $message, $context);
    }

    public static function logDatabaseQuery($query, $params = [], $executionTime = null)
    {
        $message = 'Database Query: ' . $query;
        
        $context = [];
        if (!empty($params)) {
            $context['params'] = $params;
        }
        if ($executionTime !== null) {
            $context['execution_time'] = $executionTime . 'ms';
        }

        self::debug($message, $context);
    }

    public static function logApiRequest($endpoint, $method, $statusCode, $responseTime = null)
    {
        $message = sprintf('API Request: %s %s - Status: %d', $method, $endpoint, $statusCode);
        
        $context = ['status_code' => $statusCode];
        if ($responseTime !== null) {
            $context['response_time'] = $responseTime . 'ms';
        }

        self::info($message, $context);
    }

    public static function logUserAction($userId, $action, array $details = [])
    {
        $message = sprintf('User Action: User %d performed %s', $userId, $action);
        
        self::info($message, $details);
    }

    public static function logSecurityEvent($event, array $details = [])
    {
        $message = 'Security Event: ' . $event;
        
        self::warning($message, $details);
    }

    /**
     * Get recent log entries
     */
    public static function getRecentLogs($lines = 100)
    {
        if (!file_exists(self::$logPath)) {
            return [];
        }

        $content = file_get_contents(self::$logPath);
        $logLines = explode("\n", $content);
        
        // Get last N lines (excluding empty ones)
        $logLines = array_filter($logLines);
        return array_slice($logLines, -$lines);
    }

    /**
     * Clear log file
     */
    public static function clearLogs()
    {
        if (file_exists(self::$logPath)) {
            file_put_contents(self::$logPath, '');
        }
    }

    /**
     * Get log file size in bytes
     */
    public static function getLogSize()
    {
        if (!file_exists(self::$logPath)) {
            return 0;
        }
        
        return filesize(self::$logPath);
    }
}
