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

$sender_id = $_SESSION['id'];
$receiver_id = $_POST['receiver_id'] ?? 0;
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($receiver_id) || !is_numeric($receiver_id) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Destinatario y mensaje son requeridos']);
    exit();
}

// Check if receiver exists and is an agent
$check_receiver_sql = "SELECT id, user_type FROM users WHERE id = ?";
if ($stmt = $conn->prepare($check_receiver_sql)) {
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Destinatario no encontrado']);
        exit();
    }

    $receiver_data = $result->fetch_assoc();
    if ($receiver_data['user_type'] !== 'agent') {
        $stmt->close();
        $conn->close();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Solo se puede enviar mensajes a agentes']);
        exit();
    }
    $stmt->close();
}

// Insert message
$sql = "INSERT INTO user_messages (sender_id, receiver_id, subject, message) VALUES (?, ?, ?, ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $subject, $message);

    if ($stmt->execute()) {
        // Create notification for receiver
        include 'create_notification.php';
        $sender_name = $_SESSION['username'] ?? 'Usuario';
        createMessageNotification($receiver_id, $sender_name, $subject);

        echo json_encode(['success' => true, 'message' => 'Mensaje enviado exitosamente']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al enviar mensaje: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conn->error]);
}

$conn->close();
?>