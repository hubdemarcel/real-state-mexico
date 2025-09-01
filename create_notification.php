<?php
/*
 * Create Notification Utility
 *
 * This script provides functions to create notifications for various events
 */

require_once 'config.php';

function createNotification($user_id, $type, $title, $message, $data = null) {
    global $conn;

    $data_json = $data ? json_encode($data) : null;

    $sql = "INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("issss", $user_id, $type, $title, $message, $data_json);

        if ($stmt->execute()) {
            return $stmt->insert_id;
        } else {
            error_log("Error creating notification: " . $stmt->error);
            return false;
        }
        $stmt->close();
    } else {
        error_log("Error preparing notification statement: " . $conn->error);
        return false;
    }
}

function createPropertyAlertNotification($user_id, $property_data) {
    $title = "Nueva propiedad disponible";
    $message = "Se ha encontrado una nueva propiedad que coincide con tus criterios de búsqueda: {$property_data['title']} en {$property_data['location']}";

    return createNotification($user_id, 'property_alert', $title, $message, $property_data);
}

function createMessageNotification($user_id, $sender_name, $subject) {
    $title = "Nuevo mensaje";
    $message = "{$sender_name} te ha enviado un mensaje: {$subject}";

    return createNotification($user_id, 'message', $title, $message, [
        'sender_name' => $sender_name,
        'subject' => $subject
    ]);
}

function createFavoriteNotification($user_id, $property_title) {
    $title = "Propiedad agregada a favoritos";
    $message = "Has agregado '{$property_title}' a tus propiedades favoritas";

    return createNotification($user_id, 'favorite', $title, $message, [
        'property_title' => $property_title
    ]);
}

function createSavedNotification($user_id, $property_title) {
    $title = "Propiedad guardada";
    $message = "Has guardado '{$property_title}' para revisarla más tarde";

    return createNotification($user_id, 'saved', $title, $message, [
        'property_title' => $property_title
    ]);
}

function createPriceChangeNotification($user_id, $property_title, $old_price, $new_price) {
    $title = "Cambio de precio";
    $message = "El precio de '{$property_title}' ha cambiado de $" . number_format($old_price) . " a $" . number_format($new_price) . " MXN";

    return createNotification($user_id, 'price_change', $title, $message, [
        'property_title' => $property_title,
        'old_price' => $old_price,
        'new_price' => $new_price
    ]);
}

// Example usage in other PHP files:
/*
// In save_property.php, after successful save:
include 'create_notification.php';
createSavedNotification($user_id, $property_title);

// In favorite_property.php, after successful favorite:
include 'create_notification.php';
createFavoriteNotification($user_id, $property_title);

// In send_message.php, after successful message:
include 'create_notification.php';
createMessageNotification($receiver_id, $sender_name, $subject);
*/
?>