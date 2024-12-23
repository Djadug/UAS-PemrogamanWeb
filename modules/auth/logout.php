<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Clear all session data
session_unset();
session_destroy();

// Redirect to login page
setFlashMessage('success', 'You have been successfully logged out.');
redirect('modules/auth/login.php');
