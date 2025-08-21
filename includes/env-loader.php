<?php
/**
 * Secure Environment Configuration Loader
 * This file loads configuration from .env file and environment variables
 */

class EnvLoader {
    private static $loaded = false;
    
    public static function load($envPath = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($envPath === null) {
            $envPath = dirname(__DIR__) . '/.env';
        }
        
        if (!file_exists($envPath)) {
            throw new Exception('Environment file not found: ' . $envPath);
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }
            
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match('/^\'(.*)\'$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }
        
        self::$loaded = true;
    }
    
    public static function get($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
    
    public static function getRequired($key) {
        $value = self::get($key);
        if ($value === null) {
            throw new Exception("Required environment variable '$key' is not set");
        }
        return $value;
    }
    
    public static function getBool($key, $default = false) {
        $value = self::get($key, $default);
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
    }
    
    public static function getInt($key, $default = 0) {
        return (int) self::get($key, $default);
    }
}
?>
