<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page with success message
header('Location: index.php?status=success&message=Has cerrado sesión exitosamente.');
exit();
?>