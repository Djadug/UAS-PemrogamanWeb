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
    
    // Get total records
    $totalRecords = $db->fetchOne(
        "SELECT COUNT(*) as count FROM carbon_footprints WHERE user_id = ?", 
        [$_SESSION['user_id']]
    )['count'];
    
    $totalPages = ceil($totalRecords / $limit);
    
    // Get records for current page
    $records = $db->fetchAll(
        "SELECT * FROM carbon_footprints 
         WHERE user_id = ? 
         ORDER BY date DESC, created_at DESC 
         LIMIT ? OFFSET ?",
        [$_SESSION['user_id'], $limit, $offset]
    );
    
} catch (Exception $e) {
    error_log($e->getMessage());
    setFlashMessage('error', 'Failed to load carbon footprint history.');
    redirect('modules/dashboard');
}

$pageTitle = 'Carbon Footprint History';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-4">Your Carbon Footprint History</h2>
                    
                    <?php if (empty($records)): ?>
                        <div class="alert alert-info">
                            No records found. Start by <a href="calculate.php">calculating your carbon footprint</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Transportation</th>
                                        <th>Energy</th>
                                        <th>Waste</th>
                                        <th>Total (CO2e)</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): ?>
                                        <tr>
                                            <td><?= formatDate($record['date']) ?></td>
                                            <td><?= formatNumber($record['transportation']) ?> km</td>
                                            <td><?= formatNumber($record['energy']) ?> kWh</td>
                                            <td><?= formatNumber($record['waste']) ?> kg</td>
                                            <td class="fw-bold"><?= formatNumber($record['total']) ?> tonnes</td>
                                            <td><?= htmlspecialchars($record['description']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <a href="report.php" class="btn btn-primary">
                                <i class="fas fa-chart-line me-2"></i>View Detailed Report
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
