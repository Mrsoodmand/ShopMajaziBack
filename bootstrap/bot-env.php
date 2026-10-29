<?php

// The Telegram bot scripts in public/ run outside Laravel, so they read their
// keys from the same .env file through this helper instead of keeping them in code.

if (!function_exists('bot_env')) {
    function bot_env(string $key, $default = null)
    {
        static $vars = null;

        if ($vars === null) {
            $vars = [];
            $file = dirname(__DIR__) . '/.env';
            $lines = is_readable($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                    continue;
                }

                [$name, $value] = explode('=', $line, 2);
                $name = trim(preg_replace('/^export\s+/', '', $name));
                $value = trim($value);

                if ($value !== '' && ($value[0] === "'" || $value[0] === '"')) {
                    $quote = $value[0];
                    $end = strpos($value, $quote, 1);
                    $value = substr($value, 1, $end === false ? null : $end - 1);
                } else {
                    $value = trim(preg_replace('/\s+#.*$/', '', $value));
                }

                $vars[$name] = $value;
            }
        }

        if (array_key_exists($key, $vars)) {
            return $vars[$key];
        }

        $env = getenv($key);

        return $env === false ? $default : $env;
    }
}
