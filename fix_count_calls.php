<?php
/**
 * Fix all count(true) calls in feature files
 * Replace with count() and add try-catch error handling
 */

$baseDir = __DIR__;
$fixed = 0;

$featureDir = $baseDir . '/features';
$files = glob($featureDir . '/side_*.php');

foreach ($files as $filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Replace count(true) with count() wrapped in try-catch
    // Pattern 1: Simple count(true) replacements
    $content = preg_replace(
        '/\$(\w+)\s*=\s*\$query->count\(true\);/',
        '$\1 = 0; try { $\1 = $query->count(); } catch (Exception $e) { /* Query failed - using default */ }',
        $content
    );
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $fixed++;
        echo "Fixed: " . basename($filePath) . "<br>";
    }
}

echo "<strong>Total files fixed: $fixed</strong><br>";
?>
