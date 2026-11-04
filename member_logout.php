<?php

require __DIR__ . '/config.php';

/*
=====================================================
MEMBER LOGOUT
=====================================================
*/

// Remove ONLY the member login information
unset($_SESSION['member_id']);
unset($_SESSION['member_name']);

// Regenerate session ID for security
session_regenerate_id(true);

// After logout, return to the MAIN HOME PAGE
header('Location: home.php');

exit;