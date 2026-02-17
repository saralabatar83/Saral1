<?php
/**
 * User Profile API
 * GET /api/account/profile.php - Get profile
 * PUT /api/account/profile.php - Update profile
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

$userId = $_SESSION['user_id'];
$conn = getDbConnection();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, avatar, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        echo json_encode(['success' => true, 'data' => $user]);
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $firstName = sanitize($data['first_name'] ?? '');
        $lastName = sanitize($data['last_name'] ?? '');
        $phone = sanitize($data['phone'] ?? '');
        
        if (empty($firstName) || empty($lastName)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'First name and last name are required']);
            break;
        }
        
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $firstName, $lastName, $phone, $userId);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            echo json_encode(['success' => true, 'message' => 'Profile updated']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
        $stmt->close();
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

closeDbConnection($conn);
?>
