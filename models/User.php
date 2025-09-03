<?php
/**
 * User Model
 *
 * Handles all user-related database operations
 */
class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Get user by ID
     */
    public function getById($userId) {
        $sql = "SELECT id, username, email, user_type, first_name, last_name,
                       phone_number, bio, profile_picture_url, created_at
                FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database prepare error: " . $this->conn->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    /**
     * Get user's saved properties
     */
    public function getSavedProperties($userId, $limit = null) {
        $sql = "SELECT p.* FROM properties p
                INNER JOIN user_saved_properties usp ON p.id = usp.property_id
                WHERE usp.user_id = ? ORDER BY usp.saved_at DESC";

        if ($limit) {
            $sql .= " LIMIT ?";
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $this->conn->error);
        }

        if ($limit) {
            $stmt->bind_param("ii", $userId, $limit);
        } else {
            $stmt->bind_param("i", $userId);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $properties = [];
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }

        $stmt->close();
        return $properties;
    }

    /**
     * Get user's favorite properties
     */
    public function getFavoriteProperties($userId, $limit = null) {
        $sql = "SELECT p.* FROM properties p
                INNER JOIN user_favorites uf ON p.id = uf.property_id
                WHERE uf.user_id = ? ORDER BY uf.favorited_at DESC";

        if ($limit) {
            $sql .= " LIMIT ?";
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $this->conn->error);
        }

        if ($limit) {
            $stmt->bind_param("ii", $userId, $limit);
        } else {
            $stmt->bind_param("i", $userId);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $properties = [];
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }

        $stmt->close();
        return $properties;
    }

    /**
     * Get user's search history
     */
    public function getSearchHistory($userId, $limit = 10) {
        $sql = "SELECT search_query, searched_at FROM user_search_history
                WHERE user_id = ? ORDER BY searched_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database prepare error: " . $this->conn->error);
        }

        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = [
                'query' => $row['search_query'] ?: 'Búsqueda general',
                'date' => date('d/m/Y H:i', strtotime($row['searched_at']))
            ];
        }

        $stmt->close();
        return $history;
    }

    /**
     * Get user's alerts
     */
    public function getAlerts($userId) {
        $sql = "SELECT alert_type, criteria, created_at FROM user_alerts
                WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database prepare error: " . $this->conn->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $criteria = json_decode($row['criteria'], true);
            $alerts[] = [
                'type' => $row['alert_type'],
                'criteria' => is_array($criteria) ? implode(', ', $criteria) : $row['criteria'],
                'date' => date('d/m/Y', strtotime($row['created_at']))
            ];
        }

        $stmt->close();
        return $alerts;
    }

    /**
     * Get user's messages
     */
    public function getMessages($userId, $limit = 10) {
        $sql = "SELECT um.*, u.username as sender_name FROM user_messages um
                INNER JOIN users u ON um.sender_id = u.id
                WHERE um.receiver_id = ? ORDER BY um.sent_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database prepare error: " . $this->conn->error);
        }

        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = [
                'sender' => $row['sender_name'],
                'subject' => $row['subject'] ?: 'Sin asunto',
                'preview' => substr($row['message'], 0, 100) . (strlen($row['message']) > 100 ? '...' : ''),
                'date' => date('d/m/Y H:i', strtotime($row['sent_at'])),
                'is_read' => $row['is_read']
            ];
        }

        $stmt->close();
        return $messages;
    }
}
?>