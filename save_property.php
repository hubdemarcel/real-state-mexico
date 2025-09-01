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
$property_id = $_POST['property_id'] ?? 0;

if (empty($property_id) || !is_numeric($property_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de propiedad inválido']);
    exit();
}

// Check if property exists
$check_sql = "SELECT id FROM properties WHERE id = ?";
if ($stmt = $conn->prepare($check_sql)) {
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $conn->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Propiedad no encontrada']);
        exit();
    }
    $stmt->close();
}

// Check if already saved
$check_saved_sql = "SELECT id FROM user_saved_properties WHERE user_id = ? AND property_id = ?";
if ($stmt = $conn->prepare($check_saved_sql)) {
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Propiedad ya guardada']);
        exit();
    }
    $stmt->close();
}

// Save the property
$save_sql = "INSERT INTO user_saved_properties (user_id, property_id) VALUES (?, ?)";
if ($stmt = $conn->prepare($save_sql)) {
    $stmt->bind_param("ii", $user_id, $property_id);

    if ($stmt->execute()) {
        // Create notification
        include 'create_notification.php';
        createSavedNotification($user_id, 'Propiedad guardada'); // We'll get the title from property data

        echo json_encode(['success' => true, 'message' => 'Propiedad guardada exitosamente']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al guardar la propiedad: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conn->error]);
}

$conn->close();
?>