<?php
require_once 'auth.php';

// Log the logout action before destroying session
if (isAdminLoggedIn()) {
    logAdminAction('logout', 'user', $_SESSION['id'], null, ['logout_time' => date('Y-m-d H:i:s')]);
}

// Perform logout
adminLogout();
?>