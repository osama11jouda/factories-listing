<?php
/**
 * EnvLoader - A simple class for loading and accessing environment variables from .env file
 */
class EnvLoader {
    private static $variables = [];
    private static $initialized = false;
    
    /**
     * Initialize the environment variables from .env file
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        
        $envPath = dirname(__DIR__) . '/.env';
        
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Parse only valid lines with format: KEY=VALUE
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (preg_match('/^"(.+)"$/', $value, $matches)) {
                        $value = $matches[1];
                    } elseif (preg_match("/^'(.+)'$/", $value, $matches)) {
                        $value = $matches[1];
                    }
                    
                    // Convert specific strings to their boolean/null values
                    if ($value === 'true') {
                        $value = true;
                    } elseif ($value === 'false') {
                        $value = false;
                    } elseif ($value === 'null') {
                        $value = null;
                    }
                    
                    // Store in the variables array
                    self::$variables[$key] = $value;
                }
            }
            
            self::$initialized = true;
        } else {
            error_log('EnvLoader: .env file not found at ' . $envPath);
        }
    }
    
    /**
     * Get an environment variable value
     *
     * @param string $key The environment variable key
     * @param mixed $default Default value if the key doesn't exist
     * @return mixed The value of the environment variable
     */
    public static function get($key, $default = null) {
        if (!self::$initialized) {
            self::init();
        }
        
        return isset(self::$variables[$key]) ? self::$variables[$key] : $default;
    }
    
    /**
     * Get all environment variables
     *
     * @return array All environment variables
     */
    public static function getAll() {
        if (!self::$initialized) {
            self::init();
        }
        
        return self::$variables;
    }
    
    /**
     * Check if an environment variable exists
     *
     * @param string $key The environment variable key
     * @return bool True if the key exists, false otherwise
     */
    public static function has($key) {
        if (!self::$initialized) {
            self::init();
        }
        
        return isset(self::$variables[$key]);
    }
}
?>