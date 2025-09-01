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
$alert_type = $_POST['alert_type'] ?? '';
$criteria = $_POST['criteria'] ?? '';

if (empty($alert_type) || empty($criteria)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipo de alerta y criterios son requeridos']);
    exit();
}

// Insert alert
$sql = "INSERT INTO user_alerts (user_id, alert_type, criteria) VALUES (?, ?, ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iss", $user_id, $alert_type, $criteria);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Alerta creada exitosamente']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al crear alerta: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conn->error]);
}

$conn->close();
?>