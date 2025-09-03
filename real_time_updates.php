<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    exit('Unauthorized');
}

$user_id = $_SESSION['id'];

// Rate limiting - only allow one connection per user every 30 seconds
$cache_key = "sse_user_{$user_id}";
$cache_file = sys_get_temp_dir() . "/{$cache_key}.cache";

if (file_exists($cache_file)) {
    $last_request = (int)file_get_contents($cache_file);
    $time_diff = time() - $last_request;

    if ($time_diff < 30) {
        // Too frequent, return cached response
        http_response_code(429);
        exit('Rate limited');
    }
}

// Update cache
file_put_contents($cache_file, time());

// Set headers for Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

// Function to send SSE data
function sendSSE($event, $data) {
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Get initial data
$last_check = isset($_GET['last_check']) ? $_GET['last_check'] : date('Y-m-d H:i:s', strtotime('-1 hour'));

// Check for new notifications
$notification_sql = "SELECT COUNT(*) as new_count FROM notifications
                     WHERE user_id = ? AND is_read = FALSE AND created_at > ?";
if ($stmt = $conn->prepare($notification_sql)) {
    $stmt->bind_param("is", $user_id, $last_check);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['new_count'] > 0) {
            sendSSE('notification_update', [
                'type' => 'new_notifications',
                'count' => (int)$row['new_count'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    $stmt->close();
}

// Check for new messages
$message_sql = "SELECT COUNT(*) as new_count FROM user_messages
                WHERE receiver_id = ? AND is_read = FALSE AND sent_at > ?";
if ($stmt = $conn->prepare($message_sql)) {
    $stmt->bind_param("is", $user_id, $last_check);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['new_count'] > 0) {
            sendSSE('message_update', [
                'type' => 'new_messages',
                'count' => (int)$row['new_count'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    $stmt->close();
}

// Check for property updates (new properties matching user criteria)
$alerts_sql = "SELECT criteria FROM user_alerts WHERE user_id = ? AND is_active = 1";
$alerts = [];
if ($stmt = $conn->prepare($alerts_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $alerts[] = $row['criteria'];
    }
    $stmt->close();
}

// For each alert, check for new matching properties
foreach ($alerts as $alert_criteria) {
    $criteria = json_decode($alert_criteria, true);
    if (!$criteria) continue;

    $where_clauses = ["created_at > ?"];
    $params = [$last_check];
    $types = "s";

    // Build query based on alert criteria
    if (isset($criteria['location']) && !empty($criteria['location'])) {
        $where_clauses[] = "location LIKE ?";
        $params[] = "%" . $criteria['location'] . "%";
        $types .= "s";
    }

    if (isset($criteria['property_type']) && !empty($criteria['property_type'])) {
        $where_clauses[] = "property_type = ?";
        $params[] = $criteria['property_type'];
        $types .= "s";
    }

    if (isset($criteria['min_price']) && !empty($criteria['min_price'])) {
        $where_clauses[] = "price >= ?";
        $params[] = (float)$criteria['min_price'];
        $types .= "d";
    }

    if (isset($criteria['max_price']) && !empty($criteria['max_price'])) {
        $where_clauses[] = "price <= ?";
        $params[] = (float)$criteria['max_price'];
        $types .= "d";
    }

    $property_sql = "SELECT COUNT(*) as new_count FROM properties WHERE " . implode(' AND ', $where_clauses);
    if ($stmt = $conn->prepare($property_sql)) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($row['new_count'] > 0) {
                sendSSE('property_alert', [
                    'type' => 'new_properties',
                    'count' => (int)$row['new_count'],
                    'criteria' => $criteria,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        }
        $stmt->close();
    }
}

$conn->close();

// Send heartbeat every 30 seconds to keep connection alive
while (true) {
    sendSSE('heartbeat', ['timestamp' => date('Y-m-d H:i:s')]);
    sleep(30);
}
?>