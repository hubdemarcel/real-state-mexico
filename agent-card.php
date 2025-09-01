<?php
// Agent Card Component
function renderAgentCard($agent) {
    $id = $agent['id'] ?? 'default-id';
    $name = $agent['name'] ?? 'Agente';
    $company = $agent['company'] ?? 'Inmobiliaria';
    $location = $agent['location'] ?? 'Ubicación no especificada';
    $rating = $agent['rating'] ?? 0;
    $reviewCount = $agent['reviewCount'] ?? 0;
    $avatar = $agent['avatar'] ?? 'https://randomuser.me/api/portraits/men/32.jpg';
    $specialties = $agent['specialties'] ?? [];
    $phone = $agent['phone'] ?? '';
    $email = $agent['email'] ?? '';
    $properties = $agent['properties'] ?? 0;
    $experience = $agent['experience'] ?? 0;

    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $stars .= '<i class="fas fa-star"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $stars .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $stars .= '<i class="far fa-star"></i>';
        }
    }
    ?>
    <div class="agent-card" style="opacity: 1; transform: translateY(0px); transition: opacity 0.6s, transform 0.6s;">
        <div class="agent-top">
            <div class="agent-avatar">
                <img src="<?php echo $avatar; ?>" alt="<?php echo htmlspecialchars($name); ?>">
            </div>
            <div class="agent-info">
                <h3 class="agent-name"><?php echo htmlspecialchars($name); ?></h3>
                <div class="agent-company"><?php echo htmlspecialchars($company); ?></div>
                <div class="agent-rating">
                    <div class="rating-stars">
                        <?php echo $stars; ?>
                    </div>
                    <div class="rating-count">(<?php echo $reviewCount; ?> reseñas)</div>
                </div>
            </div>
        </div>

        <div class="agent-stats">
            <div class="stat-item">
                <div class="stat-label">Rango de precios del equipo</div>
                <div class="stat-value">$1.2M - $12.5M MXN</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Ventas del equipo últimos 12 meses</div>
                <div class="stat-value"><?php echo $properties; ?> ventas</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Ventas en <?php echo htmlspecialchars($location); ?></div>
                <div class="stat-value">325 ventas</div>
            </div>
        </div>

        <div class="agent-cta">
            <a href="contact_agent.php?agent_id=<?php echo $id; ?>" class="btn-contact">
                <i class="fas fa-envelope"></i> Contactar
            </a>
        </div>
    </div>
    <?php
}
?>