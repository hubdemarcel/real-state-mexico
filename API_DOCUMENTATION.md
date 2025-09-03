# Tierras.mx API Documentation

## Overview

The Tierras.mx API provides programmatic access to property data, user management, and platform features. This RESTful API is designed to support web applications, mobile apps, and third-party integrations.

## Base URL

```
https://tierras.mx/
```

## Authentication

### Session-based Authentication

Most endpoints require user authentication through PHP sessions. Users must be logged in to access protected resources.

### Headers

```
Content-Type: application/json
Accept: application/json
```

## Endpoints

### Property Management

#### GET /get_properties.php

Retrieve properties with advanced filtering and pagination.

**Parameters:**

- `page` (integer, optional): Page number (default: 1)
- `limit` (integer, optional): Items per page (default: 12, max: 50)
- `location` (string, optional): Location search (city, state, neighborhood)
- `property_type` (string, optional): Property type filter
  - `casa` - House
  - `departamento` - Apartment
  - `terreno` - Land
  - `local-comercial` - Commercial property
- `min_price` (float, optional): Minimum price in MXN
- `max_price` (float, optional): Maximum price in MXN
- `bedrooms` (integer, optional): Minimum number of bedrooms
- `bathrooms` (integer, optional): Minimum number of bathrooms
- `amenities` (string, optional): Comma-separated amenities list

**Example Request:**

```bash
GET /get_properties.php?page=1&limit=12&location=CDMX&property_type=casa&min_price=2000000&max_price=5000000
```

**Response:**

```json
{
  "properties": [
    {
      "id": 1,
      "title": "Casa moderna en Polanco",
      "description": "Hermosa casa con acabados de lujo",
      "price": 5500000.0,
      "location": "Polanco, Miguel Hidalgo, CDMX",
      "property_type": "casa",
      "bedrooms": 3,
      "bathrooms": 2.5,
      "construction_size": 250.0,
      "land_size": 300.0,
      "amenities": "{\"seguridad\": true, \"estacionamiento\": true}",
      "image_url": "https://images.unsplash.com/photo-...",
      "status": "active",
      "created_at": "2024-01-15 10:30:00"
    }
  ],
  "total": 150,
  "page": 1,
  "limit": 12,
  "total_pages": 13
}
```

**Status Codes:**

- `200`: Success
- `400`: Bad request (invalid parameters)
- `500`: Server error

#### POST /add_property.php

Add a new property listing (Agents and Sellers only).

**Authentication:** Required (Agent/Seller)

**Request Body:**

```json
{
  "title": "Casa en venta - Guadalajara",
  "description": "Hermosa casa familiar en zona residencial",
  "price": 3200000.0,
  "location": "Providencia, Guadalajara, Jalisco",
  "property_type": "casa",
  "bedrooms": 3,
  "bathrooms": 2,
  "construction_size": 180.0,
  "land_size": 200.0,
  "amenities": {
    "seguridad": true,
    "jardin": true,
    "estacionamiento": true
  },
  "image_url": "https://example.com/image.jpg"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Property added successfully",
  "property_id": 123
}
```

#### PUT /edit_property.php

Update an existing property listing.

**Authentication:** Required (Property owner or Agent)

**Parameters:**

- `id` (integer, required): Property ID

**Request Body:** Same as add_property.php

**Response:**

```json
{
  "success": true,
  "message": "Property updated successfully"
}
```

#### DELETE /delete_property.php

Delete a property listing.

**Authentication:** Required (Property owner or Agent)

**Parameters:**

- `id` (integer, required): Property ID

**Response:**

```json
{
  "success": true,
  "message": "Property deleted successfully"
}
```

### User Management

#### POST /login.php

Authenticate a user.

**Request Body:**

```json
{
  "username": "john_doe",
  "password": "secure_password"
}
```

**Response (Success):**

```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 123,
    "username": "john_doe",
    "user_type": "buyer",
    "first_name": "John",
    "last_name": "Doe"
  },
  "redirect": "/buyer_dashboard.php"
}
```

**Response (Error):**

```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

#### POST /register.php

Register a new user account.

**Request Body:**

```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "secure_password",
  "confirm_password": "secure_password",
  "user_type": "buyer",
  "first_name": "John",
  "last_name": "Doe",
  "phone_number": "+523331234567"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Registration successful. Please login."
}
```

#### POST /logout.php

Log out the current user.

**Response:**

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Communication

#### POST /send_message.php

Send a message to another user.

**Authentication:** Required

**Request Body:**

```json
{
  "receiver_id": 456,
  "subject": "Inquiry about property #123",
  "message": "I'm interested in this property. Can we schedule a viewing?"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Message sent successfully",
  "message_id": 789
}
```

#### GET /get_notifications.php

Retrieve user notifications.

**Authentication:** Required

**Parameters:**

- `page` (integer, optional): Page number (default: 1)
- `limit` (integer, optional): Items per page (default: 20)

**Response:**

```json
{
  "notifications": [
    {
      "id": 1,
      "type": "property_match",
      "title": "New property matches your criteria",
      "message": "3 new properties found in Guadalajara",
      "is_read": false,
      "created_at": "2024-01-15 14:30:00"
    }
  ],
  "total": 5,
  "unread_count": 3
}
```

#### POST /mark_notification_read.php

Mark a notification as read.

**Authentication:** Required

**Request Body:**

```json
{
  "notification_id": 1
}
```

**Response:**

```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

### User Preferences

#### POST /save_property.php

Save a property to user's saved list.

**Authentication:** Required

**Request Body:**

```json
{
  "property_id": 123
}
```

**Response:**

```json
{
  "success": true,
  "message": "Property saved successfully"
}
```

#### POST /favorite_property.php

Add a property to user's favorites.

**Authentication:** Required

**Request Body:**

```json
{
  "property_id": 123
}
```

**Response:**

```json
{
  "success": true,
  "message": "Property added to favorites"
}
```

#### POST /create_alert.php

Create a property alert for the user.

**Authentication:** Required

**Request Body:**

```json
{
  "alert_type": "price",
  "criteria": {
    "location": "Guadalajara",
    "max_price": 3000000,
    "property_type": "casa",
    "bedrooms": 3
  }
}
```

**Response:**

```json
{
  "success": true,
  "message": "Alert created successfully",
  "alert_id": 456
}
```

### Market Intelligence

#### GET /basic_intelligence.php

Get basic market intelligence data.

**Authentication:** Optional (enhanced data for logged-in users)

**Response:**

```json
{
  "market_overview": {
    "total_properties": 15420,
    "average_price": 2850000,
    "price_trend": "+5.2%",
    "popular_locations": [
      { "name": "CDMX", "count": 5230 },
      { "name": "Guadalajara", "count": 3120 },
      { "name": "Monterrey", "count": 2890 }
    ]
  },
  "price_ranges": {
    "under_1m": 2340,
    "1m_to_3m": 6870,
    "3m_to_5m": 4230,
    "over_5m": 1980
  }
}
```

#### GET /agent_intelligence.php

Get advanced market intelligence (Premium feature).

**Authentication:** Required (Agent with premium subscription)

**Response:**

```json
{
  "predictive_analytics": {
    "price_forecast": {
      "cdmx": { "current": 3200000, "forecast_6m": 3450000, "growth": "+7.8%" },
      "guadalajara": {
        "current": 2800000,
        "forecast_6m": 2950000,
        "growth": "+5.4%"
      }
    },
    "demand_analysis": {
      "high_demand": ["Polanco", "Condesa", "Roma"],
      "emerging_areas": ["Santa Fe", "Bosques de las Lomas"]
    }
  },
  "investment_opportunities": [
    {
      "location": "Tijuana",
      "potential_return": "12.5%",
      "risk_level": "medium",
      "time_horizon": "2-3 years"
    }
  ]
}
```

## Error Handling

### Standard Error Response

```json
{
  "success": false,
  "message": "Error description",
  "error_code": "ERROR_CODE",
  "details": "Additional error information"
}
```

### Common Error Codes

- `AUTH_REQUIRED`: Authentication required
- `INVALID_CREDENTIALS`: Invalid login credentials
- `PERMISSION_DENIED`: Insufficient permissions
- `VALIDATION_ERROR`: Invalid input data
- `NOT_FOUND`: Resource not found
- `SERVER_ERROR`: Internal server error

## Rate Limiting

- **Authenticated users**: 1000 requests per hour
- **Anonymous users**: 100 requests per hour
- **Property search**: 500 requests per hour

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

## Data Formats

### Property Object

```json
{
  "id": "integer",
  "title": "string",
  "description": "string",
  "price": "float",
  "location": "string",
  "property_type": "string",
  "bedrooms": "integer|null",
  "bathrooms": "float|null",
  "construction_size": "float|null",
  "land_size": "float|null",
  "amenities": "object|null",
  "image_url": "string|null",
  "status": "string",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

### User Object

```json
{
  "id": "integer",
  "username": "string",
  "email": "string",
  "user_type": "string",
  "first_name": "string|null",
  "last_name": "string|null",
  "phone_number": "string|null",
  "bio": "string|null",
  "profile_picture_url": "string|null"
}
```

## SDKs and Libraries

### JavaScript SDK

```javascript
// Initialize the SDK
const tierrasAPI = new TierrasAPI({
  baseURL: 'https://tierras.mx',
  apiKey: 'your-api-key', // For future API key authentication
});

// Search properties
const properties = await tierrasAPI.getProperties({
  location: 'CDMX',
  property_type: 'casa',
  min_price: 2000000,
  max_price: 5000000,
});

// Add a property
const newProperty = await tierrasAPI.addProperty({
  title: 'Casa en venta',
  price: 3500000,
  location: 'Guadalajara',
});
```

### PHP SDK

```php
require_once 'TierrasAPI.php';

$api = new TierrasAPI([
    'base_url' => 'https://tierras.mx'
]);

// Search properties
$properties = $api->getProperties([
    'location' => 'CDMX',
    'property_type' => 'casa'
]);

// Handle response
if ($properties['success']) {
    foreach ($properties['properties'] as $property) {
        echo $property['title'] . ' - $' . number_format($property['price']) . PHP_EOL;
    }
}
```

## Webhooks

### Available Events

- `property.created` - New property listing
- `property.updated` - Property information updated
- `property.deleted` - Property listing removed
- `user.registered` - New user registration
- `message.sent` - New message sent

### Webhook Payload Example

```json
{
  "event": "property.created",
  "timestamp": "2024-01-15T10:30:00Z",
  "data": {
    "property": {
      "id": 123,
      "title": "Casa moderna en Polanco",
      "price": 5500000,
      "location": "Polanco, CDMX"
    }
  }
}
```

## Changelog

### Version 1.0.0 (Current)

- Initial API release
- Basic property CRUD operations
- User authentication
- Search and filtering
- Basic market intelligence

### Planned Features

- **v1.1.0**: Advanced search with geospatial queries
- **v1.2.0**: Real-time property updates via WebSocket
- **v2.0.0**: GraphQL API support
- **v2.1.0**: Mobile SDKs for iOS and Android

## Support

### Documentation

- [Complete API Reference](https://docs.tierras.mx/api)
- [Integration Guides](https://docs.tierras.mx/guides)
- [Code Examples](https://github.com/tierras-mx/api-examples)

### Getting Help

- **Email**: api@tierras.mx
- **Forum**: https://community.tierras.mx
- **Status Page**: https://status.tierras.mx

---

_For the latest API updates and changes, please refer to the [changelog](https://docs.tierras.mx/changelog) or subscribe to our [developer newsletter](https://newsletter.tierras.mx)._
