<?php
session_start();
require 'conn.php';

// Unset all session variables
foreach (array_keys($_SESSION) as $k) { unset($_SESSION[$k]); }

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destroy the session
session_destroy();

header('Location: index.php');
exit();
?>