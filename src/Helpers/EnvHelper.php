<?php
namespace Xgenious\Installer\Helpers;

class EnvHelper
{
    protected $envFilePath;

    public function __construct($envFilePath='')
    {
        $this->envFilePath = base_path('.env');
    }

    /**
     * Check if a specific key exists in the .env file.
     *
     * @param string $key The key to search for.
     * @return bool True if the key exists, false otherwise.
     */
    public static function keyExists($key)
    {
        $envVars = (new self())->parseEnvFile();

        return array_key_exists($key, $envVars);
    }

    /**
     * Parse the .env file and return an associative array of key-value pairs.
     *
     * @return array
     */
    protected function parseEnvFile()
    {
        if (!file_exists($this->envFilePath)) {
            throw new \Exception("The .env file does not exist at path: {$this->envFilePath}");
        }

        $envVars = [];
        $lines = file($this->envFilePath);

        foreach ($lines as $line) {
            // Trim the line and skip comments and empty lines
            $line = trim($line);
            if (empty($line) || $line[0] === '#') {
                continue;
            }

            // Split the line into key and value
            list($key, $value) = explode('=', $line, 2) + [NULL, NULL];
            if ($key !== NULL && $value !== NULL) {
                // Remove any extra spaces
                $key = trim($key);
                $value = trim($value);

                // Store in the associative array
                $envVars[$key] = $value;
            }
        }

        return $envVars;
    }
}

// Usage example:
try {
    $envHelper = new EnvHelper(__DIR__ . '/.env');
    if ($envHelper->keyExists('DB_CONNECTION')) {
//        echo "DB_CONNECTION key exists in the .env file.";
    } else {
//        echo "DB_CONNECTION key does not exist in the .env file.";
    }
} catch (\Exception $e) {
//    echo "Error: " . $e->getMessage();
}
