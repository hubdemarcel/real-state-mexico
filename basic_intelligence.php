<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html?status=error&message=Debes iniciar sesión para acceder a las herramientas de mercado.');
    exit();
}

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];
$user_type = $_SESSION['user_type'];

$pageTitle = 'Información de Mercado - Tierras.mx';
include 'header.php';

// Get basic market data for the user's location
$market_data = [
    'avg_price_per_sqm' => 0,
    'total_properties' => 0,
    'avg_property_size' => 0,
    'location_trend' => 'Stable'
];

// Get overall market trend
$overall_trend_sql = "SELECT AVG(value) as avg_trend FROM property_market_trends
                      WHERE trend_type = 'price_growth'
                      ORDER BY calculated_at DESC LIMIT 10";

if ($stmt = $conn->prepare($overall_trend_sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $trend_data = $result->fetch_assoc();
        $avg_trend = $trend_data['avg_trend'] ?? 0;

        if ($avg_trend > 2.0) {
            $market_data['location_trend'] = 'Growing';
        } elseif ($avg_trend < -2.0) {
            $market_data['location_trend'] = 'Declining';
        } else {
            $market_data['location_trend'] = 'Stable';
        }
    }
    $stmt->close();
}

// Get user's preferred location (if available)
$user_location = 'México'; // Default

// Get basic market statistics
$market_sql = "SELECT
    AVG(price) as avg_price,
    COUNT(*) as total_properties,
    AVG(price / NULLIF(size_sqm, 0)) as avg_price_per_sqm
    FROM properties
    WHERE status = 'active'";

if ($stmt = $conn->prepare($market_sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $market_data['avg_price_per_sqm'] = $data['avg_price_per_sqm'] ?? 0;
        $market_data['total_properties'] = $data['total_properties'] ?? 0;
        $market_data['avg_price'] = $data['avg_price'] ?? 0;
    }
    $stmt->close();
}

// Get location-based data from database
$location_data = [];

// Define the locations we want to display
$locations = ['Mexico City', 'Guadalajara', 'Monterrey', 'Cancun', 'Puerto Vallarta', 'Sayulita'];

foreach ($locations as $location) {
    // Get price per sqm for this location
    $price_sql = "SELECT value FROM market_data
                  WHERE location = ? AND data_type = 'price_per_sqm'
                  ORDER BY period_start DESC LIMIT 1";

    $price_value = 0;
    if ($stmt = $conn->prepare($price_sql)) {
        $stmt->bind_param("s", $location);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $price_data = $result->fetch_assoc();
            $price_value = $price_data['value'];
        }
        $stmt->close();
    }

    // Get trend for this location
    $trend_sql = "SELECT value FROM property_market_trends
                  WHERE location = ? AND trend_type = 'price_growth'
                  ORDER BY calculated_at DESC LIMIT 1";

    $trend_value = '+0.0%';
    if ($stmt = $conn->prepare($trend_sql)) {
        $stmt->bind_param("s", $location);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $trend_data = $result->fetch_assoc();
            $trend_value = sprintf('%+.1f%%', $trend_data['value']);
        }
        $stmt->close();
    }

    // Format location name for display
    $display_name = str_replace(' ', '_', strtolower($location));

    $location_data[$display_name] = [
        'avg_price_sqm' => $price_value,
        'trend' => $trend_value
    ];
}

// If no data found for some locations, use fallback values
$fallback_data = [
    'mexico_city' => ['avg_price_sqm' => 185000, 'trend' => '+2.1%'],
    'guadalajara' => ['avg_price_sqm' => 125000, 'trend' => '+1.8%'],
    'monterrey' => ['avg_price_sqm' => 118000, 'trend' => '+3.2%'],
    'cancun' => ['avg_price_sqm' => 152000, 'trend' => '+4.5%'],
    'puerto_vallarta' => ['avg_price_sqm' => 65000, 'trend' => '+1.5%'],
    'sayulita' => ['avg_price_sqm' => 85000, 'trend' => '+2.8%']
];

foreach ($fallback_data as $key => $data) {
    if (!isset($location_data[$key]) || $location_data[$key]['avg_price_sqm'] == 0) {
        $location_data[$key] = $data;
    }
}

$conn->close();
?>

<main class="container">
    <div class="basic-intelligence">
        <!-- Header -->
        <section class="intelligence-header">
            <div class="intelligence-welcome">
                <h1>📊 Información de Mercado</h1>
                <p>Datos básicos del mercado inmobiliario en México</p>
            </div>
            <div class="intelligence-notice">
                <div class="notice-card">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Información Limitada</strong>
                        <p>Accede a datos completos con nuestro plan premium</p>
                        <a href="#" class="btn btn-sm btn-primary" onclick="showPremiumUpgrade()">
                            <i class="fas fa-crown"></i> Actualizar
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Market Overview -->
        <section class="market-overview-section">
            <div class="section-header">
                <h2 class="section-title">📈 Panorama General</h2>
                <p class="section-subtitle">Indicadores básicos del mercado nacional</p>
            </div>

            <div class="market-stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">$<?php echo number_format($market_data['avg_price_per_sqm'], 0); ?></div>
                        <div class="stat-label">Precio Promedio/m²</div>
                        <div class="stat-note">Nacional</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($market_data['total_properties']); ?>+</div>
                        <div class="stat-label">Propiedades Activas</div>
                        <div class="stat-note">En plataforma</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $market_data['location_trend']; ?></div>
                        <div class="stat-label">Tendencia General</div>
                        <div class="stat-note">Últimos 3 meses</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Location-Based Pricing -->
        <section class="location-pricing-section">
            <div class="section-header">
                <h2 class="section-title">📍 Precios por Ciudad</h2>
                <p class="section-subtitle">Precio promedio por metro cuadrado en las principales ciudades</p>
            </div>

            <div class="pricing-table">
                <div class="pricing-header">
                    <div class="city-column">Ciudad</div>
                    <div class="price-column">Precio/m²</div>
                    <div class="trend-column">Tendencia</div>
                </div>

                <?php foreach ($location_data as $city => $data): ?>
                    <div class="pricing-row">
                        <div class="city-column">
                            <strong><?php echo ucfirst(str_replace('_', ' ', $city)); ?></strong>
                        </div>
                        <div class="price-column">
                            $<?php echo number_format($data['avg_price_sqm']); ?>
                        </div>
                        <div class="trend-column <?php echo strpos($data['trend'], '+') === 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $data['trend']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pricing-note">
                <i class="fas fa-info-circle"></i>
                <span>Los precios son promedios y pueden variar según la zona específica y características de la propiedad.</span>
            </div>
        </section>

        <!-- Basic Market Insights -->
        <section class="basic-insights-section">
            <div class="section-header">
                <h2 class="section-title">💡 Información Básica</h2>
                <p class="section-subtitle">Consejos generales para el mercado inmobiliario</p>
            </div>

            <div class="insights-grid">
                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="insight-content">
                        <h3>Investigación Local</h3>
                        <p>El precio por metro cuadrado varía significativamente entre colonias. Investiga la zona específica que te interesa.</p>
                    </div>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="insight-content">
                        <h3>Temporada del Mercado</h3>
                        <p>Los mejores precios suelen encontrarse fuera de temporada alta (verano). Considera el timing en tus decisiones.</p>
                    </div>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="insight-content">
                        <h3>Comparación de Precios</h3>
                        <p>Compara propiedades similares en la misma zona. El tamaño, antigüedad y estado de mantenimiento afectan el precio.</p>
                    </div>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="insight-content">
                        <h3>Negociación</h3>
                        <p>En México, el precio inicial suele ser negociable. Un descuento del 5-10% es común en buenas negociaciones.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Premium Teaser -->
        <section class="premium-teaser-section">
            <div class="teaser-card">
                <div class="teaser-content">
                    <h2>🚀 Desbloquea Todo el Potencial</h2>
                    <p>Accede a análisis avanzados, predicciones de precios, recomendaciones personalizadas y mucho más con nuestro plan premium.</p>

                    <div class="premium-features-preview">
                        <div class="feature">
                            <i class="fas fa-brain"></i>
                            <span>Análisis de mercado con IA</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-chart-line"></i>
                            <span>Predicciones de precios</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-magic"></i>
                            <span>Recomendaciones inteligentes</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-file-export"></i>
                            <span>Reportes detallados</span>
                        </div>
                    </div>

                    <a href="#" class="btn btn-primary btn-large" onclick="showPremiumUpgrade()">
                        <i class="fas fa-crown"></i> Actualizar a Premium
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
.basic-intelligence {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 0;
}

.intelligence-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #333;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.intelligence-welcome h1 {
    margin: 0;
    font-size: 2rem;
}

.intelligence-welcome p {
    margin: 0.5rem 0 0 0;
    opacity: 0.8;
}

.intelligence-notice {
    max-width: 400px;
}

.notice-card {
    background: white;
    border: 2px solid #ffd700;
    border-radius: 10px;
    padding: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.notice-card i {
    color: #ffd700;
    font-size: 1.5rem;
    margin-top: 0.25rem;
}

.notice-card strong {
    display: block;
    color: #333;
    margin-bottom: 0.5rem;
}

.notice-card p {
    margin: 0 0 1rem 0;
    color: #666;
    font-size: 0.9rem;
}

.market-overview-section,
.location-pricing-section,
.basic-insights-section,
.premium-teaser-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    padding: 2rem;
}

.section-header {
    margin-bottom: 2rem;
}

.section-title {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.section-subtitle {
    margin: 0.5rem 0 0 0;
    color: #666;
}

.market-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-left: 4px solid #007bff;
}

.stat-icon {
    font-size: 2rem;
    color: #007bff;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 10px;
}

.stat-content .stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 0.25rem;
}

.stat-content .stat-label {
    color: #666;
    font-size: 0.9rem;
}

.stat-note {
    font-size: 0.8rem;
    color: #888;
    margin-top: 0.25rem;
}

.pricing-table {
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.pricing-header {
    background: #007bff;
    color: white;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 1rem;
    padding: 1rem 1.5rem;
    font-weight: 600;
}

.pricing-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    align-items: center;
}

.pricing-row:last-child {
    border-bottom: none;
}

.pricing-row:hover {
    background: rgba(0,123,255,0.05);
}

.city-column {
    font-weight: 500;
    color: #333;
}

.price-column {
    font-weight: 600;
    color: #007bff;
}

.trend-column {
    text-align: center;
    font-weight: 500;
}

.trend-column.positive {
    color: #28a745;
}

.trend-column.negative {
    color: #dc3545;
}

.pricing-note {
    background: #e3f2fd;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pricing-note i {
    color: #2196f3;
}

.pricing-note span {
    color: #555;
    font-size: 0.9rem;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.insight-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 2rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    border-left: 4px solid #007bff;
}

.insight-icon {
    font-size: 2rem;
    color: #007bff;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 10px;
    flex-shrink: 0;
}

.insight-content h3 {
    margin: 0 0 1rem 0;
    color: #333;
}

.insight-content p {
    margin: 0;
    color: #666;
    line-height: 1.5;
}

.premium-teaser-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem 2rem;
}

.teaser-card {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.teaser-content h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.teaser-content p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.premium-features-preview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.feature {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
}

.feature i {
    color: #ffd700;
    width: 20px;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: #ffd700;
    color: #333;
}

.btn-primary:hover {
    background: #ffed4e;
    transform: translateY(-2px);
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .intelligence-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .notice-card {
        max-width: 100%;
    }

    .market-stats-grid {
        grid-template-columns: 1fr;
    }

    .pricing-header,
    .pricing-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    .pricing-header {
        display: none;
    }

    .pricing-row .city-column,
    .pricing-row .price-column,
    .pricing-row .trend-column {
        padding: 0.25rem 0;
    }

    .pricing-row .city-column::before {
        content: "Ciudad: ";
        font-weight: bold;
    }

    .pricing-row .price-column::before {
        content: "Precio/m²: ";
        font-weight: bold;
    }

    .pricing-row .trend-column::before {
        content: "Tendencia: ";
        font-weight: bold;
    }

    .insights-grid {
        grid-template-columns: 1fr;
    }

    .insight-card {
        flex-direction: column;
        text-align: center;
    }

    .premium-features-preview {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Premium upgrade modal
function showPremiumUpgrade() {
    // Create modal overlay
    const modal = document.createElement('div');
    modal.className = 'premium-modal-overlay';
    modal.innerHTML = `
        <div class="premium-modal">
            <div class="premium-modal-header">
                <h3><i class="fas fa-crown"></i> Actualizar a Premium</h3>
                <button class="modal-close" onclick="closePremiumModal()">&times;</button>
            </div>
            <div class="premium-modal-body">
                <p>Desbloquea análisis completos de mercado, predicciones avanzadas y recomendaciones personalizadas.</p>
                <div class="premium-features-list">
                    <div class="feature-item">
                        <i class="fas fa-brain"></i>
                        <span>Inteligencia artificial para análisis de mercado</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Predicciones de precios y tendencias</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-magic"></i>
                        <span>Recomendaciones personalizadas</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-file-export"></i>
                        <span>Reportes avanzados exportables</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-headset"></i>
                        <span>Soporte prioritario</span>
                    </div>
                </div>
                <div class="premium-pricing">
                    <div class="pricing-card">
                        <h4>Plan Premium</h4>
                        <div class="price">$499<span>/mes</span></div>
                        <p>Acceso completo a todas las herramientas</p>
                    </div>
                </div>
            </div>
            <div class="premium-modal-footer">
                <button class="btn btn-secondary" onclick="closePremiumModal()">Después</button>
                <button class="btn btn-primary" onclick="upgradeToPremium()">
                    <i class="fas fa-star"></i> Actualizar Ahora
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add modal styles
    const style = document.createElement('style');
    style.textContent = `
        .premium-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .premium-modal {
            background: white;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .premium-modal-header {
            padding: 2rem 2rem 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .premium-modal-header h3 {
            margin: 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .premium-modal-header .fa-crown {
            color: #ffd700;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .premium-modal-body {
            padding: 2rem;
        }

        .premium-features-list {
            display: grid;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .feature-item i {
            color: #667eea;
            width: 20px;
        }

        .premium-pricing {
            margin-top: 2rem;
        }

        .pricing-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
        }

        .pricing-card h4 {
            margin: 0 0 1rem 0;
            font-size: 1.5rem;
        }

        .price {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .price span {
            font-size: 1rem;
            opacity: 0.8;
        }

        .pricing-card p {
            margin: 1rem 0 0 0;
            opacity: 0.9;
        }

        .premium-modal-footer {
            padding: 1.5rem 2rem 2rem;
            border-top: 1px solid #eee;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            .premium-modal {
                width: 95%;
                margin: 1rem;
            }

            .premium-modal-header,
            .premium-modal-body,
            .premium-modal-footer {
                padding: 1rem 1.5rem;
            }

            .premium-modal-footer {
                flex-direction: column;
            }
        }
    `;
    document.head.appendChild(style);
}

function closePremiumModal() {
    const modal = document.querySelector('.premium-modal-overlay');
    if (modal) {
        modal.remove();
    }
}

function upgradeToPremium() {
    closePremiumModal();
    alert('Funcionalidad de actualización próximamente disponible. Contacta a soporte para actualizar tu plan.');
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('premium-modal-overlay')) {
        closePremiumModal();
    }
});
</script>

<?php
include 'footer.php';
?>