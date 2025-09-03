<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../models/User.php';

/**
 * Dashboard Controller
 *
 * Handles dashboard-related operations for different user types
 */
class DashboardController extends BaseController {
    private $userModel;

    public function __construct($conn = null) {
        parent::__construct($conn);
        $this->userModel = new User($this->conn);
    }

    /**
     * Show buyer dashboard
     */
    public function buyerDashboard() {
        try {
            $this->requireRole('buyer');

            $userId = $this->user['id'];

            // Get user profile
            $userProfile = $this->userModel->getById($userId);

            // Get dashboard data
            $savedProperties = $this->userModel->getSavedProperties($userId);
            $favoriteProperties = $this->userModel->getFavoriteProperties($userId);
            $searchHistory = $this->userModel->getSearchHistory($userId);
            $userAlerts = $this->userModel->getAlerts($userId);
            $userMessages = $this->userModel->getMessages($userId);

            // Prepare view data
            $data = [
                'pageTitle' => 'Panel de Comprador - Tierras.mx',
                'username' => $this->user['username'],
                'userProfile' => $userProfile,
                'savedProperties' => $savedProperties,
                'favoriteProperties' => $favoriteProperties,
                'searchHistory' => $searchHistory,
                'userAlerts' => $userAlerts,
                'userMessages' => $userMessages
            ];

            // Render the view
            $this->render('dashboard/buyer', $data);

        } catch (Exception $e) {
            $this->handleError('Error al cargar el panel de comprador: ' . $e->getMessage());
        }
    }

    /**
     * Show seller dashboard
     */
    public function sellerDashboard() {
        try {
            $this->requireRole('seller');

            $userId = $this->user['id'];

            // Get user profile
            $userProfile = $this->userModel->getById($userId);

            // Get seller's properties
            $sellerProperties = $this->getSellerProperties($userId);

            // Get client leads
            $clientLeads = $this->getClientLeads($userId);

            // Mock analytics data (replace with real analytics)
            $analytics = $this->getMockAnalytics($sellerProperties, $clientLeads);

            // Prepare view data
            $data = [
                'pageTitle' => 'Panel de Vendedor - Tierras.mx',
                'username' => $this->user['username'],
                'userProfile' => $userProfile,
                'sellerProperties' => $sellerProperties,
                'clientLeads' => $clientLeads,
                'analytics' => $analytics
            ];

            // Render the view
            $this->render('dashboard/seller', $data);

        } catch (Exception $e) {
            $this->handleError('Error al cargar el panel de vendedor: ' . $e->getMessage());
        }
    }

    /**
     * Show agent dashboard
     */
    public function agentDashboard() {
        try {
            $this->requireRole('agent');

            $userId = $this->user['id'];

            // Get agent profile
            $agentProfile = $this->getAgentProfile($userId);

            // Get agent's properties
            $agentProperties = $this->getAgentProperties($userId);

            // Get client leads
            $clientLeads = $this->getClientLeads($userId);

            // Mock analytics data
            $analytics = $this->getMockAnalytics($agentProperties, $clientLeads);

            // Prepare view data
            $data = [
                'pageTitle' => 'Panel de Agente - Tierras.mx',
                'username' => $this->user['username'],
                'agentProfile' => $agentProfile,
                'agentProperties' => $agentProperties,
                'clientLeads' => $clientLeads,
                'analytics' => $analytics
            ];

            // Render the view
            $this->render('dashboard/agent', $data);

        } catch (Exception $e) {
            $this->handleError('Error al cargar el panel de agente: ' . $e->getMessage());
        }
    }

    /**
     * Get seller's properties
     */
    private function getSellerProperties($userId) {
        $sql = "SELECT p.* FROM properties p WHERE p.agent_id = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database prepare error: " . $this->conn->error);
        }

        $stmt->bind_param("i", $userId);
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
     * Get agent's properties
     */
    private function getAgentProperties($userId) {
        return $this->getSellerProperties($userId); // Same logic for agents
    }

    /**
     * Get client leads (messages)
     */
    private function getClientLeads($userId) {
        $sql = "SELECT um.*, u.username as client_name, u.email as client_email
                FROM user_messages um
                INNER JOIN users u ON um.sender_id = u.id
                WHERE um.receiver_id = ? ORDER BY um.sent_at DESC LIMIT 20";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database prepare error: " . $this->conn->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $leads = [];
        while ($row = $result->fetch_assoc()) {
            $leads[] = $row;
        }

        $stmt->close();
        return $leads;
    }

    /**
     * Get agent profile with additional agent data
     */
    private function getAgentProfile($userId) {
        $userProfile = $this->userModel->getById($userId);

        // Get agent-specific data
        $sql = "SELECT * FROM agents WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $agentData = $result->fetch_assoc();
                $userProfile = array_merge($userProfile, $agentData);
            }
            $stmt->close();
        }

        return $userProfile;
    }

    /**
     * Get mock analytics data (replace with real analytics)
     */
    private function getMockAnalytics($properties, $leads) {
        return [
            'total_views' => count($properties) * 45,
            'total_inquiries' => count($leads),
            'properties_sold_this_month' => rand(1, 3),
            'average_response_time' => rand(2, 5),
            'conversion_rate' => rand(10, 25)
        ];
    }
}
?>