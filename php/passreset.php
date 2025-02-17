<?php require ('verifypass.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/passreset.css">
    <title>Password Reset</title>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <img src="../img/Logo.webp" alt="Logo" class="logo">
            <h1>Password Reset</h1>
            <form action="verifypass.php" method="POST">
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com" required>
                </div>
                <button type="submit" class="btn">Send OTP</button>
            </form>
            <div class="message">
                <p>Remembered your password? <a href="../index.php" data-direction="backward">Sign In</a></p>
            </div>
        </div>
    </div>
</body>
<script src="../js/toggle-theme.js" type="module"></script>
<script src="/modular1/js/page-transitions.js"></script>
</html>
