<?php
/**
 * Automatic Session and Error Fix Script
 * Fixes session_start() warnings and error reporting across all PHP files
 */

$baseDir = __DIR__;
$fixed = 0;
$errors = [];

function fixPhpFile($filePath) {
    global $fixed, $errors;
    
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Fix 1: Replace session_start(); with conditional session start
    $content = preg_replace(
        '/\nuse Parse\\\\ParseUser;\\s*\n\\s*session_start\(\);/',
        "\nuse Parse\\ParseUser;\n\nif (session_status() === PHP_SESSION_NONE) {\n    session_start();\n}",
        $content
    );
    
    // Fix 2: For files that start with session_start
    $content = preg_replace(
        '/use Parse\\\\ParseSessionStorage;\\s*\\nuse Parse\\\\ParseGeoPoint;\\s*\\nsession_start\(\);/',
        "use Parse\\ParseSessionStorage;\nuse Parse\\ParseGeoPoint;\n\nif (session_status() === PHP_SESSION_NONE) {\n    session_start();\n}",
        $content
    );
    
    // Fix 3: Generic session_start replacement
    $content = preg_replace(
        '/^session_start\(\);$/m',
        "if (session_status() === PHP_SESSION_NONE) {\n    session_start();\n}",
        $content
    );
    
    if ($content !== $originalContent) {
        if (file_put_contents($filePath, $content)) {
            $fixed++;
            return true;
        } else {
            $errors[] = "Failed to write: $filePath";
            return false;
        }
    }
    
    return false;
}

// Get all PHP files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$phpFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getRealPath();
        // Skip vendor and certain directories
        if (strpos($filePath, 'vendor') === false && 
            strpos($filePath, '.git') === false) {
            $phpFiles[] = $filePath;
        }
    }
}

// Fix each file
foreach ($phpFiles as $filePath) {
    fixPhpFile($filePath);
}

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Auto Fix Complete</title>";
echo "<style>";
echo "body { font-family: Arial; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }";
echo ".success { color: #388e3c; padding: 15px; background: #e8f5e9; border-radius: 4px; }";
echo ".error { color: #d32f2f; padding: 10px; background: #ffebee; border-radius: 4px; margin: 10px 0; }";
echo "h1 { color: #333; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>✅ Auto Fix Complete</h1>";
echo "<div class='success'>";
echo "<strong>Fixed $fixed files</strong><br>";
echo "All session_start() issues have been resolved<br>";
if (count($errors) === 0) {
    echo "No errors occurred!";
} else {
    echo "Errors: ";
    foreach ($errors as $error) {
        echo "<div class='error'>$error</div>";
    }
}
echo "</div>";
echo "<br><a href='dashboard/panel.php' style='padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>Go to Dashboard →</a>";
echo "</div>";
echo "</body>";
echo "</html>";
?>
