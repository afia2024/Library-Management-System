<?php

require __DIR__ . '/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    try {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Please enter a valid email address.');
        }

        if (strlen($password) < 6) {
            throw new DomainException(
                'Password must be at least 6 characters.'
            );
        }

        if ($password !== $confirmPassword) {
            throw new DomainException(
                'Passwords do not match.'
            );
        }

        // Find member
        $stmt = $pdo->prepare(
            'SELECT member_id FROM MEMBERS WHERE email = ?'
        );

        $stmt->execute([$email]);

        $member = $stmt->fetch();

        if (!$member) {
            throw new DomainException(
                'No member account was found with that email.'
            );
        }

        // Create secure password hash
        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        // Update member password
        $stmt = $pdo->prepare(
            'UPDATE MEMBERS
             SET password = ?
             WHERE member_id = ?'
        );

        $stmt->execute([
            $hashedPassword,
            $member['member_id']
        ]);

        $success =
            'Password updated successfully! You can now log in.';

    } catch (DomainException $e) {

        $error = $e->getMessage();

    } catch (PDOException $e) {

        $error = 'Unable to update password. Please try again.';

    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Forgot Password - Library</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}

.page {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 30px;
}

.box {
    background: white;
    width: 100%;
    max-width: 420px;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.12);
}

.logo {
    text-align: center;
    font-size: 30px;
    font-weight: bold;
}

.subtitle {
    text-align: center;
    color: #666;
    margin: 8px 0 25px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 18px;
}

input {
    width: 100%;
    padding: 12px;
    margin-top: 7px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

button {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 6px;
    background: #3578b8;
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

.error {
    background: #ffe5e5;
    color: #a40000;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.success {
    background: #e5f7e9;
    color: #176b2c;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.back {
    text-align: center;
    margin-top: 20px;
}

.back a {
    color: #3578b8;
    text-decoration: none;
    font-weight: bold;
}

</style>

</head>

<body>

<div class="page">

<div class="box">

    <div class="logo">
        📚 Library
    </div>

    <div class="subtitle">
        Reset Member Password
    </div>

    <?php if ($error): ?>

        <div class="error">
            <?= h($error) ?>
        </div>

    <?php endif; ?>


    <?php if ($success): ?>

        <div class="success">
            <?= h($success) ?>
        </div>

    <?php endif; ?>


    <form method="post">

        <input
            type="hidden"
            name="csrf"
            value="<?= h(csrf_token()) ?>"
        >

        <label>
            Email

            <input
                type="email"
                name="email"
                placeholder="Enter your account email"
                required
            >
        </label>


        <label>
            New Password

            <input
                type="password"
                name="password"
                placeholder="Enter new password"
                minlength="6"
                required
            >
        </label>


        <label>
            Confirm New Password

            <input
                type="password"
                name="confirm_password"
                placeholder="Enter new password again"
                minlength="6"
                required
            >
        </label>


        <button type="submit">
            Reset Password
        </button>

    </form>


    <div class="back">

        <a href="member_login.php">
            ← Back to Member Login
        </a>

    </div>

</div>

</div>

</body>

</html>