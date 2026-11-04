<?php

require __DIR__ . '/config.php';

$error = '';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === 'admin123') {

        $_SESSION['admin_logged_in'] = true;

        header('Location: index.php');
        exit;

    } else {

        $error = 'Incorrect username or password.';

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

    <title>Admin Login - Library Management System</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background: white;
            width: 350px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .login-box h1 {
            text-align: center;
            margin-bottom: 5px;
        }

        .login-box p {
            text-align: center;
            margin-bottom: 25px;
            color: #666;
        }

        .login-box label {
            display: block;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .login-box input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .login-error {
            background: #ffdede;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .login-note {
            margin-top: 20px;
            font-size: 13px;
            text-align: center;
            color: #777;
        }

    </style>

</head>


<body>

<div class="login-page">

    <div class="login-box">

        <h1>📚 Library</h1>

        <p>Administrator Login</p>


        <?php if ($error): ?>

            <div class="login-error">

                <?= h($error) ?>

            </div>

        <?php endif; ?>


        <form method="post">

            <label>

                Username

                <input
                    type="text"
                    name="username"
                    placeholder="Enter username"
                    required
                >

            </label>


            <label>

                Password

                <input
                    type="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >

            </label>


            <button type="submit">

                Login

            </button>

        </form>


        <div class="login-note">

            CIS 344 Library Management System

        </div>

    </div>

</div>

</body>

</html>