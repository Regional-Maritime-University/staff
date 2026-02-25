<?php

/**
 * Authentication Guard
 *
 * Include this file at the top of every protected page.
 * It handles session validation, role checking, logout, and redirects.
 *
 * Usage:
 *   $allowedRoles = ['secretary', 'admin', 'developers']; // optional, defaults to these
 *   require_once __DIR__ . '/../inc/auth-guard.php';
 *
 * After inclusion, $staffData is available with the current user's session data.
 */

session_name("rmu_staff_portal");
session_start();

// Check if user is logged in
if (
    !isset($_SESSION["staffLoginSuccess"]) ||
    $_SESSION["staffLoginSuccess"] == false ||
    !isset($_SESSION["staff"]["number"]) ||
    empty($_SESSION["staff"]["number"])
) {
    header("Location: ../index.php");
    exit;
}

// Determine allowed roles (can be overridden before including this file)
if (!isset($allowedRoles)) {
    $allowedRoles = ['admin', 'developers', 'secretary'];
}

$isUser = in_array(strtolower($_SESSION["staff"]["role"]), $allowedRoles);

// Handle logout or unauthorized access
if (isset($_GET['logout']) || !$isUser) {
    session_destroy();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    header('Location: ../index.php');
    exit;
}

$staffData = $_SESSION["staff"] ?? null;
$_SESSION["lastAccessed"] = time();
