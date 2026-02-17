<?php
/**
 * User Login API
 * POST /api/auth/login.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$email = sanitize($data['email'] ?? '');
$password = $data['password'] ?? '';
$remember = $data['remember'] ?? false;

// Validation
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

$conn = getDbConnection();

// Get user by email
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, is_active FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    $stmt->close();
    closeDbConnection($conn);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if account is active
if (!$user['is_active']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Account is deactivated']);
    closeDbConnection($conn);
    exit;
}

// Verify password
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    closeDbConnection($conn);
    exit;
}

// Create session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];

// Handle remember me
if ($remember) {
    $token = generateToken();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $conn->prepare("INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)");
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $stmt->bind_param("issss", $user['id'], $token, $ipAddress, $userAgent, $expiresAt);
    $stmt->execute();
    $stmt->close();
    
    // Set cookie
    setcookie('remember_token', $token, strtotime('+30 days'), '/', '', false, true);
}

// Transfer guest cart to user
if (isset($_SESSION['guest_session_id'])) {
    $stmt = $conn->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ?");
    $stmt->bind_param("is", $user['id'], $_SESSION['guest_session_id']);
    $stmt->execute();
    $stmt->close();
    unset($_SESSION['guest_session_id']);
}

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'id' => $user['id'],
        'name' => $user['first_name'] . ' ' . $user['last_name'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
]);

closeDbConnection($conn);
?>
