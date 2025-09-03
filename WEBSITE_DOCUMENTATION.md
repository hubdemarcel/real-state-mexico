# Tierras.mx - Real Estate Platform Documentation

## Overview

**Tierras.mx** is a comprehensive real estate marketplace platform designed specifically for the Mexican real estate market. The platform connects buyers, sellers, and real estate agents, providing tools for property search, listing management, market intelligence, and transaction facilitation.

### Mission

To transform how investors, developers, brokers, and financial institutions analyze the Mexican real estate market by centralizing fragmented data from public, private, and institutional sources.

### Key Features

- 🏠 **Property Listings**: Comprehensive property database with advanced search and filtering
- 👥 **User Management**: Multi-role system (Buyers, Sellers, Agents, Administrators)
- 📊 **Market Intelligence**: Basic and advanced analytics for market insights
- 🔔 **Real-time Notifications**: Instant updates on property matches and market changes
- 🗺️ **Interactive Maps**: Geographic visualization of properties across Mexico
- 💬 **Communication Tools**: Direct messaging between users and agents
- 📱 **Responsive Design**: Mobile-first approach for all devices

## Technology Stack

### Backend

- **Language**: PHP 7.4+
- **Database**: MySQL (Hostinger hosting)
- **Architecture**: MVC pattern with procedural PHP
- **Connection Pooling**: Custom implementation for performance optimization

### Frontend

- **HTML5/CSS3**: Semantic markup and responsive design
- **JavaScript**: Vanilla JS with modern ES6+ features
- **Libraries**:
  - Font Awesome for icons
  - Leaflet for interactive maps
  - Chart.js for data visualization

### Infrastructure

- **Hosting**: Hostinger shared hosting
- **Development Server**: XAMPP for local development
- **Version Control**: Git
- **Deployment**: Manual FTP deployment

## Project Structure

```
real-state-mexico/
├── 📁 Root Files
│   ├── index.php                 # Main homepage
│   ├── config.php                # Database configuration
│   ├── header.php                # Site header/navigation
│   ├── footer.php                # Site footer
│   ├── .htaccess                 # Apache configuration
│   └── .env                      # Environment variables
├── 📁 User Authentication
│   ├── login.php                 # User login processing
│   ├── register.php              # User registration
│   ├── logout.php                # User logout
│   └── login.html                # Login form
├── 📁 Property Management
│   ├── get_properties.php        # API endpoint for property data
│   ├── add_property.php          # Add new property
│   ├── edit_property.php         # Edit existing property
│   ├── delete_property.php       # Delete property
│   └── property-card.php         # Property display component
├── 📁 User Dashboards
│   ├── buyer_dashboard.php       # Buyer dashboard
│   ├── seller_dashboard.php      # Seller dashboard
│   ├── agent_dashboard.php       # Agent dashboard
│   └── user_dashboard.php        # Generic user dashboard
├── 📁 Agent Features
│   ├── agent_public_profile.php  # Public agent profile
│   ├── edit_agent_profile.php    # Agent profile editing
│   ├── agent_intelligence.php    # Advanced analytics (premium)
│   └── basic_intelligence.php    # Basic market intelligence
├── 📁 Communication
│   ├── send_message.php          # Send messages
│   ├── contact_agent.php         # Contact agent forms
│   └── get_notifications.php     # Notification API
├── 📁 Static Pages
│   ├── comprar.php               # Buy properties page
│   ├── venta.php                 # Sell properties page
│   ├── renta.php                 # Rent properties page
│   ├── creditos.php              # Financing page
│   ├── encuentraunagente.php     # Find agent page
│   └── sobre_nosotros.php        # About us page
├── 📁 Assets
│   └── Tierrasmx/
│       ├── css/
│       │   ├── main.css          # Main stylesheet
│       │   ├── responsive.css    # Mobile styles
│       │   └── *.css             # Component styles
│       ├── js/
│       │   ├── main.js           # Main JavaScript
│       │   ├── search.js         # Search functionality
│       │   ├── notifications.js  # Notification system
│       │   └── *.js              # Feature scripts
│       ├── data/
│       │   ├── properties-mx.json # Property data (fallback)
│       │   ├── locations-mx.json  # Location data
│       │   └── translations-es.json # UI translations
│       └── images/               # Static images
└── 📁 Database
    ├── create_database.php       # Database setup
    ├── create_main_tables.php    # Core tables
    ├── create_agents_table.php   # Agent-specific tables
    └── *.php                     # Database utilities
```

## Database Schema

### Core Tables

#### users

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('buyer', 'seller', 'agent', 'admin') NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone_number VARCHAR(20),
    bio TEXT,
    profile_picture_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### properties

```sql
CREATE TABLE properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agent_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(15,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    property_type VARCHAR(50) NOT NULL,
    bedrooms INT,
    bathrooms DECIMAL(3,1),
    construction_size DECIMAL(10,2),
    land_size DECIMAL(10,2),
    amenities JSON,
    image_url VARCHAR(500),
    status ENUM('active', 'sold', 'rented', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES users(id)
);
```

#### agents

```sql
CREATE TABLE agents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone_number VARCHAR(20),
    bio TEXT,
    company VARCHAR(100),
    license_number VARCHAR(50),
    specialties VARCHAR(255),
    experience_years INT,
    location VARCHAR(100),
    website VARCHAR(255),
    profile_picture_url VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    properties_sold INT DEFAULT 0,
    total_sales DECIMAL(15,2) DEFAULT 0.00,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Additional Tables

#### user_saved_properties

```sql
CREATE TABLE user_saved_properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    property_id INT,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (property_id) REFERENCES properties(id),
    UNIQUE KEY unique_save (user_id, property_id)
);
```

#### user_favorites

```sql
CREATE TABLE user_favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    property_id INT,
    favorited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (property_id) REFERENCES properties(id),
    UNIQUE KEY unique_favorite (user_id, property_id)
);
```

#### user_search_history

```sql
CREATE TABLE user_search_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    search_query TEXT,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### user_alerts

```sql
CREATE TABLE user_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    alert_type VARCHAR(50) NOT NULL,
    criteria JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### user_messages

```sql
CREATE TABLE user_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT,
    receiver_id INT,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);
```

#### notifications

```sql
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## User Roles & Permissions

### 1. Buyers (Compradores)

**Permissions:**

- Browse and search properties
- Save favorite properties
- Create property alerts
- Contact agents
- Access basic market intelligence
- View property details

**Dashboard Features:**

- Saved properties list
- Favorite properties
- Search history
- Active alerts
- Message inbox
- Basic market data

### 2. Sellers (Vendedores)

**Permissions:**

- All buyer permissions
- List properties for sale
- Manage their property listings
- Receive inquiries from buyers
- Access seller resources and guides

**Dashboard Features:**

- Property management
- Inquiry management
- Sales performance
- Seller resources

### 3. Agents (Agentes)

**Permissions:**

- All seller permissions
- Advanced property listing management
- Client relationship management
- Access to premium market intelligence
- Lead generation tools
- Analytics and reporting

**Dashboard Features:**

- Property portfolio management
- Client lead management
- Performance analytics
- Market intelligence (basic + premium)
- Communication tools
- Profile management

### 4. Administrators (Administradores)

**Permissions:**

- Full system access
- User management
- Content management
- System configuration
- Analytics access
- Database management

## Key Features Documentation

### Property Search & Filtering

The platform provides comprehensive search capabilities through the `get_properties.php` API endpoint.

**Search Parameters:**

- `location`: City, state, or neighborhood
- `property_type`: casa, departamento, terreno, local-comercial
- `min_price` / `max_price`: Price range in MXN
- `bedrooms`: Minimum number of bedrooms
- `bathrooms`: Minimum number of bathrooms
- `amenities`: Comma-separated list of amenities

**Example API Call:**

```
GET /get_properties.php?location=CDMX&property_type=casa&min_price=2000000&max_price=5000000&bedrooms=2
```

**Response Format:**

```json
{
  "properties": [
    {
      "id": 1,
      "title": "Casa moderna en Polanco",
      "price": 5500000,
      "location": "Polanco, Miguel Hidalgo, CDMX",
      "bedrooms": 3,
      "bathrooms": 2.5,
      "image_url": "https://...",
      "property_type": "casa"
    }
  ],
  "total": 150,
  "page": 1,
  "limit": 12,
  "total_pages": 13
}
```

### Real-time Features

#### Notifications System

- **Real-time Updates**: WebSocket-based notifications for new properties
- **Alert System**: Custom alerts for price/location changes
- **Message Notifications**: Instant messaging between users
- **Property Updates**: Notifications for saved/favorite properties

#### Live Data Updates

- Property availability changes
- Price updates
- New property listings
- Market data refreshes

### Market Intelligence

#### Basic Intelligence (Free)

- Market trends overview
- Basic price analysis
- Location insights
- General market indicators

#### Advanced Intelligence (Premium)

- Predictive analytics
- Price forecasting
- Demand analysis
- Investment recommendations
- Comparative market analysis
- Custom reports

### Communication System

#### Direct Messaging

- User-to-user messaging
- Agent-client communication
- Inquiry management
- Message history

#### Contact Forms

- Property inquiry forms
- Agent contact forms
- General contact forms

## API Endpoints

### Property Endpoints

#### GET /get_properties.php

Retrieve properties with filtering and pagination.

**Parameters:**

- `page` (int): Page number (default: 1)
- `limit` (int): Items per page (default: 12)
- `location` (string): Location search
- `property_type` (string): Property type filter
- `min_price` (float): Minimum price
- `max_price` (float): Maximum price
- `bedrooms` (int): Minimum bedrooms
- `bathrooms` (int): Minimum bathrooms
- `amenities` (string): Comma-separated amenities

#### POST /add_property.php

Add a new property listing (agent/seller only).

#### PUT /edit_property.php

Edit an existing property listing.

#### DELETE /delete_property.php

Delete a property listing.

### User Management Endpoints

#### POST /login.php

User authentication.

#### POST /register.php

User registration.

#### POST /logout.php

User logout.

### Communication Endpoints

#### POST /send_message.php

Send a message to another user.

#### GET /get_notifications.php

Retrieve user notifications.

#### POST /mark_notification_read.php

Mark notification as read.

### Dashboard Endpoints

#### GET /buyer_dashboard.php

Buyer dashboard data.

#### GET /seller_dashboard.php

Seller dashboard data.

#### GET /agent_dashboard.php

Agent dashboard data.

## Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- Composer (optional, for dependency management)

### Local Development Setup

1. **Clone the repository:**

   ```bash
   git clone <repository-url>
   cd real-state-mexico
   ```

2. **Database Setup:**

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

3. **Configuration:**

   - Copy `.env.example` to `.env`
   - Update database credentials in `config.php`
   - Configure environment variables

4. **Start Development Server:**

   ```bash
   php -S localhost:8000
   ```

5. **Access the Application:**
   Open `http://localhost:8000` in your browser.

### Production Deployment

1. **Upload files to hosting provider**
2. **Configure database on hosting provider**
3. **Update configuration files with production credentials**
4. **Set up SSL certificate**
5. **Configure domain and DNS**

## Security Features

### Authentication & Authorization

- Password hashing with bcrypt
- Session management
- Role-based access control (RBAC)
- CSRF protection
- Input validation and sanitization

### Data Protection

- SQL injection prevention
- XSS protection
- Data encryption for sensitive information
- GDPR compliance for Mexican data laws

### Connection Security

- Connection pooling for performance
- Retry logic for connection failures
- Connection timeout handling
- Secure credential storage

## Performance Optimization

### Database Optimization

- Indexed queries for fast property searches
- Connection pooling to reduce overhead
- Query result caching
- Database query optimization

### Frontend Optimization

- Lazy loading of images
- Minified CSS and JavaScript
- Responsive design for mobile devices
- Progressive enhancement

### Caching Strategy

- Browser caching for static assets
- Database query result caching
- CDN integration for global delivery

## Maintenance & Monitoring

### Regular Tasks

- Database backup automation
- Log file monitoring
- Performance monitoring
- Security updates

### Monitoring Tools

- Error logging
- Performance metrics
- User activity tracking
- Database health checks

## Future Enhancements

### Planned Features

- Mobile application (React Native)
- Advanced AI-powered recommendations
- Virtual property tours (VR/AR)
- Blockchain-based transactions
- Integration with Mexican government APIs
- Multi-language support (English/Spanish)

### Technical Improvements

- Migration to modern PHP framework (Laravel/Symfony)
- Microservices architecture
- GraphQL API implementation
- Advanced caching (Redis/Memcached)
- Containerization (Docker)

## Support & Documentation

### User Documentation

- User guides for each role
- FAQ section
- Video tutorials
- Help center

### Technical Documentation

- API documentation
- Database schema documentation
- Code documentation
- Deployment guides

### Support Channels

- Email support
- Live chat
- Knowledge base
- Community forum

## Contributing

### Development Guidelines

1. Follow PHP PSR standards
2. Use meaningful commit messages
3. Write comprehensive tests
4. Document new features
5. Follow security best practices

### Code Review Process

1. Create feature branch
2. Implement changes
3. Write/update tests
4. Submit pull request
5. Code review and approval
6. Merge to main branch

## License

This project is proprietary software owned by Tierras.mx. All rights reserved.

## Contact Information

- **Website**: https://tierras.mx
- **Email**: info@tierras.mx
- **Phone**: +52 333 101 0164
- **Address**: Guadalajara, Jalisco, México

---

_This documentation is maintained by the Tierras.mx development team and is updated regularly to reflect the latest features and changes to the platform._
