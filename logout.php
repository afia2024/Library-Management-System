<?php

require __DIR__ . '/config.php';

/*
=====================================================
ADMIN / STAFF LOGOUT
=====================================================
*/

// Remove ONLY the admin login session
unset($_SESSION['admin_logged_in']);

// Regenerate the session ID for security
session_regenerate_id(true);

// Send the employee back to the main homepage
header('Location: home.php');

exit;