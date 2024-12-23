<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
requireLogin();

try {
    $db = Database::getInstance();
    
    // Get page number
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    // Get category filter
    $category = isset($_GET['category']) ? sanitize($_GET['category']) : null;
    
    // Build query
    $whereClause = "WHERE status = 'published'";
    $params = [];
    
    if ($category) {
        $whereClause .= " AND category = ?";
        $params[] = $category;
    }
    
    // Get total articles
    $totalArticles = $db->fetchOne(
        "SELECT COUNT(*) as count FROM educational_content " . $whereClause,
        $params
    )['count'];
    
    $totalPages = ceil($totalArticles / $limit);
    
    // Get articles
    $articles = $db->fetchAll(
        "SELECT ec.*, u.username 
         FROM educational_content ec
         JOIN users u ON ec.author_id = u.id
         $whereClause
         ORDER BY ec.created_at DESC
         LIMIT ? OFFSET ?",
        array_merge($params, [$limit, $offset])
    );
    
    // Get categories for filter
    $categories = $db->fetchAll(
        "SELECT DISTINCT category 
         FROM educational_content 
         WHERE category IS NOT NULL 
         ORDER BY category"
    );
    
} catch (Exception $e) {
    error_log($e->getMessage());
    setFlashMessage('error', 'Failed to load articles.');
    redirect('modules/dashboard');
}

$pageTitle = 'Educational Articles';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Educational Articles</h2>
                        
                        <!-- Category Filter -->
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown">
                                <?= $category ? ucfirst($category) : 'All Categories' ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="?">All Categories</a>
                                </li>
                                <?php foreach ($categories as $cat): ?>
                                    <li>
                                        <a class="dropdown-item" 
                                           href="?category=<?= urlencode($cat['category']) ?>">
                                            <?= ucfirst($cat['category']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <?php if (empty($articles)): ?>
                        <div class="alert alert-info">
                            No articles found.
                        </div>
                    <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h3 class="card-title">
                                        <?= htmlspecialchars($article['title']) ?>
                                    </h3>
                                    
                                    <div class="mb-3">
                                        <span class="badge bg-primary">
                                            <?= ucfirst($article['category']) ?>
                                        </span>
                                        <small class="text-muted ms-2">
                                            By <?= htmlspecialchars($article['username']) ?> • 
                                            <?= formatDate($article['created_at']) ?>
                                        </small>
                                    </div>
                                    
                                    <p class="card-text">
                                        <?= nl2br(htmlspecialchars(
                                            substr($article['content'], 0, 300) . 
                                            (strlen($article['content']) > 300 ? '...' : '')
                                        )) ?>
                                    </p>
                                    
                                    <a href="article.php?id=<?= $article['id'] ?>" 
                                       class="btn btn-primary">
                                        Read More
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" 
                                               href="?page=<?= $i ?><?= $category ? '&category=' . urlencode($category) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Featured Articles -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4>Featured Articles</h4>
                    <div class="list-group list-group-flush">
                        <?php
                        $featured = $db->fetchAll(
                            "SELECT * FROM educational_content 
                             WHERE status = 'published' 
                             ORDER BY RAND() 
                             LIMIT 3"
                        );
                        foreach ($featured as $item):
                        ?>
                            <a href="article.php?id=<?= $item['id'] ?>" 
                               class="list-group-item list-group-item-action">
                                <h6 class="mb-1"><?= htmlspecialchars($item['title']) ?></h6>
                                <small class="text-muted">
                                    <?= ucfirst($item['category']) ?>
                                </small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Tips -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4>Quick Tips</h4>
                    <div class="list-group list-group-flush">
                        <a href="../carbon_tracker/calculate.php" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-calculator text-primary me-2"></i>
                            Calculate your carbon footprint
                        </a>
                        <a href="../community/challenges.php" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-trophy text-warning me-2"></i>
                            Join eco-friendly challenges
                        </a>
                        <a href="tips.php" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-lightbulb text-success me-2"></i>
                            View all eco tips
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
