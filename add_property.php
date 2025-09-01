<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an agent
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html?status=error&message=Debes iniciar sesión para agregar propiedades.');
    exit();
}

if ($_SESSION['user_type'] !== 'agent') {
    header('Location: user_dashboard.php?status=error&message=Solo los agentes pueden agregar propiedades.');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $location = $_POST['location'] ?? '';
    $property_type = $_POST['property_type'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $bedrooms = $_POST['bedrooms'] ?? null;
    $bathrooms = $_POST['bathrooms'] ?? null;
    $amenities = $_POST['amenities'] ?? '';

    // Basic validation
    if (empty($title) || empty($price) || empty($location) || empty($property_type)) {
        header('Location: agent_dashboard.php?status=error&message=Por favor, complete todos los campos requeridos.');
        exit();
    }

    // Get agent ID
    $agent_id = $_SESSION['id'];

    // Prepare an insert statement
    $sql = "INSERT INTO properties (title, description, price, location, property_type, image_url, agent_id, bedrooms, bathrooms, amenities) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssdsssidds", $title, $description, $price, $location, $property_type, $image_url, $agent_id, $bedrooms, $bathrooms, $amenities);

        if ($stmt->execute()) {
            header('Location: agent_dashboard.php?status=success&message=Propiedad agregada exitosamente!');
        } else {
            header('Location: agent_dashboard.php?status=error&message=Error al agregar la propiedad: ' . $stmt->error);
        }
        $stmt->close();
    } else {
        header('Location: agent_dashboard.php?status=error&message=Error en la preparación de la consulta: ' . $conn->error);
    }

    $conn->close();
} else {
    header('Location: agent_dashboard.php');
    exit();
}
?>