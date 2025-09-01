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

// Remove from favorites
$unfav_sql = "DELETE FROM user_favorites WHERE user_id = ? AND property_id = ?";
if ($stmt = $conn->prepare($unfav_sql)) {
    $stmt->bind_param("ii", $user_id, $property_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Propiedad eliminada de favoritos']);
        } else {
            echo json_encode(['success' => false, 'message' => 'La propiedad no estaba en favoritos']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al eliminar de favoritos: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conn->error]);
}

$conn->close();
?>