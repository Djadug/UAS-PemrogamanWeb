<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/modules/auth/login.php');  // Perbaiki path
    exit();
}

try {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    
    // Get user's latest carbon footprint data
    $latestFootprint = $db->fetchOne(
        "SELECT * FROM carbon_footprints 
         WHERE user_id = ? 
         ORDER BY date DESC 
         LIMIT 1",
        [$userId]
    );
    
    // Get monthly averages
    $monthlyAverages = $db->fetchAll(
        "SELECT DATE_FORMAT(date, '%Y-%m') as month,
                AVG(total) as average
         FROM carbon_footprints
         WHERE user_id = ?
         GROUP BY DATE_FORMAT(date, '%Y-%m')
         ORDER BY month DESC
         LIMIT 6",
        [$userId]
    );
    
    // Get active challenges
    $activeChallenges = $db->fetchAll(
        "SELECT c.*, uc.progress
         FROM challenges c
         JOIN user_challenges uc ON c.id = uc.challenge_id
         WHERE uc.user_id = ? AND c.status = 'active'
         ORDER BY c.end_date ASC
         LIMIT 3",
        [$userId]
    );
    
    // Get recent community posts
    $recentPosts = $db->fetchAll(
        "SELECT p.*, u.username, u.profile_picture,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
         FROM community_posts p
         JOIN users u ON p.user_id = u.id
         WHERE p.status = 'active'
         ORDER BY p.created_at DESC
         LIMIT 5"
    );
    
} catch (Exception $e) {
    error_log($e->getMessage());
    setFlashMessage('error', 'Failed to load dashboard data.');
    redirect('index.php');
}

$pageTitle = 'Dashboard';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Welcome Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
                    
                    <?php if ($latestFootprint): ?>
                        <div class="carbon-widget p-4 rounded">
                            <h4>Latest Carbon Footprint</h4>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5>Transportation</h5>
                                        <p class="h3 mb-0"><?= formatNumber($latestFootprint['transportation']) ?> km</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5>Energy</h5>
                                        <p class="h3 mb-0"><?= formatNumber($latestFootprint['energy']) ?> kWh</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h5>Waste</h5>
                                        <p class="h3 mb-0"><?= formatNumber($latestFootprint['waste']) ?> kg</p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <a href="../carbon_tracker/calculate.php" class="btn btn-light">Calculate New</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Start tracking your carbon footprint! 
                            <a href="../carbon_tracker/calculate.php" class="alert-link">Calculate now</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Progress Chart -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4>Your Progress</h4>
                    <canvas id="progressChart" height="300"></canvas>
                </div>
            </div>
            
            <!-- Active Challenges -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4>Active Challenges</h4>
                    <?php if (empty($activeChallenges)): ?>
                        <p class="text-muted">
                            No active challenges. 
                            <a href="../community/challenges.php">Join a challenge</a>
                        </p>
                    <?php else: ?>
                        <?php foreach ($activeChallenges as $challenge): ?>
                            <div class="challenge-card p-3 mb-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($challenge['title']) ?></h5>
                                        <p class="text-muted mb-2">
                                            Ends <?= formatDate($challenge['end_date']) ?>
                                        </p>
                                    </div>
                                    <span class="badge bg-primary"><?= $challenge['points'] ?> Points</span>
                                </div>
                                <div class="progress progress-eco">
                                    <div class="progress-bar progress-bar-eco" 
                                         role="progressbar" 
                                         style="width: <?= $challenge['progress'] ?>%" 
                                         aria-valuenow="<?= $challenge['progress'] ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?= $challenge['progress'] ?>%
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4>Quick Stats</h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Total Calculations</span>
                            <span class="badge bg-primary rounded-pill">
                                <?= $db->fetchOne(
                                    "SELECT COUNT(*) as count FROM carbon_footprints WHERE user_id = ?",
                                    [$userId]
                                )['count'] ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Challenges Completed</span>
                            <span class="badge bg-success rounded-pill">
                                <?= $db->fetchOne(
                                    "SELECT COUNT(*) as count FROM user_challenges 
                                     WHERE user_id = ? AND status = 'completed'",
                                    [$userId]
                                )['count'] ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Forum Posts</span>
                            <span class="badge bg-info rounded-pill">
                                <?= $db->fetchOne(
                                    "SELECT COUNT(*) as count FROM community_posts WHERE user_id = ?",
                                    [$userId]
                                )['count'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Community Activity -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4>Recent Community Posts</h4>
                    <?php if (empty($recentPosts)): ?>
                        <p class="text-muted">No recent posts</p>
                    <?php else: ?>
                        <?php foreach ($recentPosts as $post): ?>
                            <div class="mb-3">
                                <h6 class="mb-1">
                                    <a href="../community/forums.php" class="text-decoration-none">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    by <?= htmlspecialchars($post['username']) ?> • 
                                    <?= $post['comment_count'] ?> comments
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize progress chart
const monthlyData = <?= json_encode(array_reverse($monthlyAverages)) ?>;
const ctx = document.getElementById('progressChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthlyData.map(item => item.month),
        datasets: [{
            label: 'Monthly Average CO2e (tonnes)',
            data: monthlyData.map(item => item.average),
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
