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

// Check if already favorited
$check_fav_sql = "SELECT id FROM user_favorites WHERE user_id = ? AND property_id = ?";
if ($stmt = $conn->prepare($check_fav_sql)) {
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Propiedad ya en favoritos']);
        exit();
    }
    $stmt->close();
}

// Add to favorites
$fav_sql = "INSERT INTO user_favorites (user_id, property_id) VALUES (?, ?)";
if ($stmt = $conn->prepare($fav_sql)) {
    $stmt->bind_param("ii", $user_id, $property_id);

    if ($stmt->execute()) {
        // Create notification
        include 'create_notification.php';
        createFavoriteNotification($user_id, 'Propiedad agregada a favoritos'); // We'll get the title from property data

        echo json_encode(['success' => true, 'message' => 'Propiedad agregada a favoritos']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al agregar a favoritos: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conn->error]);
}

$conn->close();
?>