
<?php
$pageTitle = 'Créditos Hipotecarios - Tierras.mx';
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
        z-index: 10;
        max-width: 800px;
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
    }

    /* Mortgage Calculator */
    .mortgage-calculator {
        background-color: white;
        border-radius: var(--radius-xl);
        padding: 2rem;
        box-shadow: var(--shadow-xl);
        max-width: 700px;
    }

    .calculator-tabs {
        display: flex;
        border-bottom: 1px solid var(--gray-200);
        margin-bottom: 1.5rem;
    }

    .calculator-tab {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        cursor: pointer;
        position: relative;
        color: var(--gray-600);
    }

    .calculator-tab.active {
        color: var(--primary);
        font-weight: 600;
    }

    .calculator-tab.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--primary);
        border-radius: 3px 3px 0 0;
    }

    .calculator-form {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .calculator-form {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--gray-700);
    }

    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        font-size: 1rem;
        color: var(--gray-900);
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        font-size: 1rem;
        color: var(--gray-900);
        background-color: white;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M7 10l5 5 5-5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 1rem;
    }

    .form-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-range {
        width: 100%;
        margin: 0.5rem 0;
    }

    .range-values {
        display: flex;
        justify-content: space-between;
        color: var(--gray-600);
        font-size: 0.875rem;
    }

    .results-section {
        background-color: var(--gray-50);
        padding: 2rem;
        border-radius: var(--radius-lg);
        margin-top: 1.5rem;
    }

    .results-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .result-card {
        text-align: center;
        padding: 1.5rem;
        border-radius: var(--radius-md);
        background-color: white;
        box-shadow: var(--shadow);
    }

    .result-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .result-label {
        color: var(--gray-600);
        font-weight: 500;
    }

    .btn-calculate {
        background-color: var(--primary);
        color: white;
        width: 100%;
        padding: 0.75rem;
        border-radius: var(--radius-md);
        font-weight: 500;
        font-size: 1rem;
        transition: var(--transition);
        margin-top: 1rem;
        border: none;
        cursor: pointer;
    }

    .btn-calculate:hover {
        background-color: var(--primary-dark);
    }

    /* Section Styles */
    .section {
        padding: 4rem 0;
    }

    .section-featured {
        background-color: var(--gray-50);
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
    }

    /* Mortgage Types */
    .mortgage-types {
        max-width: 1000px;
        margin: 0 auto;
    }

    .mortgage-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 2rem;
        margin-top: 2rem;
    }

    @media (min-width: 1024px) {
        .mortgage-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .mortgage-card {
        background-color: white;
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border-left: 4px solid var(--primary);
    }

    .mortgage-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .mortgage-icon {
        width: 3.5rem;
        height: 3.5rem;
        background-color: var(--gray-50);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        color: var(--primary);
        font-size: 1.5rem;
    }

    .mortgage-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--gray-900);
    }

    .mortgage-description {
        color: var(--gray-600);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .mortgage-features {
        list-style: none;
        padding-left: 0;
    }

    .mortgage-features li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        color: var(--gray-700);
    }

    .mortgage-features li i {
        color: var(--secondary);
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

    @media (min-width: 1024px) {
        .process-steps {
            grid-template-columns: repeat(4, 1fr);
        }
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

    @media (min-width: 1024px) {
        .testimonials-grid {
            grid-template-columns: repeat(2, 1fr);
        }
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

    /* FAQ Section */
    .faq-section {
        padding: 4rem 0;
        background-color: white;
    }

    .faq-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .faq-item {
        border-bottom: 1px solid var(--gray-200);
        padding: 1.5rem 0;
    }

    .faq-item:last-child {
        border-bottom: none;
    }

    .faq-question {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        padding: 0.5rem 0;
    }

    .faq-answer {
        color: var(--gray-700);
        line-height: 1.6;
        margin-top: 1rem;
        display: none;
    }

    .faq-answer.active {
        display: block;
    }

    .faq-icon {
        transition: transform 0.3s ease;
    }

    .faq-item.active .faq-icon {
        transform: rotate(180deg);
    }

    /* Recommendation Info Banner */
    .recommendation-info {
        background: linear-gradient(135deg, var(--mexico-blue), var(--primary));
        color: white;
        border-radius: var(--radius-lg);
        padding: 2rem;
        margin: 2rem 0;
    }

    .recommendation-info-content {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    .recommendation-info-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .recommendation-info-text {
        font-size: 1.125rem;
        margin-bottom: 1.5rem;
        opacity: 0.9;
    }

    .btn-info {
        background-color: white;
        color: var(--primary);
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        display: inline-block;
        transition: var(--transition);
        text-decoration: none;
    }

    .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, var(--mexico-blue), var(--primary));
        color: white;
        padding: 4rem 0;
        text-align: center;
    }

    .cta-content {
        max-width: 700px;
        margin: 0 auto;
    }

    .cta-title {
        font-size: 2.25rem;
        margin-bottom: 1.5rem;
    }

    .cta-subtitle {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .cta-buttons {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-cta {
        background-color: white;
        color: var(--primary);
        font-weight: 600;
        padding: 0.75rem 2rem;
        border-radius: var(--radius-md);
        transition: var(--transition);
        min-width: 220px;
        text-decoration: none;
        display: inline-block;
    }

    .btn-cta:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .btn-cta-secondary {
        background-color: transparent;
        color: white;
        border: 2px solid white;
        min-width: 220px;
    }

    .btn-cta-secondary:hover {
        background-color: rgba(255, 255, 255, 0.1);
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
    }

    @media (min-width: 1024px) {
        .container {
            padding: 0 2rem;
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
            <h1 class="hero-title">Encuentra el Crédito Hipotecario Perfecto para Ti</h1>
            <p class="hero-subtitle">Compara tasas de interés, calcula pagos mensuales y encuentra el mejor crédito hipotecario para tu nueva casa en México.</p>

            <!-- Mortgage Calculator -->
            <div class="mortgage-calculator">
                <div class="calculator-tabs">
                    <div class="calculator-tab active" data-tab="payment">Calculadora de Pago</div>
                    <div class="calculator-tab" data-tab="affordability">Asequibilidad</div>
                </div>

                <form class="calculator-form">
                    <div class="form-group">
                        <label for="property-price" class="form-label">Precio de la Propiedad</label>
                        <input type="text" id="property-price" class="form-input" placeholder="$ 2,500,000 MXN" value="$ 2,500,000 MXN">
                    </div>

                    <div class="form-group">
                        <label for="down-payment" class="form-label">Enganche (20%)</label>
                        <input type="text" id="down-payment" class="form-input" placeholder="$ 500,000 MXN" value="$ 500,000 MXN">
                    </div>

                    <div class="form-group">
                        <label for="loan-term" class="form-label">Plazo del Crédito</label>
                        <select id="loan-term" class="form-select">
                            <option>20 años</option>
                            <option>15 años</option>
                            <option>25 años</option>
                            <option>30 años</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="interest-rate" class="form-label">Tasa de Interés (8.5%)</label>
                        <input type="range" id="interest-rate" class="form-range" min="5" max="12" step="0.1" value="8.5">
                        <div class="range-values">
                            <span>5%</span>
                            <span>12%</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="property-tax" class="form-label">Impuesto Predial Anual</label>
                        <input type="text" id="property-tax" class="form-input" placeholder="$ 15,000 MXN" value="$ 15,000 MXN">
                    </div>

                    <div class="form-group">
                        <label for="insurance" class="form-label">Seguro de Vivienda</label>
                        <input type="text" id="insurance" class="form-input" placeholder="$ 3,000 MXN" value="$ 3,000 MXN">
                    </div>

                    <div class="form-group" style="grid-column: 1/-1;">
                        <button type="button" class="btn-calculate">Calcular Pago Mensual</button>
                    </div>
                </form>

                <div class="results-section">
                    <div class="results-grid">
                        <div class="result-card">
                            <div class="result-value">$ 19,580 MXN</div>
                            <div class="result-label">Pago Mensual Total</div>
                        </div>
                        <div class="result-card">
                            <div class="result-value">$ 17,250 MXN</div>
                            <div class="result-label">Pago Principal e Interés</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mortgage Types Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tipos de Créditos Hipotecarios en México</h2>
            <p class="section-subtitle">Encuentra el crédito que mejor se adapta a tus necesidades y perfil financiero</p>
        </div>

        <div class="mortgage-types">
            <div class="mortgage-grid">
                <!-- Mortgage Type 1 - INFONAVIT -->
                <div class="mortgage-card">
                    <div class="mortgage-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="mortgage-title">Crédito INFONAVIT</h3>
                    <p class="mortgage-description">El crédito hipotecario más popular en México para trabajadores registrados en el IMSS.</p>
                    <ul class="mortgage-features">
                        <li><i class="fas fa-check-circle"></i> Tasa de interés fija del 10.8% anual</li>
                        <li><i class="fas fa-check-circle"></i> Plazos de hasta 30 años</li>
                        <li><i class="fas fa-check-circle"></i> Monto máximo de 412,000 puntos</li>
                        <li><i class="fas fa-check-circle"></i> Requiere estar afiliado al IMSS</li>
                        <li><i class="fas fa-check-circle"></i> Pago en UMAS o pesos fijos</li>
                    </ul>
                    <button class="btn btn-primary" style="width: 100%;">Más información</button>
                </div>

                <!-- Mortgage Type 2 - FOVISSSTE -->
                <div class="mortgage-card">
                    <div class="mortgage-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <h3 class="mortgage-title">Crédito FOVISSSTE</h3>
                    <p class="mortgage-description">Crédito hipotecario para trabajadores del sector público y fuerzas armadas.</p>
                    <ul class="mortgage-features">
                        <li><i class="fas fa-check-circle"></i> Tasa de interés fija del 10.5% anual</li>
                        <li><i class="fas fa-check-circle"></i> Plazos de hasta 30 años</li>
                        <li><i class="fas fa-check-circle"></i> Monto máximo de 8,240,000 UMAS</li>
                        <li><i class="fas fa-check-circle"></i> Requiere estar afiliado al ISSSTE</li>
                        <li><i class="fas fa-check-circle"></i> Sistema de amortización progresiva</li>
                    </ul>
                    <button class="btn btn-primary" style="width: 100%;">Más información</button>
                </div>

                <!-- Mortgage Type 3 - Bancario -->
                <div class="mortgage-card">
                    <div class="mortgage-icon">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <h3 class="mortgage-title">Crédito Bancario</h3>
                    <p class="mortgage-description">Créditos hipotecarios ofrecidos por bancos privados con mayor flexibilidad.</p>
                    <ul class="mortgage-features">
                        <li><i class="fas fa-check-circle"></i> Tasas desde 8.5% anual</li>
                        <li><i class="fas fa-check-circle"></i> Plazos de hasta 25 años</li>
                        <li><i class="fas fa-check-circle"></i> Monto hasta 90% del valor de la propiedad</li>
                        <li><i class="fas fa-check-circle"></i> Evaluación de capacidad de pago</li>
                        <li><i class="fas fa-check-circle"></i> Seguro de vida incluido</li>
                    </ul>
                    <button class="btn btn-primary" style="width: 100%;">Más información</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="section section-featured">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Cómo Obtener un Crédito Hipotecario en México</h2>
            <p class="section-subtitle">Sigue estos pasos para obtener tu crédito hipotecario sin complicaciones</p>
        </div>

        <div class="process-steps">
            <!-- Step 1 -->
            <div class="process-step">
                <div class="step-number">1</div>
                <h3 class="step-title">Pre-calificación</h3>
                <p class="step-description">Compara diferentes opciones de crédito y determina cuánto puedes calificar basado en tus ingresos, deudas y historial crediticio.</p>
            </div>

            <!-- Step 2 -->
            <div class="process-step">
                <div class="step-number">2</div>
                <h3 class="step-title">Pre-aprobación</h3>
                <p class="step-description">Obtén una pre-aprobación de crédito que te permitirá buscar propiedades con confianza y hacer ofertas más competitivas.</p>
            </div>

            <!-- Step 3 -->
            <div class="process-step">
                <div class="step-number">3</div>
                <h3 class="step-title">Evaluación de Propiedad</h3>
                <p class="step-description">El banco evaluará la propiedad que deseas comprar para determinar su valor y elegibilidad para el crédito.</p>
            </div>

            <!-- Step 4 -->
            <div class="process-step">
                <div class="step-number">4</div>
                <h3 class="step-title">Cierre del Crédito</h3>
                <p class="step-description">Completa el proceso con la firma de documentos y el registro ante el notario público para finalizar tu compra.</p>
            </div>
        </div>
    </div>
</section>

<!-- Recommendation Info Banner -->
<div class="container">
    <div class="recommendation-info">
        <div class="recommendation-info-content">
            <h2 class="recommendation-info-title">¿Cómo funcionan estas recomendaciones?</h2>
            <p class="recommendation-info-text">Las recomendaciones se basan en tu ubicación y actividad de búsqueda, como las propiedades que has visto y guardado y los filtros que has utilizado. Usamos esta información para mostrarte opciones de crédito similares, para que no te pierdas ninguna oportunidad.</p>
            <a href="#" class="btn-info">Más información sobre recomendaciones</a>
        </div>
    </div>
</div>

<!-- First-Time Homebuyers Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Para Compradores Primeriza Vez</h2>
            <p class="section-subtitle">Recursos y consejos para obtener tu primera vivienda en México</p>
        </div>

        <div class="mortgage-grid">
            <!-- Resource 1 -->
            <div class="mortgage-card">
                <div class="mortgage-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <h3 class="mortgage-title">Programas de Apoyo para Primeriza Vez</h3>
                <p class="mortgage-description">En México existen varios programas gubernamentales que pueden ayudarte a obtener tu primera vivienda:</p>
                <ul class="mortgage-features">
                    <li><i class="fas fa-check-circle"></i> INFONAVIT: Apoyo para trabajadores del IMSS</li>
                    <li><i class="fas fa-check-circle"></i> FOVISSSTE: Para empleados del gobierno</li>
                    <li><i class="fas fa-check-circle"></i> Programa Nacional de Vivienda</li>
                    <li><i class="fas fa-check-circle"></i> Subsidios estatales y municipales</li>
                    <li><i class="fas fa-check-circle"></i> Apoyo para enganche (hasta 20%)</li>
                </ul>
            </div>

            <!-- Resource 2 -->
            <div class="mortgage-card">
                <div class="mortgage-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h3 class="mortgage-title">Requisitos Básicos</h3>
                <p class="mortgage-description">Lo que necesitas para calificar para un crédito hipotecario en México:</p>
                <ul class="mortgage-features">
                    <li><i class="fas fa-check-circle"></i> Edad entre 21 y 65 años</li>
                    <li><i class="fas fa-check-circle"></i> Comprobante de ingresos mensuales</li>
                    <li><i class="fas fa-check-circle"></i> Historial crediticio limpio</li>
                    <li><i class="fas fa-check-circle"></i> Antigüedad laboral mínima de 6 meses</li>
                    <li><i class="fas fa-check-circle"></i> Enganche mínimo del 10-20%</li>
                </ul>
            </div>

            <!-- Resource 3 -->
            <div class="mortgage-card">
                <div class="mortgage-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <h3 class="mortgage-title">Cómo Calcular tu Capacidad de Pago</h3>
                <p class="mortgage-description">En México, los bancos evalúan tu capacidad de pago usando la relación de deuda-ingreso (DTI):</p>
                <ul class="mortgage-features">
                    <li><i class="fas fa-check-circle"></i> Tu pago mensual no debe exceder el 35% de tus ingresos</li>
                    <li><i class="fas fa-check-circle"></i> Debes tener un historial crediticio positivo</li>
                    <li><i class="fas fa-check-circle"></i> Los pagos totales de deuda no deben exceder el 40% de tus ingresos</li>
                    <li><i class="fas fa-check-circle"></i> Los bancos consideran ingresos estables y comprobables</li>
                    <li><i class="fas fa-check-circle"></i> Los trabajadores independientes necesitan 2 años de declaraciones</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Lo que Nuestros Clientes Dicen</h2>
            <p class="section-subtitle">Miles de mexicanos han obtenido su crédito hipotecario con nuestra ayuda</p>
        </div>

        <div class="testimonials-grid">
            <!-- Testimonial 1 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Gracias a Tierras.mx pude comparar diferentes opciones de crédito y elegir el mejor para mi situación. El proceso fue transparente y sin complicaciones. Ahora soy dueño de mi primera casa en Monterrey."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Carlos M.">
                    </div>
                    <div class="author-info">
                        <span class="author-name">Carlos M.</span>
                        <span class="author-location">Monterrey, Nuevo León</span>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Como trabajador del gobierno, no sabía que opciones tenía para obtener un crédito FOVISSSTE. La guía paso a paso y el asesoramiento personalizado me ayudaron a entender el proceso y obtener mi crédito sin contratiempos."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="María G.">
                    </div>
                    <div class="author-info">
                        <span class="author-name">María G.</span>
                        <span class="author-location">CDMX</span>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"Como primeriza vez, estaba abrumado con los requisitos y opciones. Tierras.mx me guió en cada paso, desde la pre-calificación hasta el cierre. Ahora disfruto de mi nuevo hogar en Guadalajara sin estrés."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Ana L.">
                    </div>
                    <div class="author-info">
                        <span class="author-name">Ana L.</span>
                        <span class="author-location">Guadalajara, Jalisco</span>
                    </div>
                </div>
            </div>

            <!-- Testimonial 4 -->
            <div class="testimonial-card">
                <p class="testimonial-content">"La calculadora de créditos me ayudó a entender claramente mis opciones. Comparé diferentes escenarios y elegí el que mejor se ajustaba a mi presupuesto. Recomendaría Tierras.mx a cualquier persona que busque su primera casa en México."</p>
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

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Preguntas Frecuentes</h2>
            <p class="section-subtitle">Respuestas a las preguntas más comunes sobre créditos hipotecarios en México</p>
        </div>

        <div class="faq-container">
            <!-- FAQ 1 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span>¿Cuál es el enganche mínimo requerido para un crédito hipotecario en México?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>El enganche mínimo varía según el tipo de crédito:</p>
                    <ul style="list-style: disc; padding-left: 1.5rem; margin-top: 0.5rem;">
                        <li>INFONAVIT: 10% del valor de la propiedad</li>
                        <li>FOVISSSTE: 10% del valor de la propiedad</li>
                        <li>Créditos bancarios: 10-20% del valor de la propiedad</li>
                    </ul>
                    <p>Existen programas de apoyo gubernamental que pueden ayudarte a cubrir parte del enganche si calificas.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span>¿Cuál es el plazo máximo para un crédito hipotecario en México?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>Los plazos máximos varían según el tipo de crédito:</p>
                    <ul style="list-style: disc; padding-left: 1.5rem; margin-top: 0.5rem;">
                        <li>INFONAVIT: Hasta 30 años</li>
                        <li>FOVISSSTE: Hasta 30 años</li>
                        <li>Créditos bancarios: Hasta 25 años</li>
                    </ul>
                    <p>Es importante considerar que a mayor plazo, mayor será el interés total pagado, aunque los pagos mensuales serán más bajos.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span>¿Qué documentos necesito para solicitar un crédito hipotecario?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>Los documentos básicos requeridos incluyen:</p>
                    <ul style="list-style: disc; padding-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Identificación oficial (INE, pasaporte)</li>
                        <li>Comprobante de domicilio</li>
                        <li>Últimos 3 meses de recibos de nómina o declaraciones fiscales (para trabajadores independientes)</li>
                        <li>Estado de cuenta bancario</li>
                        <li>Carta de pre-aprobación de INFONAVIT o FOVISSSTE (si aplica)</li>
                    </ul>
                    <p>Los requisitos específicos pueden variar según el tipo de crédito y la institución financiera.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span>¿Cómo afecta mi historial crediticio a mi solicitud de crédito hipotecario?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>Tu historial crediticio es crucial para obtener un crédito hipotecario en México:</p>
                    <ul style="list-style: disc; padding-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Un buen historial puede ayudarte a obtener tasas de interés más bajas</li>
                        <li>Las instituciones revisarán tu Buró de Crédito</li>
                        <li>Deudas pendientes pueden afectar tu capacidad de pago</li>
                        <li>Incumplimientos recientes pueden resultar en una negativa</li>
                    </ul>
                    <p>Se recomienda revisar tu Buró de Crédito con anticipación y corregir cualquier error antes de solicitar un crédito.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="faq-item">
                <div class="faq-question">
                    <span>¿Qué costos adicionales debo considerar al obtener un crédito hipotecario?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>Además del pago mensual, debes considerar estos costos:</p>
                    <ul style="list-style: disc; padding-left: 1.5rem; margin-top: 0.5rem;">
                        <li>Comisión por apertura (1-3% del monto)</li>
                        <li>Gastos notariales (2-5% del valor de la propiedad)</li>
                        <li>Impuesto sobre adquisición de inmuebles (varía por estado)</li>
                        <li>Seguro de daños y vida</li>
                        <li>Mantenimiento y impuesto predial</li>
                    </ul>
                    <p>Es importante plan