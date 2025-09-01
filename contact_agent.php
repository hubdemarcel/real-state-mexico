<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html?status=error&message=Debes iniciar sesión para contactar a un agente.');
    exit();
}

$agent_id = $_GET['agent_id'] ?? 0;
$pageTitle = 'Contactar Agente - Tierras.mx';
include 'header.php';

// Get agent information
$agent_info = [];
if ($agent_id) {
    $sql = "SELECT u.username, u.email, a.first_name, a.last_name, a.phone_number, a.bio, a.profile_picture_url
            FROM users u
            INNER JOIN agents a ON u.id = a.user_id
            WHERE u.id = ? AND u.user_type = 'agent'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $agent_info = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

$conn->close();
?>

<main class="container">
    <div class="contact-agent-container">
        <div class="contact-agent-header">
            <h1>Contactar Agente</h1>
            <p>Envía un mensaje al agente inmobiliario</p>
        </div>

        <?php if (empty($agent_info)): ?>
            <div class="alert alert-error">
                <p>Agente no encontrado.</p>
                <a href="encuentraunagente.php" class="btn btn-primary">Buscar Agentes</a>
            </div>
        <?php else: ?>
            <div class="contact-form-section">
                <div class="agent-info-card">
                    <div class="agent-avatar">
                        <?php if ($agent_info['profile_picture_url']): ?>
                            <img src="<?php echo htmlspecialchars($agent_info['profile_picture_url']); ?>" alt="Foto de perfil">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="agent-details">
                        <h3><?php echo htmlspecialchars($agent_info['first_name'] . ' ' . $agent_info['last_name']); ?></h3>
                        <p><?php echo htmlspecialchars($agent_info['username']); ?></p>
                        <p><?php echo htmlspecialchars($agent_info['email']); ?></p>
                        <?php if ($agent_info['phone_number']): ?>
                            <p><?php echo htmlspecialchars($agent_info['phone_number']); ?></p>
                        <?php endif; ?>
                        <?php if ($agent_info['bio']): ?>
                            <p class="agent-bio"><?php echo htmlspecialchars($agent_info['bio']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <form class="contact-form" onsubmit="sendMessage(event, <?php echo $agent_id; ?>)">
                    <div class="form-group">
                        <label for="subject">Asunto</label>
                        <input type="text" id="subject" name="subject" required placeholder="Ej: Interesado en propiedad en CDMX">
                    </div>

                    <div class="form-group">
                        <label for="message">Mensaje</label>
                        <textarea id="message" name="message" rows="6" required placeholder="Describe tu consulta o interés..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
                        <a href="user_dashboard.php" class="btn btn-secondary">Volver al Panel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.contact-agent-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.contact-agent-header {
    text-align: center;
    margin-bottom: 2rem;
}

.contact-agent-header h1 {
    color: #333;
    margin-bottom: 0.5rem;
}

.contact-agent-header p {
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

.contact-form-section {
    display: grid;
    gap: 2rem;
}

.agent-info-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.agent-avatar {
    font-size: 4rem;
    color: #007bff;
}

.agent-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}

.agent-details h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.agent-details p {
    margin: 0.25rem 0;
    color: #666;
}

.agent-bio {
    margin-top: 1rem !important;
    font-style: italic;
}

.contact-form {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 2rem;
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

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
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
    .agent-info-card {
        flex-direction: column;
        text-align: center;
    }

    .form-actions {
        flex-direction: column;
    }

    .contact-agent-container {
        margin: 1rem auto;
        padding: 0 0.5rem;
    }
}
</style>

<script>
function sendMessage(event, agentId) {
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append('receiver_id', agentId);

    const submitButton = event.target.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Enviando...';
    submitButton.disabled = true;

    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Mensaje enviado exitosamente');
            event.target.reset();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar el mensaje');
    })
    .finally(() => {
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    });
}
</script>

<?php
include 'footer.php';
?>