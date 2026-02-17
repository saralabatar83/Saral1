<?php
/**
 * Cart API
 * GET /api/cart/ - Get cart items
 * POST /api/cart/ - Add to cart
 * PUT /api/cart/ - Update cart item
 * DELETE /api/cart/ - Remove from cart
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/config.php';

$conn = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Get user ID or session ID
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$sessionId = null;

if (!$userId) {
    if (!isset($_SESSION['guest_session_id'])) {
        $_SESSION['guest_session_id'] = generateToken(16);
    }
    $sessionId = $_SESSION['guest_session_id'];
}

switch ($method) {
    case 'GET':
        // Get cart items
        if ($userId) {
            $stmt = $conn->prepare("SELECT c.*, p.name, p.slug, p.price, p.sale_price, p.quantity as stock,
                                           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                                    FROM cart c
                                    JOIN products p ON c.product_id = p.id
                                    WHERE c.user_id = ?");
            $stmt->bind_param("i", $userId);
        } else {
            $stmt = $conn->prepare("SELECT c.*, p.name, p.slug, p.price, p.sale_price, p.quantity as stock,
                                           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                                    FROM cart c
                                    JOIN products p ON c.product_id = p.id
                                    WHERE c.session_id = ?");
            $stmt->bind_param("s", $sessionId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        $subtotal = 0;
        
        while ($row = $result->fetch_assoc()) {
            $price = $row['sale_price'] ? floatval($row['sale_price']) : floatval($row['price']);
            $row['unit_price'] = $price;
            $row['total_price'] = $price * $row['quantity'];
            $subtotal += $row['total_price'];
            $items[] = $row;
        }
        $stmt->close();
        
        $tax = $subtotal * TAX_RATE;
        $shipping = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
        $total = $subtotal + $tax + $shipping;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'items' => $items,
                'summary' => [
                    'subtotal' => round($subtotal, 2),
                    'tax' => round($tax, 2),
                    'shipping' => round($shipping, 2),
                    'total' => round($total, 2),
                    'item_count' => count($items)
                ]
            ]
        ]);
        break;
        
    case 'POST':
        // Add to cart
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = intval($data['product_id'] ?? 0);
        $quantity = max(1, intval($data['quantity'] ?? 1));
        
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            break;
        }
        
        // Check product exists and has stock
        $stmt = $conn->prepare("SELECT id, quantity, max_quantity FROM products WHERE id = ? AND is_active = 1");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            break;
        }
        
        if ($product['quantity'] < $quantity) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            break;
        }
        
        // Check if already in cart
        if ($userId) {
            $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $productId);
        } else {
            $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
            $stmt->bind_param("si", $sessionId, $productId);
        }
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($existing) {
            // Update quantity
            $newQuantity = min($existing['quantity'] + $quantity, $product['max_quantity']);
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $newQuantity, $existing['id']);
        } else {
            // Insert new item
            if ($userId) {
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $userId, $productId, $quantity);
            } else {
                $stmt = $conn->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $sessionId, $productId, $quantity);
            }
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Added to cart']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
        }
        $stmt->close();
        break;
        
    case 'PUT':
        // Update cart item
        $data = json_decode(file_get_contents('php://input'), true);
        $cartId = intval($data['cart_id'] ?? 0);
        $quantity = max(1, intval($data['quantity'] ?? 1));
        
        if (!$cartId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cart ID is required']);
            break;
        }
        
        if ($userId) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $cartId, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND session_id = ?");
            $stmt->bind_param("iis", $quantity, $cartId, $sessionId);
        }
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        }
        $stmt->close();
        break;
        
    case 'DELETE':
        // Remove from cart
        $data = json_decode(file_get_contents('php://input'), true);
        $cartId = intval($data['cart_id'] ?? 0);
        $clearAll = $data['clear_all'] ?? false;
        
        if ($clearAll) {
            if ($userId) {
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
            } else {
                $stmt = $conn->prepare("DELETE FROM cart WHERE session_id = ?");
                $stmt->bind_param("s", $sessionId);
            }
        } else {
            if (!$cartId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cart ID is required']);
                break;
            }
            
            if ($userId) {
                $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $cartId, $userId);
            } else {
                $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
                $stmt->bind_param("is", $cartId, $sessionId);
            }
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Removed from cart']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to remove from cart']);
        }
        $stmt->close();
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

closeDbConnection($conn);
?>
