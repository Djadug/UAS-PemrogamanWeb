<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('modules/dashboard');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    try {
        $db = Database::getInstance();
        
        // Validate username
        if (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long';
        }
        
        // Check if username exists
        $existingUser = $db->fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existingUser) {
            $errors[] = 'Username already taken';
        }
        
        // Validate email
        if (!validateEmail($email)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        // Check if email exists
        $existingEmail = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existingEmail) {
            $errors[] = 'Email already registered';
        }
        
        // Validate password
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        // If no errors, create user
        if (empty($errors)) {
            $userId = $db->insert('users', [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($userId) {
                // Set session
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                
                setFlashMessage('success', 'Welcome to ' . SITE_NAME . '! Your account has been created.');
                redirect('auth/login.php');
            } else {
                $errors[] = 'Failed to create account. Please try again.';
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $errors[] = 'An error occurred. Please try again later.';
    }
}

$pageTitle = 'Register';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Create an Account</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                                   required>
                            <div class="invalid-feedback">
                                Please choose a username.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                   required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required>
                            <div class="invalid-feedback">
                                Please enter a password.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required>
                            <div class="invalid-feedback">
                                Please confirm your password.
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
