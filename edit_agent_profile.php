<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an agent
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html?status=error&message=Debes iniciar sesión para editar tu perfil.');
    exit();
}

if ($_SESSION['user_type'] !== 'agent') {
    header('Location: user_dashboard.php?status=error&message=Esta página es solo para agentes inmobiliarios.');
    exit();
}

$pageTitle = 'Editar Perfil de Agente - Tierras.mx';
include 'header.php';

// Get current agent profile
$user_id = $_SESSION['id'];
$agentProfile = [
    'first_name' => '',
    'last_name' => '',
    'phone_number' => '',
    'bio' => '',
    'company' => '',
    'license_number' => '',
    'specialties' => '',
    'experience_years' => 0,
    'location' => '',
    'website' => '',
    'profile_picture_url' => ''
];

$agent_sql = "SELECT * FROM agents WHERE user_id = ?";
if ($stmt = $conn->prepare($agent_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $agent_data = $result->fetch_assoc();
        $agentProfile = array_merge($agentProfile, $agent_data);
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $company = $_POST['company'] ?? '';
    $license_number = $_POST['license_number'] ?? '';
    $specialties = $_POST['specialties'] ?? '';
    $experience_years = (int)($_POST['experience_years'] ?? 0);
    $location = $_POST['location'] ?? '';
    $website = $_POST['website'] ?? '';
    $profile_picture_url = $_POST['profile_picture_url'] ?? '';

    // Check if agent record exists
    $check_sql = "SELECT id FROM agents WHERE user_id = ?";
    $agent_exists = false;
    $agent_record_id = null;

    if ($stmt = $conn->prepare($check_sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $agent_exists = true;
            $row = $result->fetch_assoc();
            $agent_record_id = $row['id'];
        }
        $stmt->close();
    }

    if ($agent_exists) {
        // Update existing record
        $update_sql = "UPDATE agents SET first_name = ?, last_name = ?, phone_number = ?, bio = ?, company = ?, license_number = ?, specialties = ?, experience_years = ?, location = ?, website = ?, profile_picture_url = ? WHERE user_id = ?";
        if ($stmt = $conn->prepare($update_sql)) {
            $stmt->bind_param("sssssssisssi", $first_name, $last_name, $phone_number, $bio, $company, $license_number, $specialties, $experience_years, $location, $website, $profile_picture_url, $user_id);
            if ($stmt->execute()) {
                header('Location: agent_dashboard.php?status=success&message=Perfil actualizado exitosamente!');
            } else {
                $error = 'Error al actualizar el perfil: ' . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO agents (user_id, first_name, last_name, phone_number, bio, company, license_number, specialties, experience_years, location, website, profile_picture_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($insert_sql)) {
            $stmt->bind_param("issssssissss", $user_id, $first_name, $last_name, $phone_number, $bio, $company, $license_number, $specialties, $experience_years, $location, $website, $profile_picture_url);
            if ($stmt->execute()) {
                header('Location: agent_dashboard.php?status=success&message=Perfil creado exitosamente!');
            } else {
                $error = 'Error al crear el perfil: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<main class="container">
    <div class="profile-edit-container">
        <div class="profile-edit-header">
            <h1>Editar Perfil de Agente</h1>
            <p>Actualiza tu información profesional para que los clientes puedan conocerte mejor</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form class="profile-edit-form" method="post" action="edit_agent_profile.php">
            <div class="form-section">
                <h2>Información Personal</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Nombre *</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($agentProfile['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Apellido *</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($agentProfile['last_name']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone_number">Teléfono</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($agentProfile['phone_number']); ?>">
                </div>
                <div class="form-group">
                    <label for="profile_picture_url">URL de Foto de Perfil</label>
                    <input type="url" id="profile_picture_url" name="profile_picture_url" value="<?php echo htmlspecialchars($agentProfile['profile_picture_url']); ?>" placeholder="https://ejemplo.com/foto.jpg">
                </div>
            </div>

            <div class="form-section">
                <h2>Información Profesional</h2>
                <div class="form-group">
                    <label for="company">Empresa/Inmobiliaria</label>
                    <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($agentProfile['company']); ?>" placeholder="Ej: RE/MAX Capital">
                </div>
                <div class="form-group">
                    <label for="license_number">Número de Licencia</label>
                    <input type="text" id="license_number" name="license_number" value="<?php echo htmlspecialchars($agentProfile['license_number']); ?>" placeholder="Ej: 123456789">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="experience_years">Años de Experiencia</label>
                        <input type="number" id="experience_years" name="experience_years" value="<?php echo $agentProfile['experience_years']; ?>" min="0" max="50">
                    </div>
                    <div class="form-group">
                        <label for="location">Ubicación Principal</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($agentProfile['location']); ?>" placeholder="Ej: Polanco, CDMX">
                    </div>
                </div>
                <div class="form-group">
                    <label for="website">Sitio Web</label>
                    <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($agentProfile['website']); ?>" placeholder="https://www.tu-sitio-web.com">
                </div>
            </div>

            <div class="form-section">
                <h2>Especialidades y Biografía</h2>
                <div class="form-group">
                    <label for="specialties">Especialidades</label>
                    <textarea id="specialties" name="specialties" rows="3" placeholder="Ej: Casas Residenciales, Departamentos, Inversión"><?php echo htmlspecialchars($agentProfile['specialties']); ?></textarea>
                    <small>Separa las especialidades con comas</small>
                </div>
                <div class="form-group">
                    <label for="bio">Biografía</label>
                    <textarea id="bio" name="bio" rows="6" placeholder="Cuéntanos sobre tu experiencia y por qué los clientes deberían elegirte..."><?php echo htmlspecialchars($agentProfile['bio']); ?></textarea>
                    <small>Máximo 500 caracteres</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="agent_dashboard.php" class="btn btn-secondary">Cancelar</a>
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
    text-align: center;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
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
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
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
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #666;
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
    .profile-edit-container {
        margin: 1rem auto;
        padding: 0 0.5rem;
    }

    .profile-edit-form {
        padding: 1.5rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Character counter for bio
document.addEventListener('DOMContentLoaded', function() {
    const bioTextarea = document.getElementById('bio');
    const maxLength = 500;

    if (bioTextarea) {
        // Add character counter
        const counter = document.createElement('div');
        counter.style.cssText = 'text-align: right; color: #666; font-size: 0.875rem; margin-top: 0.25rem;';
        bioTextarea.parentNode.appendChild(counter);

        function updateCounter() {
            const remaining = maxLength - bioTextarea.value.length;
            counter.textContent = `${remaining} caracteres restantes`;

            if (remaining < 0) {
                counter.style.color = '#dc3545';
                bioTextarea.style.borderColor = '#dc3545';
            } else if (remaining < 50) {
                counter.style.color = '#ffc107';
                bioTextarea.style.borderColor = '#ffc107';
            } else {
                counter.style.color = '#666';
                bioTextarea.style.borderColor = '#ddd';
            }
        }

        bioTextarea.addEventListener('input', updateCounter);
        updateCounter(); // Initial call
    }
});
</script>

<?php
include 'footer.php';
?>