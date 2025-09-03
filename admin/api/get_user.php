<?php
require_once '../auth.php';
requireAdminLogin();

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
    exit();
}

$user_id = (int)$_GET['id'];

require_once '../../config.php';

$sql = "SELECT id, username, email, user_type, first_name, last_name, phone_number, bio, profile_picture_url, created_at, updated_at
        FROM users WHERE id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
}

returnConnection($conn);
?>