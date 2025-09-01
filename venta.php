<?php
$pageTitle = 'Vende tu Propiedad Rápido y al Mejor Precio - Tierras.mx';
include 'header.php';
?>

<style>
    /* CSS Reset and Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --secondary: #059669;
        --warning: #f59e0b;
        --danger: #ef4444;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --mexico-blue: #0068ff;
        --mexico-green: #10b981;
        --mexico-red: #ef4444;
        --transition: all 0.3s ease;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --radius-sm: 0.125rem;
        --radius: 0.25rem;
        --radius-md: 0.375rem;
        --radius-lg: 0.5rem;
        --radius-xl: 0.75rem;
        --radius-2xl: 1rem;
        --radius-full: 9999px;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        line-height: 1.5;
        color: var(--gray-700);
        background-color: #ffffff;
        -webkit-font-smoothing: antialiased;
    }

    .container {
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Hero Section */
    .hero {
        position: relative;
        background: linear-gradient(135deg, var(--mexico-blue), var(--primary));
        color: white;
        padding: 6rem 0 4rem;
        min-height: 600px;
        display: flex;
        align-items: center;
    }

    .hero-overlay {
        position: absolute;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.2);
    }

    .hero-content {
        position: relative;
        text-align: center;
        z-index: 10;
        max-width: 800px;
        margin: 0 auto;
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        line-height: 1.1;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.9;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .hero-buttons {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-hero {
        background-color: white;
        color: var(--primary);
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        transition: var(--transition);
        min-width: 200px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-hero:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .btn-hero-secondary {
        background-color: transparent;
        color: white;
        border: 2px solid white;
        min-width: 200px;
    }

    .btn-hero-secondary:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    /* Section Styles */
    .section {
        padding: 4rem 0;
    }

    .section-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .section-title {
        font-size: 2.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--gray-900);
    }

    .section-subtitle {
        font-size: 1.25rem;
        color: var(--gray-600);
        max-width: 700px;
        margin: 0 auto;
    }

    /* Cash Offers Section */
    .cash-offers {
        background-color: var(--gray-50);
        padding: 4rem 0;
    }

    .regions-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .region-card {
        background-color: white;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .region-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .region-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .region-title i {
        color: var(--mexico-blue);
    }

    .region-cities {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .city-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
    }

    .city-item i {
        color: var(--mexico-green);
        font-size: 0.875rem;
    }

    /* Process Section */
    .process-steps {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 2rem;
        max-width: 1000px;
        margin: 0 auto;
    }

    .process-step {
        text-align: center;
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        background-color: white;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .process-step:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .step-number {
        width: 3rem;
        height: 3rem;
        background-color: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 auto 1.5rem;
    }

    .step-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--gray-900);
    }

    .step-description {
        color: var(--gray-600);
        line-height: 1.6;
    }

    /* Benefits Section */
    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 2rem;
        max-width: 1000px;
        margin: 0 auto;
    }

    .benefit-card {
        background-color: white;
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border-left: 4px solid var(--primary);
    }

    .benefit-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .benefit-icon {
        width: 3.5rem;
        height: 3.5rem;
        background-color: var(--gray-50);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: var(--primary);
        font-size: 1.5rem;
    }

    .benefit-title {
        font-size: 1.25rem;
        font-weight: 600;
        text-align: center;
        margin-bottom: 1rem;
        color: var(--gray-900);
    }

    .benefit-description {
        color: var(--gray-600);
        text-align: center;
        line-height: 1.6;
    }

    /* Testimonials Section */
    .testimonials {
        background-color: var(--gray-50);
        padding: 4rem 0;
    }

    .testimonials-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 2rem;
        max-width: 1000px;
        margin: 0 auto;
    }

    .testimonial-card {
        background-color: white;
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow);
        position: relative;
    }

    .testimonial-card::before {
        content: '"';
        position: absolute;
        top: 1rem;
        left: 1.5rem;
        font-size: 5rem;
        color: var(--gray-200);
        font-family: Georgia, serif;
        line-height: 1;
    }

    .testimonial-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .testimonial-content {
        font-style: italic;
        color: var(--gray-700);
        line-height: 1.6;
        margin-bottom: 1.5rem;
        padding-top: 1rem;
    }

    .testimonial-author {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .author-avatar {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        background-color: var(--gray-200);
        overflow: hidden;
    }

    .author-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .author-info {
        display: flex;
        flex-direction: column;
    }

    .author-name {
        font-weight: 600;
        color: var(--gray-900);
    }

    .author-location {
        color: var(--gray-600);
        font-size: 0.875rem;
    }

    /* Notification System */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        color: var(--gray-900);
        padding: 1rem 1.5rem;
        border-radius: var(--radius-md);
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        font-weight: 500;
        max-width: 400px;
        border-left: 4px solid var(--primary);
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .notification.success {
        border-left-color: var(--secondary);
    }

    .notification.warning {
        border-left-color: var(--warning);
    }

    .notification.error {
        border-left-color: var(--danger);
    }

    .notification-icon {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    /* Responsive Design */
    @media (min-width: 768px) {
        .primary-nav {
            display: flex;
        }

        .mobile-menu-btn {
            display: none;
        }

        .hero-title {
            font-size: 3.75rem;
        }

        .region-cities {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .container {
            padding: 0 2rem;
        }

        .process-steps {
            grid-template-columns: repeat(3, 1fr);
        }

        .benefits-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .testimonials-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1280px) {
        .container {
            padding: 0 1.5rem;
        }
    }

    /* Utility Classes */
    .text-center {
        text-align: center;
    }

    .hidden {
        display: none;
    }

    .mx-flag {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .mx-flag::before {
        content: '';
        display: inline-block;
        width: 16px;
        height: 12px;
        background: linear-gradient(to bottom, #006341 33%, #FFFFFF 33%, #FFFFFF 66%, #CE1126 66%);
        border: 1px solid var(--gray-300);
        border-radius: 2px;
        vertical-align: middle;
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* Print Styles */
    @media print {
        .header,
        .footer {
            display: none;
        }

        .hero {
            background: var(--gray-50);
            color: var(--gray-900);
        }

        .property-card {
            break-inside: avoid;
            margin-bottom: 1rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Vende tu Propiedad Rápido y al Mejor Precio</h1>
            <p class="hero-subtitle">Con Tierras.mx, obtén una oferta justa en minutos y vende sin el estrés de las visitas y negociaciones tradicionales.</p>

            <div class="hero-buttons">
                <a href="anunciar.php" class="btn-hero">
                    <i class="fas fa-home"></i> Obtén tu Oferta
                </a>
                <a href="encuentraunagente.php" class="btn-hero btn-hero-secondary">
                    <i class="fas fa-phone"></i> Habla con un Experto
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Cash Offers Section -->
<section class="cash-offers">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">¿En qué Mercados Ofrecemos Compras Directas?</h2>
            <p class="section-subtitle">Actualmente, puedes obtener una oferta de compra directa en los siguientes mercados en México:</p>
        </div>

        <div class="regions-container">
            <!-- Region 1 - Central -->
            <div class="region-card">
                <h3 class="region-title">
                    <i class="fas fa-map-marked-alt"></i> Centro
                </h3>
                <div class="region-cities">
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Ciudad de México
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Estado de México
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Puebla
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Tlaxcala
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Morelos
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Hidalgo
                    </div>
                </div>
            </div>

            <!-- Region 2 - Occidente -->
            <div class="region-card">
                <h3 class="region-title">
                    <i class="fas fa-map-marked-alt"></i> Occidente
                </h3>
                <div class="region-cities">
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Guadalajara
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Zapopan
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Tlaquepaque
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Puerto Vallarta
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> León
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Aguascalientes
                    </div>
                </div>
            </div>

            <!-- Region 3 - Norte -->
            <div class="region-card">
                <h3 class="region-title">
                    <i class="fas fa-map-marked-alt"></i> Norte
                </h3>
                <div class="region-cities">
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Monterrey
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> San Pedro Garza García
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Guadalupe
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Saltillo
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Chihuahua
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Ciudad Juárez
                    </div>
                </div>
            </div>

            <!-- Region 4 - Sureste -->
            <div class="region-card">
                <h3 class="region-title">
                    <i class="fas fa-map-marked-alt"></i> Sureste
                </h3>
                <div class="region-cities">
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Cancún
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Playa del Carmen
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Mérida
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Tulum
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Villahermosa
                    </div>
                    <div class="city-item">
                        <i class="fas fa-circle"></i> Veracruz
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Cómo Funciona Nuestro Proceso de Venta</h2>
            <p class="section-subtitle">Vender tu propiedad con Tierras.mx es simple, rápido y sin complicaciones</p>
        </div>

        <div class="process-steps">
            <!-- Step 1 -->
            <div class="process-step">
                <div class="step-number">1</div>
                <h3 class="step-title">Obtén tu Oferta</h3>
                <p class="step-description">Completa un breve formulario con información sobre tu propiedad y recibe una oferta justa en minutos. Nuestro algoritmo considera el valor del mercado, condiciones de la propiedad y tendencias recientes.</p>
            </div>

            <!-- Step 2 -->
            <div class="process-step">
                <div class="step-number">2</div>
                <h3 class="step-title">Acepta tu Oferta</h3>
                <p class="step-description">Si aceptas la oferta, programamos una visita rápida para verificar la información. Si prefieres vender por tu cuenta, te proporcionamos recursos gratuitos para maximizar tu precio de venta.</p>
            </div>

            <!-- Step 3 -->
            <div class="process-step">
                <div class="step-number">3</div>
                <h3 class="step-title">Cierra la Venta</h3>
                <p class="step-description">Completa la venta en el plazo que prefieras (hasta 30 días). Recibe tu pago en MXN sin comisiones ni gastos ocultos. Nosotros nos encargamos de todo el papeleo y trámites legales.</p>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Beneficios de Vender con Tierras.mx</h2>
            <p class="section-subtitle">Descubre por qué miles de propietarios en México eligen nuestro servicio para vender sus propiedades</p>
        </div>

        <div class="benefits-grid">
            <!-- Benefit 1 -->
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="benefit-title">Venta Rápida</h3>
                <p class="benefit-description">Vende en días en lugar de meses. Sin esperar a que llegue el comprador adecuado ni lidiar con visitas constantes en tu hogar.</p>
            </div>

            <!-- Benefit 2 -->
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h3 class="benefit-title">Oferta Justa</h3>
                <p class="benefit-description">Nuestras ofertas se basan en datos reales del mercado mexicano, no en estimaciones generales. Recibirás un precio justo sin subastas ni sorpresas.</p>
            </div>

            <!-- Benefit 3 -->
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <h3 class="benefit-title">Sin Comisiones</h3>
                <p class="benefit-description">Olvida las comisiones de hasta 6% de las inmobiliarias tradicionales. Con Tierras.mx, pagas solo lo que ves, sin costos ocultos.</p>
            </div>

            <!-- Benefit 4 -->
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="benefit-title">Seguridad Legal</h3>
                <p class="benefit-description">Todos nuestros procesos cumplen con la legislación mexicana. Contamos con abogados especializados en bienes raíces para garantizar una transacción segura.</p>
            </div>

            <!-- Benefit 5 -->
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="benefit-title">Control Total</h3>
                <p class="benefit-description">Tú decides cuándo cerrar la venta. Ofrecemos flexibilidad en fechas de cierre para adaptarnos a tus necesidades personales.</p>
            </div>

            <!-- Benefit 6 -->
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 class="benefit-title">Asesoría Personalizada</h3>
                <p class="benefit-description">Nuestros expertos locales te guiarán en cada paso del proceso, respondiendo todas tus preguntas en español.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Lo que Nuestros Clientes Dicen</h2>
            <p class="section-subtitle">Miles de propietarios han vendido sus propiedades con nuestra ayuda</p>
        </div>

        <div class="testimonials-grid">
            <!-- Testimonial 1 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Gracias a Tierras.mx pude vender mi casa en Guadalajara en solo 15 días. El proceso fue increíblemente simple y obtuve exactamente el precio que quería."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="María G.">
                    </div>
                    <div class="author-info">
                        <span class="author-name">María G.</span>
                        <span class="author-location">Guadalajara, Jalisco</span>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Como primerizo vendedor, estaba nervioso con todo el proceso legal. Tierras.mx se encargó de todo y me explicó cada paso. ¡Altamente recomendado!"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://randomuser.me/api/portraits/men/44.jpg" alt="Carlos M.">
                    </div>
                    <div class="author-info">
                        <span class="author-name">Carlos M.</span>
                        <span class="author-location">Monterrey, Nuevo León</span>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Vender mi departamento en CDMX nunca había sido tan fácil. Sin comisiones ocultas, proceso transparente y pago rápido. Excelente servicio."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Ana L.">
                    </div>
                    <div class="author-info">
                        <span class="author-name">Ana L.</span>
                        <span class="author-location">Ciudad de México</span>
                    </div>
                </div>
            </div>

            <!-- Testimonial 4 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Después de meses intentando vender por mi cuenta, Tierras.mx me dio una oferta justa en días. El equipo fue profesional y confiable en todo momento."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://randomuser.me/api/portraits/men/22.jpg" alt="Jorge R.">
                    </div>
                    <div class="author-info">
                        <span class="author-name">Jorge R.</span>
                        <span class="author-location">Cancún, Quintana Roo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Notification System
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = 'notification';
    if (type !== 'info') notification.classList.add(`notification-${type}`);

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fas ${icons[type]}"></i>
        </div>
        <span>${message}</span>
    `;

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        color: var(--gray-900);
        padding: 1rem 1.5rem;
        border-radius: var(--radius-md);
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        font-weight: 500;
        max-width: 400px;
        border-left: 4px solid var(--primary);
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    `;

    document.body.appendChild(notification);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Get item from localStorage with JSON parsing
function getLocalStorageItem(key, defaultValue) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (e) {
        console.error(`Error parsing localStorage item ${key}:`, e);
        return defaultValue;
    }
}

// Set item in localStorage with JSON stringifying
function setLocalStorageItem(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch (e) {
        console.error(`Error storing item ${key} in localStorage:`, e);
        return false;
    }
}
</script>

<?php
$additionalJs = '<script src="assets/js/main.js"></script>';
include 'footer.php';
?>