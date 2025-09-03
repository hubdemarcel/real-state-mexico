<?php
/**
 * Refactored Buyer Dashboard
 *
 * This is the new MVC-based buyer dashboard that separates concerns
 * and follows better architectural patterns.
 */

// Include configuration and autoloader
require_once 'config.php';
require_once 'controllers/DashboardController.php';

// Create controller instance
$dashboardController = new DashboardController();

// Handle the request
$dashboardController->buyerDashboard();
?>