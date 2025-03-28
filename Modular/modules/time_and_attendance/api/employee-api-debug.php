<?php
// This is a temporary debugging file to help locate your autoload.php
header('Content-Type: application/json');

// Get useful path information
$data = [
    'current_file' => __FILE__,
    'directory' => __DIR__,
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Not available',
    'working_directory' => getcwd(),
    'include_path' => get_include_path(),
    'possible_autoload_locations' => [
        'relative_path' => __DIR__ . '/../../../autoload.php',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] . '/autoload.php',
        'document_root_modular' => $_SERVER['DOCUMENT_ROOT'] . '/Modular/autoload.php'
    ]
];

// Check if these files exist
foreach ($data['possible_autoload_locations'] as $key => $path) {
    $data['exists'][$key] = file_exists($path);
}

// Return the information as JSON
echo json_encode($data, JSON_PRETTY_PRINT);
exit; 