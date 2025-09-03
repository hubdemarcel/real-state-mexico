<?php
session_start();
require_once '../config.php';

// Redirect if already logged in as admin
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['user_type'] === 'admin') {
    header('Location: index.php');
    exit();
}

// Handle login form submission
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $login_error = 'Por favor, complete todos los campos.';
    } else {
        // Check if user exists and is admin
        $sql = "SELECT id, username, email, password, user_type, first_name, last_name FROM users WHERE username = ? AND user_type = 'admin'";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, create session
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];

                    // Log admin login
                    logAdminAction($user['id'], 'login', 'user', $user['id'], null, ['login_time' => date('Y-m-d H:i:s')]);

                    // Redirect to admin dashboard
                    header('Location: index.php');
                    exit();
                } else {
                    $login_error = 'Contraseña incorrecta.';
                }
            } else {
                $login_error = 'Usuario administrador no encontrado.';
            }
            $stmt->close();
        } else {
            $login_error = 'Error en el sistema. Por favor, inténtelo de nuevo.';
        }
    }
}

$conn->close();

// Function to log admin actions
function logAdminAction($admin_id, $action, $entity_type, $entity_id = null, $old_values = null, $new_values = null) {
    global $conn;

    $sql = "INSERT INTO admin_audit_log (admin_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $old_json = $old_values ? json_encode($old_values) : null;
        $new_json = $new_values ? json_encode($new_values) : null;

        $stmt->bind_param("ississss", $admin_id, $action, $entity_type, $entity_id, $old_json, $new_json, $ip, $user_agent);
        $stmt->execute();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Tierras.mx</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            margin: 0;
        }

        .admin-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="admin-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Panel Administrativo</h1>
            <p>Tierras.mx - Sistema de Administración</p>
        </div>

        <?php if (!empty($login_error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username"
                       placeholder="Usuario administrador" required
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                <label for="username">
                    <i class="fas fa-user me-2"></i>Usuario Administrador
                </label>
            </div>

            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="Contraseña" required>
                <label for="password">
                    <i class="fas fa-lock me-2"></i>Contraseña
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
            </button>
        </form>

        <div class="back-link">
            <a href="../index.php">
                <i class="fas fa-arrow-left me-1"></i>Volver al sitio principal
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>