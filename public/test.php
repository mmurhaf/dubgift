<?php
// Simple test to verify PHP is working
echo "<h1>PHP Test</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Path: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "<p>File exists check for config: " . (file_exists('../dubgift-config/config.php') ? 'YES' : 'NO') . "</p>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
?>
