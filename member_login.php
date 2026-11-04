<?php

require __DIR__ . '/config.php';

$error = '';

// If member is already logged in
if (isset($_SESSION['member_id'])) {
    header('Location: member.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {

        $error = 'Please enter your email and password.';

    } else {

        $stmt = $pdo->prepare(
            'SELECT member_id, full_name, email, password
             FROM MEMBERS
             WHERE email = ?'
        );

        $stmt->execute([$email]);

        $member = $stmt->fetch();

        if (
            $member &&
            !empty($member['password']) &&
            password_verify($password, $member['password'])
        ) {

            $_SESSION['member_id'] = $member['member_id'];
            $_SESSION['member_name'] = $member['full_name'];

            header('Location: member.php');
            exit;

        } else {

            $error = 'Incorrect email or password.';

        }
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

<title>Member Login - Library</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}

.login-page {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-box {
    background: white;
    width: 100%;
    max-width: 400px;
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
    margin-top: 8px;
    margin-bottom: 25px;
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

.links {
    text-align: center;
    margin-top: 20px;
}

.links a {
    color: #3578b8;
    text-decoration: none;
    font-weight: bold;
}

.admin {
    text-align: center;
    margin-top: 15px;
    font-size: 13px;
}

.note {
    text-align: center;
    color: #777;
    margin-top: 20px;
    font-size: 13px;
}

</style>

</head>

<body>

<div class="login-page">

<div class="login-box">

    <div class="logo">
        📚 Library
    </div>

    <div class="subtitle">
        Member Login
    </div>

    <?php if ($error): ?>

        <div class="error">
            <?= h($error) ?>
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
                placeholder="Enter your email"
                required
            >

        </label>


        <label>

            Password

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >

        </label>


        <button type="submit">
            Login
        </button>

    </form>


    <div class="links">

        Don't have an account?

        <a href="register.php">
            Create Account
        </a>

    </div>


    <div class="admin">

        Library employee?

        <a href="login.php">
            Administrator Login
        </a>

    </div>


    <div class="note">
        CIS 344 Library Management System
    </div>

</div>

</div>

</body>

</html>