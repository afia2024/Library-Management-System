<?php

require __DIR__ . '/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $name = trim((string)($_POST['full_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    try {

        // Check name
        if ($name === '' || mb_strlen($name) > 120) {
            throw new DomainException('Please enter your full name.');
        }

        // Check email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Please enter a valid email address.');
        }

        // Check phone
        if (strlen($phone) > 30) {
            throw new DomainException('Phone number is too long.');
        }

        // Check password
        if (strlen($password) < 6) {
            throw new DomainException(
                'Password must be at least 6 characters.'
            );
        }

        // Confirm passwords match
        if ($password !== $confirmPassword) {
            throw new DomainException(
                'Passwords do not match.'
            );
        }

        // Check if email already exists
        $check = $pdo->prepare(
            'SELECT member_id FROM MEMBERS WHERE email = ?'
        );

        $check->execute([$email]);

        if ($check->fetch()) {
            throw new DomainException(
                'An account with this email already exists.'
            );
        }

        // Securely hash password
        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        // Add member account
        $stmt = $pdo->prepare(
            'INSERT INTO MEMBERS
            (full_name, email, phone, password)
            VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $name,
            $email,
            $phone ?: null,
            $hashedPassword
        ]);

        $success = 'Account created successfully! You can now log in.';

    } catch (DomainException $e) {

        $error = $e->getMessage();

    } catch (PDOException $e) {

        $error = 'Unable to create account. Please try again.';

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

    <title>Create Account - Library</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }

        .register-page {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .register-box {
            background: white;
            width: 100%;
            max-width: 430px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.12);
        }

        .logo {
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 15px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
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

        button:hover {
            background: #28659f;
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

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #3578b8;
            text-decoration: none;
            font-weight: bold;
        }

        .note {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #777;
        }

    </style>

</head>


<body>

<div class="register-page">

    <div class="register-box">

        <div class="logo">
            📚 Library
        </div>

        <div class="subtitle">
            Create Member Account
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

                Full Name

                <input
                    type="text"
                    name="full_name"
                    placeholder="Enter your full name"
                    maxlength="120"
                    required
                >

            </label>


            <label>

                Email

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    maxlength="190"
                    required
                >

            </label>


            <label>

                Phone

                <input
                    type="text"
                    name="phone"
                    placeholder="Enter your phone number"
                    maxlength="30"
                >

            </label>


            <label>

                Password

                <input
                    type="password"
                    name="password"
                    placeholder="Create a password"
                    minlength="6"
                    required
                >

            </label>


            <label>

                Confirm Password

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Enter password again"
                    minlength="6"
                    required
                >

            </label>


            <button type="submit">
                Create Account
            </button>

        </form>


        <div class="login-link">

            Already have an account?

            <a href="member_login.php">
                Member Login
            </a>

        </div>


        <div class="note">
            CIS 344 Library Management System
        </div>

    </div>

</div>

</body>

</html>