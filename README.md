# Tierras.mx - Real Estate Platform for Mexico

[![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)

**Tierras.mx** is a comprehensive real estate marketplace platform designed specifically for the Mexican real estate market. Connect buyers, sellers, and real estate agents with advanced search capabilities, market intelligence, and seamless communication tools.

## 🌟 Key Features

- 🏠 **Advanced Property Search** - Find properties with intelligent filtering
- 👥 **Multi-Role System** - Buyers, Sellers, Agents, and Administrators
- 📊 **Market Intelligence** - Basic and premium analytics for market insights
- 🔔 **Real-time Notifications** - Instant updates on property matches
- 🗺️ **Interactive Maps** - Geographic visualization across Mexico
- 💬 **Direct Communication** - Connect with agents and other users
- 📱 **Mobile Responsive** - Optimized for all devices

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd real-state-mexico
   ```

2. **Set up the database**
   ```bash
   # Create database
   mysql -u root -p
   CREATE DATABASE tierras_mexico;
   exit;

   # Run setup scripts
   php create_database.php
   php create_main_tables.php
   php create_agents_table.php
   ```

3. **Configure environment**
   - Copy `.env.example` to `.env`
   - Update database credentials in `config.php`
   - Set up your web server to point to the project root

4. **Start the development server**
   ```bash
   php -S localhost:8000
   ```

5. **Access the platform**
   Open `http://localhost:8000` in your browser

## 📖 Documentation

### For Users
- [User Guide](WEBSITE_DOCUMENTATION.md) - Complete platform documentation
- [API Reference](API_DOCUMENTATION.md) - Technical API documentation
- [System Architecture](system_architecture_diagram.md) - Technical overview

### For Developers
- [Installation Guide](WEBSITE_DOCUMENTATION.md#installation--setup)
- [Database Schema](WEBSITE_DOCUMENTATION.md#database-schema)
- [API Endpoints](API_DOCUMENTATION.md)
- [Contributing Guidelines](WEBSITE_DOCUMENTATION.md#contributing)

## 👥 User Roles

### 🛒 Buyers
- Browse and search properties
- Save favorite properties
- Create custom alerts
- Contact agents directly
- Access personalized recommendations

### 🏠 Sellers
- List properties for sale
- Manage property listings
- Receive buyer inquiries
- Access seller resources
- Track listing performance

### 👨‍💼 Agents
- Advanced property management
- Client relationship tools
- Premium market intelligence
- Lead generation and tracking
- Performance analytics

## 🛠️ Technology Stack

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL** - Primary database
- **PDO** - Database abstraction
- **Sessions** - User authentication

### Frontend
- **HTML5/CSS3** - Semantic markup and styling
- **JavaScript (ES6+)** - Interactive functionality
- **Font Awesome** - Icons and UI elements
- **Leaflet** - Interactive maps

### Infrastructure
- **Hostinger** - Production hosting
- **XAMPP** - Local development
- **Git** - Version control

## 🔧 Configuration

### Database Configuration (`config.php`)
```php
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASSWORD', 'your_database_password');
define('DB_HOST', 'your_database_host');
```

### Environment Variables (`.env`)
```env
APP_ENV=development
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tierras_mexico
DB_USERNAME=root
DB_PASSWORD=
```

## 📊 Key Features Overview

### Property Search & Discovery
- **Advanced Filtering**: Location, price, property type, amenities
- **Real-time Results**: Instant search with pagination
- **Saved Searches**: Save and reuse search criteria
- **Property Alerts**: Get notified of new matching properties

### Market Intelligence
- **Basic Analytics**: Market trends and price analysis
- **Premium Features**: Predictive analytics and investment insights
- **Location Intelligence**: Neighborhood and city-level data
- **Comparative Analysis**: Market comparisons and forecasting

### Communication System
- **Direct Messaging**: User-to-user communication
- **Agent Contact**: Easy agent outreach
- **Inquiry Management**: Track and manage property inquiries
- **Notification Center**: Centralized notification management

## 🌐 Live Demo

Visit [tierras.mx](https://tierras.mx) to explore the live platform.

## 📞 Support & Contact

- **Website**: [tierras.mx](https://tierras.mx)
- **Email**: info@tierras.mx
- **Phone**: +52 333 101 0164
- **Location**: Guadalajara, Jalisco, México

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](WEBSITE_DOCUMENTATION.md#contributing) for details.

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📄 License

This project is proprietary software owned by Tierras.mx. All rights reserved.

## 🙏 Acknowledgments

- Built for the Mexican real estate market
- Designed with local market expertise
- Optimized for Mexican user experience
- Compliant with Mexican data protection laws

---

**Tierras.mx** - Tu socio de confianza en bienes raíces en México 🇲🇽
