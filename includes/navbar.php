<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$currentUser = getCurrentUser();
?>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <div class="navbar-brand"">  
            <?= SITE_NAME ?>
        </div>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>   
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('modules/dashboard') ?>">Home</a>
                </li>
                
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('modules/dashboard') ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('modules/carbon_tracker/calculate.php') ?>">Calculate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('modules/community/challenges.php') ?>">Challenges</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('modules/education/articles.php') ?>">Learn</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                           <?= htmlspecialchars(getCurrentUser() ? getCurrentUser()['username'] : 'User') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= url('profile.php') ?>">Profile</a></li>
                            <li><a class="dropdown-item" href="<?= url('settings.php') ?>">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= url('modules/auth/logout.php') ?>">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('modules/auth/login.php') ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('modules/auth/register.php') ?>">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 