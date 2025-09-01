<?php
/*
 * Database Indexing Recommendations for Performance:
 *
 * To ensure fast response times, create the following indexes on the properties table:
 *
 * CREATE INDEX idx_location ON properties (location);
 * CREATE INDEX idx_property_type ON properties (property_type);
 * CREATE INDEX idx_price ON properties (price);
 * CREATE INDEX idx_bedrooms ON properties (bedrooms);
 * CREATE INDEX idx_bathrooms ON properties (bathrooms);
 * CREATE INDEX idx_amenities ON properties (amenities);
 * CREATE INDEX idx_created_at ON properties (created_at);
 *
 * For composite indexes (if frequently queried together):
 * CREATE INDEX idx_location_type_price ON properties (location, property_type, price);
 */

// Include the database configuration file
require_once 'config.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$offset = ($page - 1) * $limit;

// Base SQL query for data
$sql = "SELECT * FROM properties";

// Base SQL query for total count
$count_sql = "SELECT COUNT(*) as total FROM properties";

// Array to hold the WHERE clauses
$where_clauses = array();
$params = array();
$types = "";

// Check for location parameter
if (!empty($_GET['location'])) {
    $location = "%" . $_GET['location'] . "%";
    $where_clauses[] = "location LIKE ?";
    $params[] = &$location;
    $types .= "s";
}

// Check for property_type parameter
if (!empty($_GET['property_type']) && $_GET['property_type'] != 'all') {
    $property_type = $_GET['property_type'];
    $where_clauses[] = "property_type = ?";
    $params[] = &$property_type;
    $types .= "s";
}

// Check for min_price and max_price parameters
if (!empty($_GET['min_price']) || !empty($_GET['max_price'])) {
    $min_price = !empty($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
    $max_price = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : PHP_FLOAT_MAX;

    if (!empty($_GET['min_price']) && !empty($_GET['max_price'])) {
        $where_clauses[] = "price BETWEEN ? AND ?";
        $params[] = &$min_price;
        $params[] = &$max_price;
        $types .= "dd";
    } elseif (!empty($_GET['min_price'])) {
        $where_clauses[] = "price >= ?";
        $params[] = &$min_price;
        $types .= "d";
    } elseif (!empty($_GET['max_price'])) {
        $where_clauses[] = "price <= ?";
        $params[] = &$max_price;
        $types .= "d";
    }
}

// Check for bedrooms parameter
if (!empty($_GET['bedrooms'])) {
    $bedrooms = (int)$_GET['bedrooms'];
    $where_clauses[] = "bedrooms >= ?";
    $params[] = &$bedrooms;
    $types .= "i";
}

// Check for bathrooms parameter
if (!empty($_GET['bathrooms'])) {
    $bathrooms = (int)$_GET['bathrooms'];
    $where_clauses[] = "bathrooms >= ?";
    $params[] = &$bathrooms;
    $types .= "i";
}

// Check for amenities parameter (comma-separated)
if (!empty($_GET['amenities'])) {
    $amenities = explode(',', $_GET['amenities']);
    $amenity_conditions = array();
    foreach ($amenities as $amenity) {
        $amenity_conditions[] = "amenities LIKE ?";
        $amenity_param = "%" . trim($amenity) . "%";
        $params[] = &$amenity_param;
        $types .= "s";
    }
    if (!empty($amenity_conditions)) {
        $where_clauses[] = "(" . implode(' OR ', $amenity_conditions) . ")";
    }
}

// If there are WHERE clauses, append them to the SQL queries
$where_string = !empty($where_clauses) ? " WHERE " . implode(' AND ', $where_clauses) : "";
$sql .= $where_string;
$count_sql .= $where_string;

// Order by creation date and add pagination
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = &$limit;
$params[] = &$offset;
$types .= "ii";

// Prepare and execute the count statement
$count_stmt = $conn->prepare($count_sql);
$total = 0;
if ($count_stmt) {
    if (!empty($where_clauses)) {
        $count_types = substr($types, 0, -2); // Remove 'ii' for count
        $count_params = array_slice($params, 0, -2); // Remove limit and offset
        if (!empty($count_params)) {
            $count_stmt->bind_param($count_types, ...$count_params);
        }
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    if ($count_result->num_rows > 0) {
        $total_row = $count_result->fetch_assoc();
        $total = $total_row['total'];
    }
    $count_stmt->close();
}

// Prepare and execute the main statement
$stmt = $conn->prepare($sql);
$properties = array();

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch all the results into an associative array
        while($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
    }

    $stmt->close();
} else {
    // Handle statement preparation error
    $properties = array("error" => "Failed to prepare statement");
}

// Close the database connection
$conn->close();

// Return properties with pagination info
$response = array(
    'properties' => $properties,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'total_pages' => ceil($total / $limit)
);

// Echo the response as JSON
echo json_encode($response);
?>