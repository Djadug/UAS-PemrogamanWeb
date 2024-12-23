<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
requireLogin();

try {
    $db = Database::getInstance();
    
    // Get tips by category
    $categories = [
        'transportation' => [
            'icon' => 'fa-car',
            'color' => 'primary',
            'tips' => $db->fetchAll(
                "SELECT * FROM tips 
                 WHERE category = 'transportation' 
                 AND status = 'active' 
                 ORDER BY created_at DESC"
            )
        ],
        'energy' => [
            'icon' => 'fa-bolt',
            'color' => 'warning',
            'tips' => $db->fetchAll(
                "SELECT * FROM tips 
                 WHERE category = 'energy' 
                 AND status = 'active' 
                 ORDER BY created_at DESC"
            )
        ],
        'waste' => [
            'icon' => 'fa-trash',
            'color' => 'danger',
            'tips' => $db->fetchAll(
                "SELECT * FROM tips 
                 WHERE category = 'waste' 
                 AND status = 'active' 
                 ORDER BY created_at DESC"
            )
        ],
        'lifestyle' => [
            'icon' => 'fa-leaf',
            'color' => 'success',
            'tips' => $db->fetchAll(
                "SELECT * FROM tips 
                 WHERE category = 'lifestyle' 
                 AND status = 'active' 
                 ORDER BY created_at DESC"
            )
        ]
    ];
    
} catch (Exception $e) {
    error_log($e->getMessage());
    setFlashMessage('error', 'Failed to load tips.');
    redirect('modules/dashboard');
}

$pageTitle = 'Eco-Friendly Tips';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-4">Eco-Friendly Tips</h2>
                    
                    <!-- Category Tabs -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <?php $first = true; ?>
                        <?php foreach ($categories as $key => $category): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $first ? 'active' : '' ?>" 
                                   id="<?= $key ?>-tab" 
                                   data-bs-toggle="tab" 
                                   href="#<?= $key ?>" 
                                   role="tab">
                                    <i class="fas <?= $category['icon'] ?> text-<?= $category['color'] ?> me-2"></i>
                                    <?= ucfirst($key) ?>
                                </a>
                            </li>
                            <?php $first = false; ?>
                        <?php endforeach; ?>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content">
                        <?php $first = true; ?>
                        <?php foreach ($categories as $key => $category): ?>
                            <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" 
                                 id="<?= $key ?>" 
                                 role="tabpanel">
                                
                                <?php if (empty($category['tips'])): ?>
                                    <div class="alert alert-info">
                                        No tips available for this category.
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($category['tips'] as $tip): ?>
                                            <div class="col-md-6 mb-4">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <h5 class="card-title">
                                                            <i class="fas <?= $category['icon'] ?> text-<?= $category['color'] ?> me-2"></i>
                                                            <?= htmlspecialchars($tip['title']) ?>
                                                        </h5>
                                                        <p class="card-text">
                                                            <?= nl2br(htmlspecialchars($tip['content'])) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                            </div>
                            <?php $first = false; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Additional Resources -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h4>Additional Resources</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5>
                                        <i class="fas fa-book text-primary me-2"></i>
                                        Educational Articles
                                    </h5>
                                    <p>
                                        Learn more about environmental issues and solutions.
                                    </p>
                                    <a href="articles.php" class="btn btn-primary">
                                        View Articles
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5>
                                        <i class="fas fa-users text-success me-2"></i>
                                        Community Challenges
                                    </h5>
                                    <p>
                                        Join others in making a positive impact.
                                    </p>
                                    <a href="../community/challenges.php" class="btn btn-success">
                                        View Challenges
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5>
                                        <i class="fas fa-calculator text-warning me-2"></i>
                                        Carbon Calculator
                                    </h5>
                                    <p>
                                        Track your environmental impact.
                                    </p>
                                    <a href="../carbon_tracker/calculate.php" class="btn btn-warning">
                                        Calculate Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
