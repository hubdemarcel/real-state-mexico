<?php
require_once 'config.php';

/*
 * Import Properties from JSON to Database
 *
 * This script reads properties from the JSON file and inserts them into the database
 */

echo "Starting properties import...<br>";

// Load properties from JSON file
$json_file = 'Tierrasmx/assets/data/properties-mx.json';
if (!file_exists($json_file)) {
    die("Error: Properties JSON file not found at $json_file<br>");
}

$json_content = file_get_contents($json_file);
$properties = json_decode($json_content, true);

if ($properties === null) {
    die("Error: Failed to decode JSON file<br>");
}

echo "Found " . count($properties) . " properties in JSON file<br>";

// Prepare the insert statement
$insert_sql = "INSERT INTO properties (
    title, description, price, location, property_type, image_url,
    bedrooms, bathrooms, amenities
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($insert_sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error . "<br>");
}

$imported_count = 0;
$skipped_count = 0;

foreach ($properties as $prop) {
    // Check if property already exists (by title and location)
    $check_title = $prop['title_es'] ?? $prop['title'] ?? '';
    $check_city = $prop['location']['city'] ?? '';
    $location_pattern = '%' . $check_city . '%';

    $check_sql = "SELECT id FROM properties WHERE title = ? AND location LIKE ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $check_title, $location_pattern);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $skipped_count++;
        $check_stmt->close();
        continue;
    }
    $check_stmt->close();

    // Convert location object to string
    $location = '';
    if (isset($prop['location'])) {
        $location_parts = [];
        if (isset($prop['location']['neighborhood'])) $location_parts[] = $prop['location']['neighborhood'];
        if (isset($prop['location']['city'])) $location_parts[] = $prop['location']['city'];
        if (isset($prop['location']['state'])) $location_parts[] = $prop['location']['state'];
        $location = implode(', ', $location_parts);
    }

    // Convert features to amenities JSON
    $amenities = isset($prop['features']) ? json_encode($prop['features']) : null;

    // Prepare variables for binding
    $title = $prop['title_es'] ?? $prop['title'] ?? 'Sin título';
    $description = $prop['description'] ?? '';
    $price = $prop['price'] ?? 0;
    $property_type = $prop['property_type'] ?? 'casa';
    $image_url = $prop['image'] ?? '';
    $bedrooms = isset($prop['features']['bedrooms']) ? $prop['features']['bedrooms'] : null;
    $bathrooms = isset($prop['features']['bathrooms']) ? $prop['features']['bathrooms'] : null;

    $stmt->bind_param(
        "ssdsssdds",
        $title,
        $description,
        $price,
        $location,
        $property_type,
        $image_url,
        $bedrooms,
        $bathrooms,
        $amenities
    );

    if ($stmt->execute()) {
        $imported_count++;
    } else {
        echo "Error importing property '$title': " . $stmt->error . "<br>";
    }
}

$stmt->close();
$conn->close();

echo "<br>Import completed!<br>";
echo "Imported: $imported_count properties<br>";
echo "Skipped (already exist): $skipped_count properties<br>";
echo "Total processed: " . ($imported_count + $skipped_count) . " properties<br>";
?>