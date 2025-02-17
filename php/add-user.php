<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require('main-db.php'); // Ensure PostgreSQL connection is set up
    require '../vendor/autoload.php'; // Include PHPMailer

    // Get form data & sanitize
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $role = htmlspecialchars($_POST['role']);
    $account_number = htmlspecialchars($_POST['account_number']);

    // Check if connection is valid
    if (!$conn) {
        die("Database connection failed.");
    }

    // Step 1: Check if user already exists based on email
    $checkUserQuery = "SELECT id FROM users WHERE email = $1";
    $checkUserStmt = pg_prepare($conn, "check_user", $checkUserQuery);
    $userResult = pg_execute($conn, "check_user", [$email]);

    if ($userResult && pg_num_rows($userResult) > 0) {
        // User already exists, fetch their ID
        $existingUser = pg_fetch_assoc($userResult);
        $user_id = $existingUser['id'];
    } else {
        // Step 2: Insert new user if they don’t exist
        $insertUserQuery = "INSERT INTO users (name, email, role) VALUES ($1, $2, $3) RETURNING id";
        $insertUserStmt = pg_prepare($conn, "insert_user", $insertUserQuery);
        $newUserResult = pg_execute($conn, "insert_user", [$name, $email, $role]);

        if ($newUserResult) {
            $newUser = pg_fetch_assoc($newUserResult);
            $user_id = $newUser['id'];

            // Step 3: Send email only for new users
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tianryno01@gmail.com';
            $mail->Password = 'axms oobi witf ytqa'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Set the email content
            $mail->setFrom('tianryno01@gmail.com', 'Modular System');
            $mail->addAddress($email, $name);
            $mail->Subject = 'Password Reset Request';
            $resetLink = "http://yourdomain.com/pages/passreset.html?account_number=" . urlencode($account_number);
            $mail->Body = "Dear $name,<br><br>Your account number is: $account_number<br><br>";
            $mail->Body .= "To reset your password, please click the link below:<br>";
            $mail->Body .= "<a href='$resetLink'>$resetLink</a><br><br>";
            $mail->Body .= "Best regards,<br>The Modular Team";
            $mail->isHTML(true);

            if (!$mail->send()) {
                echo "User added but failed to send email. Mailer Error: " . $mail->ErrorInfo;
            }
        } else {
            echo "Error inserting user.";
            exit;
        }
    }

    // Step 4: Insert into account_number table (Only if account_number is not already assigned)
    $checkAccountQuery = "SELECT id FROM account_number WHERE account_number = $1 AND user_id = $2";
    $checkAccountStmt = pg_prepare($conn, "check_account", $checkAccountQuery);
    $checkAccountResult = pg_execute($conn, "check_account", [$account_number, $user_id]);

    if ($checkAccountResult && pg_num_rows($checkAccountResult) === 0) {
        // Account number does not exist for this user, so insert it
        $insertAccountQuery = "INSERT INTO account_number (account_number, user_id) VALUES ($1, $2)";
        $insertAccountStmt = pg_prepare($conn, "insert_account", $insertAccountQuery);
        $result = pg_execute($conn, "insert_account", [$account_number, $user_id]);

        if ($result) {
            header("Location: ../main/techlogin.php");
            echo "User and account number added successfully!";
        } else {
            echo "Error inserting account number.";
        }
    } else {
        echo "User already has this account number.";
    }

    // Close connection
    pg_close($conn);
} else {
    echo "Invalid request method.";
}
?>
