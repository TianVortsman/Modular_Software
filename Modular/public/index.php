<?php
require_once __DIR__ . '/../src/Utils/errorHandler.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/root.css">
    <link rel="stylesheet" href="assets/css/sign-in.css">
</head>
<body>
    <div class="sign-in-container">
        <div class="header">
            <h1 class="software-name">Modular Software</h1>
        </div>
        
        <div class="sign-in-logo">
            <img src="assets/img/Logo.webp" alt="Company Logo">
        </div>

        <div class="sign-in-form">
            <h1>Sign In</h1>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php if ($_GET['error'] === 'db_connection'): ?>
                        <p>Database connection error. Please try again later or contact support.</p>
                    <?php else: ?>
                        <p>Invalid email or password. Please try again.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <form action="../src/auth.php" method="POST" id="signInForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" placeholder="example@gmail.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" data-direction="forward" class="btn-sign-in">Sign In</button>
                <a href="../src/auth.php?action=password_reset" data-direction="forward" class="forgot-password">Forgot Password?</a>
            </form>

            <div class="new-customer">
                <p>New here? <a href="contact.php" data-direction="forward" >Contact us</a></p>
            </div>
            
            <!-- For testing only -->
            <div style="margin-top: 20px; text-align: center;">
                <a href="path_test.php" style="font-size: 14px; color: #999; margin-right: 10px;">Path Test</a>
            </div>
        </div>
    </div>

<script src="assets/js/toggle-theme.js" type="module"></script>
</body>
</html>
