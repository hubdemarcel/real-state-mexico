<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$user_id = $_SESSION['id'];
$notification_id = $_POST['notification_id'] ?? 0;

if (empty($notification_id) || !is_numeric($notification_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de notificación inválido']);
    exit();
}

// Mark notification as read (only if it belongs to the user)
$sql = "UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $notification_id, $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Notificación no encontrada o no autorizada']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar notificación: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conn->error]);
}

$conn->close();
?>