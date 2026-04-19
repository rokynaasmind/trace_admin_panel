<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 1);

require 'vendor/autoload.php';
include 'Configs.php';

use Parse\ParseUser;
use Parse\ParseQuery;

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Dashboard Pages Error Scanner</title>";
echo "<style>";
echo "body { font-family: Arial; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }";
echo "h1 { color: #333; }";
echo ".page { background: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #2196F3; }";
echo ".success { color: #388e3c; }";
echo ".error { color: #d32f2f; }";
echo "hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }";
echo ".code { background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; overflow-x: auto; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";

echo "<h1>🔍 Dashboard Pages Error Scanner</h1>";

// List of dashboard pages to check
$pages = [
    'comments.php',
    'posts.php',
    'videos.php',
    'messages.php',
    'calls.php',
    'stories.php',
    'streaming.php',
    'clicks.php',
    'visits.php',
    'encounters.php',
    'follow.php',
    'favorites.php',
];

$currUser = ParseUser::getCurrentUser();

if (!$currUser) {
    echo "<div class='error'>❌ Not logged in! Cannot scan.</div>";
} else {
    echo "<p>Checking " . count($pages) . " dashboard pages...</p>";
    echo "<hr>";
    
    foreach ($pages as $page) {
        $filePath = __DIR__ . "/dashboard/$page";
        
        echo "<div class='page'>";
        echo "<strong>$page:</strong> ";
        
        if (!file_exists($filePath)) {
            echo "<span class='error'>❌ File not found</span>";
        } else {
            // Check if file includes features file
            $content = file_get_contents($filePath);
            
            if (preg_match('/side_(.+?)\.php/', $content, $matches)) {
                $sideFile = $matches[0];
                $sidePath = __DIR__ . "/features/$sideFile";
                
                if (file_exists($sidePath)) {
                    echo "<span class='success'>✅ OK</span>";
                    echo " - Uses: <code>$sideFile</code>";
                } else {
                    echo "<span class='error'>❌ Missing feature file: $sideFile</span>";
                }
            } else {
                echo "<span class='success'>✅ Exists</span>";
            }
        }
        echo "</div>";
    }
}

echo "<hr>";
echo "<h2>Direct Feature File Test</h2>";

$featureFiles = [
    'side_comments.php',
    'side_posts.php',
    'side_videos.php',
    'side_messages.php',
    'side_calls.php',
    'side_stories.php',
    'side_streaming.php',
];

foreach ($featureFiles as $feature) {
    $filePath = __DIR__ . "/features/$feature";
    
    echo "<div class='page'>";
    echo "<strong>$feature:</strong> ";
    
    if (file_exists($filePath)) {
        echo "<span class='success'>✅ Exists</span>";
    } else {
        echo "<span class='error'>❌ Missing</span>";
    }
    echo "</div>";
}

echo "</div>";
echo "</body>";
echo "</html>";
?>
