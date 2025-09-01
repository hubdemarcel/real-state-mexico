<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($user_type)) {
        header('Location: register.html?status=error&message=Por favor, complete todos los campos requeridos.');
        exit();
    }

    if ($password !== $confirm_password) {
        header('Location: register.html?status=error&message=Las contraseñas no coinciden.');
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Insert into users table
        $sql_user = "INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)";
        if ($stmt_user = $conn->prepare($sql_user)) {
            $stmt_user->bind_param("ssss", $username, $email, $hashed_password, $user_type);
            if (!$stmt_user->execute()) {
                throw new Exception("Error al registrar el usuario: " . $stmt_user->error);
            }
            $user_id = $conn->insert_id; // Get the ID of the newly inserted user
            $stmt_user->close();
        } else {
            throw new Exception("Error en la preparación de la consulta de usuario: " . $conn->error);
        }

        // If user_type is agent, insert into agents table
        if ($user_type === 'agent') {
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $phone_number = $_POST['phone_number'] ?? '';
            $bio = $_POST['bio'] ?? '';
            $profile_picture_url = $_POST['profile_picture_url'] ?? '';

            // Basic validation for agent fields
            if (empty($first_name) || empty($last_name)) {
                throw new Exception("Por favor, complete los campos de nombre y apellido para el agente.");
            }

            $sql_agent = "INSERT INTO agents (user_id, first_name, last_name, phone_number, bio, profile_picture_url) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt_agent = $conn->prepare($sql_agent)) {
                $stmt_agent->bind_param("isssss", $user_id, $first_name, $last_name, $phone_number, $bio, $profile_picture_url);
                if (!$stmt_agent->execute()) {
                    throw new Exception("Error al registrar el agente: " . $stmt_agent->error);
                }
                $stmt_agent->close();
            } else {
                throw new Exception("Error en la preparación de la consulta de agente: " . $conn->error);
            }
        }

        // Commit the transaction
        $conn->commit();
        header('Location: login.html?status=success&message=Registro exitoso. Ahora puedes iniciar sesión.');
        exit();

    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        header('Location: register.html?status=error&message=' . urlencode($e->getMessage()));
        exit();
    } finally {
        $conn->close();
    }
} else {
    header('Location: register.html');
    exit();
}
?>