<?php
/**
 * User Logout API
 * POST /api/auth/logout.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../../config/config.php';

// Clear remember token cookie
if (isset($_COOKIE['remember_token'])) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE session_token = ?");
    $stmt->bind_param("s", $_COOKIE['remember_token']);
    $stmt->execute();
    $stmt->close();
    closeDbConnection($conn);
    
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Destroy session
session_unset();
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>
