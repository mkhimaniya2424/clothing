<?php
/**
 * Session Helper Functions
 * Provides consistent session management across the application
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

/**
 * Get current user ID
 * @return int|null
 */
function getUserId() {
    return isLoggedIn() ? $_SESSION['user']['id'] : null;
}

/**
 * Get current user data
 * @return array|null
 */
function getUser() {
    return isLoggedIn() ? $_SESSION['user'] : null;
}

/**
 * Require login - redirect to login page if not authenticated
 * @param string $redirect - Page to redirect back to after login
 */
function requireLogin($redirect = '') {
    if (!isLoggedIn()) {
        $redirectUrl = 'login.php';
        if (!empty($redirect)) {
            $redirectUrl .= '?redirect=' . urlencode($redirect);
        }
        header("Location: $redirectUrl");
        exit();
    }
}

/**
 * Get username
 * @return string
 */
function getUsername() {
    return isLoggedIn() ? $_SESSION['user']['username'] : 'Guest';
}

/**
 * Get user email
 * @return string|null
 */
function getUserEmail() {
    return isLoggedIn() ? $_SESSION['user']['email'] : null;
}
