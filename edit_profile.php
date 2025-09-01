<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html?status=error&message=Debes iniciar sesión para editar tu perfil.');
    exit();
}

$user_id = $_SESSION['id'];
$pageTitle = 'Editar Perfil - Tierras.mx';
include 'header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];
    $success = false;

    // Validate required fields
    if (empty($first_name) || empty($last_name)) {
        $errors[] = 'Nombre y apellido son obligatorios.';
    }

    // Handle password change
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'Debes proporcionar tu contraseña actual para cambiarla.';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'Las nuevas contraseñas no coinciden.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } else {
            // Verify current password
            $sql = "SELECT password FROM users WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->bind_result($hashed_password);
                $stmt->fetch();
                $stmt->close();

                if (!password_verify($current_password, $hashed_password)) {
                    $errors[] = 'La contraseña actual es incorrecta.';
                }
            }
        }
    }

    if (empty($errors)) {
        // Update user profile
        $update_sql = "UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, bio = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($update_sql)) {
            $stmt->bind_param("sssssi", $first_name, $last_name, $phone_number, $bio, $user_id);

            if ($stmt->execute()) {
                // Update password if provided
                if (!empty($new_password)) {
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $password_sql = "UPDATE users SET password = ? WHERE id = ?";
                    if ($password_stmt = $conn->prepare($password_sql)) {
                        $password_stmt->bind_param("si", $new_hashed_password, $user_id);
                        $password_stmt->execute();
                        $password_stmt->close();
                    }
                }

                $success = true;
                $_SESSION['username'] = $first_name; // Update session username
            } else {
                $errors[] = 'Error al actualizar el perfil: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = 'Error en la preparación de la consulta: ' . $conn->error;
        }
    }
}

// Get current user data
$user_data = [];
$sql = "SELECT username, email, first_name, last_name, phone_number, bio FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}
$conn->close();
?>

<main class="container">
    <div class="profile-edit-container">
        <div class="profile-edit-header">
            <h1>Editar Mi Perfil</h1>
            <p>Actualiza tu información personal y preferencias</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <p>Perfil actualizado exitosamente.</p>
            </div>
        <?php endif; ?>

        <form method="POST" class="profile-edit-form">
            <div class="form-section">
                <h2>Información Personal</h2>

                <div class="form-group">
                    <label for="username">Nombre de Usuario</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" disabled>
                    <small>El nombre de usuario no se puede cambiar.</small>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" disabled>
                    <small>El correo electrónico no se puede cambiar.</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Nombre *</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Apellido *</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone_number">Número de Teléfono</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user_data['phone_number'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="bio">Biografía</label>
                    <textarea id="bio" name="bio" rows="4" placeholder="Cuéntanos un poco sobre ti..."><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="form-section">
                <h2>Cambiar Contraseña</h2>
                <p>Deja estos campos vacíos si no deseas cambiar tu contraseña.</p>

                <div class="form-group">
                    <label for="current_password">Contraseña Actual</label>
                    <input type="password" id="current_password" name="current_password">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Nueva Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="user_dashboard.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<style>
.profile-edit-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.profile-edit-header {
    text-align: center;
    margin-bottom: 2rem;
}

.profile-edit-header h1 {
    color: #333;
    margin-bottom: 0.5rem;
}

.profile-edit-header p {
    color: #666;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

.profile-edit-form {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 2rem;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section h2 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #333;
    font-weight: 500;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.form-group input:disabled {
    background: #f8f9fa;
    color: #6c757d;
    cursor: not-allowed;
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #6c757d;
    font-size: 0.875rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .profile-edit-container {
        margin: 1rem auto;
        padding: 0 0.5rem;
    }

    .profile-edit-form {
        padding: 1rem;
    }
}
</style>

<?php
include 'footer.php';
?>