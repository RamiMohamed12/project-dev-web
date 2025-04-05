<?php
// Location: /home/demy/project-dev-web/src/Controller/logoutController.php

require_once __DIR__ . '/../Auth/AuthSession.php';

// Use the helper to destroy the session
AuthSession::destroySession();

// Redirect to login page with a success message
// Path is relative from this controller
header('Location: ../View/login.php?logout=success');
exit();
?>
