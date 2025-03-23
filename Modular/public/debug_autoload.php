<?php
// Simple debugging script to test autoloading and paths

echo "<h1>Path Debugging</h1>";

echo "<h2>Directory Structure</h2>";
echo "<pre>";
echo "Current script path: " . __FILE__ . "\n";
echo "Directory: " . __DIR__ . "\n";
echo "Root path calculation: " . __DIR__ . "/..\n";
echo "Absolute root path: " . realpath(__DIR__ . "/..");
echo "</pre>";

echo "<h2>Controller Path Check</h2>";
echo "<pre>";
$controllerPath = realpath(__DIR__ . "/../src/Controllers/ClockServerController.php");
echo "Expected controller path: " . $controllerPath . "\n";
echo "File exists: " . (file_exists($controllerPath) ? "Yes" : "No") . "\n";
echo "</pre>";

// Try to include the controller directly
echo "<h2>Direct Include Test</h2>";
echo "<pre>";
$includeResult = @include_once($controllerPath);
echo "Include result: " . ($includeResult ? "Success" : "Failed") . "\n";
echo "</pre>";

// Test the autoloader
echo "<h2>Autoloader Test</h2>";
echo "<pre>";

function testAutoload() {
    // Register autoloader
    spl_autoload_register(function ($class) {
        echo "Trying to autoload: $class\n";
        
        // Convert namespace to file path
        $class = str_replace('\\', '/', $class);
        
        // Map App namespace to src directory
        $class = str_replace('App/', '', $class);
        
        // Get the root path (going back from public to root)
        $rootPath = __DIR__ . '/..';
        
        // The full path to the file
        $file = $rootPath . '/src/' . $class . '.php';
        
        echo "Looking for file: $file\n";
        echo "File exists: " . (file_exists($file) ? "Yes" : "No") . "\n";
        
        // Check if the file exists and include it
        if (file_exists($file)) {
            require_once $file;
            echo "File included successfully\n";
            return true;
        }
        echo "File not found\n";
        return false;
    });
    
    // Try to use the controller
    try {
        $controller = new App\Controllers\ClockServerController();
        echo "Controller instantiated successfully\n";
        return true;
    } catch (Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return false;
    }
}

$result = testAutoload();
echo "Overall test result: " . ($result ? "Success" : "Failed") . "\n";
echo "</pre>";
?> 