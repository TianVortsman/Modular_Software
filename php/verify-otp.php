<?php include('process-otp.php')?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/passreset.css">
    <script src="../js/toggle-theme.js" type="module"></script>
    <title>Verify OTP</title>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Verify OTP</h1>
            <form action="process-otp.php" method="POST">
                <div class="input-group">
                    <label for="otp">Enter OTP:</label>
                    <input type="text" id="otp" name="otp" required>
                </div>
                <div class="input-group">
                    <label for="new-password">New Password:</label>
                    <input type="password" id="new-password" name="new-password" required>
                </div>
                <div class="input-group">
                    <label for="confirm-password">Confirm Password:</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>
                </div>
                <button type="submit" class="btn">Verify OTP</button>
            </form>
        </div>
    </div>
</body>
</html>
