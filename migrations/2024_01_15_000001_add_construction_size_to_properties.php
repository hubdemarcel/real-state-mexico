<?php
/**
 * Migration: Add construction_size and land_size to properties table
 * Created: 2024-01-15
 */

function 2024_01_15_000001_add_construction_size_to_properties_up($conn) {
    $sql = "
        ALTER TABLE properties
        ADD COLUMN construction_size DECIMAL(10,2) NULL AFTER bathrooms,
        ADD COLUMN land_size DECIMAL(10,2) NULL AFTER construction_size;
    ";

    return $conn->query($sql);
}

function 2024_01_15_000001_add_construction_size_to_properties_down($conn) {
    $sql = "
        ALTER TABLE properties
        DROP COLUMN construction_size,
        DROP COLUMN land_size;
    ";

    return $conn->query($sql);
}
?>