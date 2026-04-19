<?php
// Suppress deprecation warnings from vendor libraries
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $env_file = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_file as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$key] = $value;
        }
    }
}

// Get credentials from environment variables
$parse_app_id = $_ENV['PARSE_APP_ID'] ?? '';
$parse_rest_key = $_ENV['PARSE_REST_API_KEY'] ?? '';
$parse_master_key = $_ENV['PARSE_MASTER_KEY'] ?? '';
$parse_server_url = $_ENV['PARSE_SERVER_URL'] ?? 'https://parseapi.back4app.com/';

$app_name = $_ENV['APP_NAME'] ?? 'Trace';
$default_icon_color = $_ENV['DEFAULT_ICON_COLOR'] ?? 'text-white'; // use Bootstrap text color sintax

use Parse\ParseClient;
use Parse\ParseSessionStorage;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if ($parse_app_id && $parse_rest_key && $parse_master_key) {
        ParseClient::initialize(
            $parse_app_id,
            $parse_rest_key,
            $parse_master_key
        );
        ParseClient::setServerURL($parse_server_url, '/');
        ParseClient::setStorage(new ParseSessionStorage());
    } else {
        error_log('Missing Parse configuration. Please set credentials in .env file');
    }
} catch (Exception $e) {
    error_log('Parse initialization error: ' . $e->getMessage());
}

$health = ParseClient::getServerHealth();
if($health['status'] !== 200) {

}

// Website root url
$GLOBALS['WEBSITE_PATH'] = $_ENV['WEBSITE_PATH'] ?? 'https://parseapi.back4app.com'; 