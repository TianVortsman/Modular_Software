<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/root.css">
    <link rel="stylesheet" href="css/sign-in.css">
</head>
<body>
    <div class="sign-in-container">
        <div class="header">
            <h1 class="software-name">Modular Software</h1>
        </div>
        
        <div class="sign-in-logo">
            <img src="img/Logo.webp" alt="Company Logo">
        </div>

        <div class="sign-in-form">
            <h1>Sign In</h1>
            <form action="php/authentication.php" method="POST" id="signInForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" placeholder="example@gmail.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" data-direction="forward" class="btn-sign-in">Sign In</button>
                <a href="php/passreset.php" data-direction="forward" class="forgot-password">Forgot Password?</a>
            </form>

            <div class="new-customer">
                <p>New here? <a href="contact.php" data-direction="forward" >Contact us</a></p>
            </div>
        </div>
    </div>

<script src="js/toggle-theme.js" type="module"></script>
<script src="/modular1/js/page-transitions.js"></script>
</body>
</html>
