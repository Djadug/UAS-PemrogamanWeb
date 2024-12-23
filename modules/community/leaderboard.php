<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
requireLogin();

try {
    $db = Database::getInstance();
    
    // Get top users by challenge points
    $topUsers = $db->fetchAll(
        "SELECT u.id, u.username, u.profile_picture,
                COUNT(DISTINCT uc.challenge_id) as challenges_completed,
                SUM(c.points) as total_points
         FROM users u
         LEFT JOIN user_challenges uc ON u.id = uc.user_id AND uc.status = 'completed'
         LEFT JOIN challenges c ON uc.challenge_id = c.id
         GROUP BY u.id
         ORDER BY total_points DESC, challenges_completed DESC
         LIMIT 10"
    );
    
    // Get user's rank
    $userRank = $db->fetchOne(
        "SELECT rank
         FROM (
             SELECT id, 
                    RANK() OVER (ORDER BY total_points DESC, challenges_completed DESC) as rank
             FROM (
                 SELECT u.id,
                        COUNT(DISTINCT uc.challenge_id) as challenges_completed,
                        SUM(COALESCE(c.points, 0)) as total_points
                 FROM users u
                 LEFT JOIN user_challenges uc ON u.id = uc.user_id AND uc.status = 'completed'
                 LEFT JOIN challenges c ON uc.challenge_id = c.id
                 GROUP BY u.id
             ) ranked_users
         ) user_ranks
         WHERE id = ?",
        [$_SESSION['user_id']]
    );
    
} catch (Exception $e) {
    error_log($e->getMessage());
    setFlashMessage('error', 'Failed to load leaderboard.');
    redirect('modules/dashboard');
}

$pageTitle = 'Community Leaderboard';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-4">Community Leaderboard</h2>
                    
                    <div class="alert alert-info mb-4">
                        Your current rank: #<?= $userRank['rank'] ?? 'N/A' ?>
                    </div>
                    
                    <?php if (empty($topUsers)): ?>
                        <div class="alert alert-info">
                            No rankings available yet. Start completing challenges!
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>User</th>
                                        <th>Challenges Completed</th>
                                        <th>Total Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topUsers as $index => $user): ?>
                                        <tr class="<?= $user['id'] === $_SESSION['user_id'] ? 'table-primary' : '' ?>">
                                            <td class="align-middle">
                                                <?php if ($index < 3): ?>
                                                    <i class="fas fa-trophy text-warning"></i>
                                                <?php else: ?>
                                                    #<?= $index + 1 ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= asset('uploads/profiles/' . ($user['profile_picture'] ?? 'default.png')) ?>" 
                                                         class="rounded-circle me-2" 
                                                         width="32" 
                                                         height="32" 
                                                         alt="Profile">
                                                    <?= htmlspecialchars($user['username']) ?>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <?= $user['challenges_completed'] ?>
                                            </td>
                                            <td class="align-middle">
                                                <strong><?= $user['total_points'] ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="challenges.php" class="btn btn-primary">
                                View Active Challenges
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h4>How to Earn Points</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Complete community challenges
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Participate in discussions
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Share your eco-friendly achievements
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Help others reduce their carbon footprint
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
