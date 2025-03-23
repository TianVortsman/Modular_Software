<?php
// Web-accessible authentication test script
require_once __DIR__ . '/Core/Auth/Authentication.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default to HTML output
$output_format = $_GET['format'] ?? 'html';
if ($output_format === 'text') {
    header('Content-Type: text/plain');
} else {
    header('Content-Type: text/html');
}

// Process login form
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $auth = new App\Core\Auth\Authentication();
    $result = $auth->login($_POST['email'], $_POST['password']);
}

// HTML output
if ($output_format !== 'text') {
    ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .success {
            border-color: #4CAF50;
            background-color: #e8f5e9;
        }
        .error {
            border-color: #f44336;
            background-color: #ffebee;
        }
        .debug {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            overflow: auto;
        }
    </style>
</head>
<body>
    <h1>Authentication Test</h1>
    
    <form method="post" action="">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <input type="submit" value="Test Login">
    </form>
    
    <?php if ($result): ?>
    <div class="result <?= $result['success'] ? 'success' : 'error' ?>">
        <h2>Login Result:</h2>
        <p><strong>Success:</strong> <?= $result['success'] ? 'Yes' : 'No' ?></p>
        <p><strong>Message:</strong> <?= htmlspecialchars($result['message']) ?></p>
        <?php if ($result['success'] && $result['redirect']): ?>
        <p><strong>Redirect:</strong> <?= htmlspecialchars($result['redirect']) ?></p>
        <?php endif; ?>
    </div>
    
    <div class="debug">
        <h2>Session Data:</h2>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    <?php endif; ?>
    
    <div class="debug">
        <h2>Test Accounts:</h2>
        <p>Try these test accounts:</p>
        <ul>
            <li><strong>Technician:</strong> tianryno01@gmail.com / Modul@rdev@2024</li>
            <li><strong>User:</strong> tian@uniclox.com / H3llOnline2001</li>
        </ul>
    </div>
</body>
</html>
<?php
} else {
    // Plain text output
    echo "Authentication Test\n";
    echo "==================\n\n";
    
    if ($result) {
        echo "Login Result:\n";
        echo "- Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
        echo "- Message: " . $result['message'] . "\n";
        if ($result['success'] && $result['redirect']) {
            echo "- Redirect: " . $result['redirect'] . "\n";
        }
        
        echo "\nSession Data:\n";
        print_r($_SESSION);
    } else {
        echo "No login attempt has been made.\n";
        echo "Use the form at " . $_SERVER['REQUEST_URI'] . " to test login.\n";
    }
}
?> 