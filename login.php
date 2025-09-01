<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($username_or_email) || empty($password)) {
        header('Location: login.html?status=error&message=Por favor, complete todos los campos.');
        exit();
    }

    // Prepare a select statement
    $sql = "SELECT id, username, email, password, user_type FROM users WHERE username = ? OR email = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $username_or_email, $username_or_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $username, $email, $hashed_password, $user_type);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Password is correct, start a new session
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['user_type'] = $user_type;

                // Redirect based on user type
                if ($user_type === 'agent') {
                    header('Location: agent_dashboard.php?status=success&message=Bienvenido, ' . $username . '!');
                } else {
                    header('Location: user_dashboard.php?status=success&message=Bienvenido, ' . $username . '!');
                }
            } else {
                header('Location: login.html?status=error&message=Contraseña incorrecta.');
            }
        } else {
            header('Location: login.html?status=error&message=No se encontró el usuario.');
        }
        $stmt->close();
    } else {
        header('Location: login.html?status=error&message=Error en la preparación de la consulta: ' . $conn->error);
    }

    $conn->close();
} else {
    header('Location: login.html');
    exit();
}
?>