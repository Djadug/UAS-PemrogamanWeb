<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

$request = $_SERVER['REQUEST_URI'];
$basePath = '/EcoTrack/';
$request = str_replace($basePath, '', $request);

switch ($request) {
    case '':
    case '/':
        require __DIR__ . '/../modules/auth/login.php';
        break;
    
    // Tambahkan route untuk forgot password
    case 'forgot-password':
        require __DIR__ . '/../views/forgot-password.php';
        break;
        
    case 'privacy-policy':
        require __DIR__ . '/../views/privacy-policy.php';
        break;
        
    case 'terms':
        require __DIR__ . '/../views/terms.php';
        break;
        
    default:
        http_response_code(404);
        require __DIR__ . '/../views/404.php';
        break;
} 