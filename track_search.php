<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Don't track if not logged in
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$user_id = $_SESSION['id'];
$search_query = $_POST['search_query'] ?? '';
$search_filters = $_POST['search_filters'] ?? '{}';

if (empty($search_query)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Consulta de búsqueda requerida']);
    exit();
}

// Insert search history
$sql = "INSERT INTO user_search_history (user_id, search_query, search_filters) VALUES (?, ?, ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iss", $user_id, $search_query, $search_filters);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Búsqueda registrada']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al registrar búsqueda: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conn->error]);
}

$conn->close();
?>