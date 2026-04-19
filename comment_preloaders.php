<?php
/**
 * Comment out all Preloader divs across dashboard
 */

$baseDir = __DIR__;
$fixed = 0;

$dashboardDir = $baseDir . '/dashboard';
$files = glob($dashboardDir . '/*.php');

foreach ($files as $filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Comment out preloader div
    $content = preg_replace(
        '/<div class="preloader">\\s*<svg[^>]*>[^<]*<circle[^>]*><\\/circle>[^<]*<\\/svg>\\s*<\\/div>/s',
        '<!-- <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" ></circle>
        </svg>
    </div> -->',
        $content
    );
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $fixed++;
    }
}

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Preloader Comment Completed</title>";
echo "<style>";
echo "body { font-family: Arial; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }";
echo ".success { color: #388e3c; padding: 15px; background: #e8f5e9; border-radius: 4px; }";
echo "h1 { color: #333; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>✅ Preloader Commented</h1>";
echo "<div class='success'>";
echo "Commented out preloaders in <strong>$fixed files</strong><br>";
echo "All dashboard pages will now load without preloader spinner<br><br>";
echo "<a href='dashboard/panel.php' style='padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>Go to Dashboard →</a>";
echo "</div>";
echo "</div>";
echo "</body>";
echo "</html>";
?>
