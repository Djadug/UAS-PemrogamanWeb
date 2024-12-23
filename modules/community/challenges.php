<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
requireLogin();

try {
    $db = Database::getInstance();
    
    // Get active challenges
    $activeChallenges = $db->fetchAll(
        "SELECT c.*, 
                COUNT(DISTINCT uc.user_id) as participants,
                uc2.status as user_status,
                uc2.progress as user_progress
         FROM challenges c
         LEFT JOIN user_challenges uc ON c.id = uc.challenge_id
         LEFT JOIN user_challenges uc2 ON c.id = uc2.challenge_id AND uc2.user_id = ?
         WHERE c.status = 'active'
         GROUP BY c.id
         ORDER BY c.end_date ASC",
        [$_SESSION['user_id']]
    );
    
    // Get user's completed challenges
    $completedChallenges = $db->fetchAll(
        "SELECT c.*, uc.completed_at
         FROM challenges c
         JOIN user_challenges uc ON c.id = uc.challenge_id
         WHERE uc.user_id = ? AND uc.status = 'completed'
         ORDER BY uc.completed_at DESC
         LIMIT 5",
        [$_SESSION['user_id']]
    );
    
} catch (Exception $e) {
    error_log($e->getMessage());
    setFlashMessage('error', 'Failed to load challenges.');
    redirect('modules/dashboard');
}

$pageTitle = 'Community Challenges';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-4">Active Challenges</h2>
                    
                    <?php if (empty($activeChallenges)): ?>
                        <div class="alert alert-info">
                            No active challenges at the moment. Check back later!
                        </div>
                    <?php else: ?>
                        <?php foreach ($activeChallenges as $challenge): ?>
                            <div class="challenge-card card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h4><?= htmlspecialchars($challenge['title']) ?></h4>
                                            <p class="text-muted">
                                                <?= htmlspecialchars($challenge['description']) ?>
                                            </p>
                                        </div>
                                        <span class="badge bg-primary"><?= $challenge['points'] ?> Points</span>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <small>Progress</small>
                                            <small><?= $challenge['user_progress'] ?? 0 ?>%</small>
                                        </div>
                                        <div class="progress progress-eco">
                                            <div class="progress-bar progress-bar-eco" 
                                                 role="progressbar" 
                                                 style="width: <?= $challenge['user_progress'] ?? 0 ?>%" 
                                                 aria-valuenow="<?= $challenge['user_progress'] ?? 0 ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-users me-1"></i>
                                            <?= $challenge['participants'] ?> participants
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-calendar me-1"></i>
                                            Ends <?= formatDate($challenge['end_date']) ?>
                                        </div>
                                        
                                        <?php if (!$challenge['user_status']): ?>
                                            <button class="btn btn-primary btn-sm" 
                                                    onclick="joinChallenge(<?= $challenge['id'] ?>)">
                                                Join Challenge
                                            </button>
                                        <?php elseif ($challenge['user_status'] === 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    onclick="updateProgress(<?= $challenge['id'] ?>)">
                                                Update Progress
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4>Your Achievements</h4>
                    <?php if (empty($completedChallenges)): ?>
                        <p class="text-muted">No completed challenges yet. Start participating!</p>
                    <?php else: ?>
                        <ul class="list-unstyled">
                            <?php foreach ($completedChallenges as $completed): ?>
                                <li class="mb-2">
                                    <i class="fas fa-trophy text-warning me-2"></i>
                                    <?= htmlspecialchars($completed['title']) ?>
                                    <small class="text-muted d-block">
                                        Completed on <?= formatDate($completed['completed_at']) ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4>Leaderboard</h4>
                    <p>
                        Check out the <a href="leaderboard.php">full leaderboard</a> 
                        to see top contributors!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function joinChallenge(challengeId) {
    if (confirm('Are you sure you want to join this challenge?')) {
        fetch('/api/challenges/join', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ challengeId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to join challenge. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

function updateProgress(challengeId) {
    const progress = prompt('Enter your progress (0-100):', '0');
    if (progress !== null) {
        const progressNum = parseInt(progress);
        if (isNaN(progressNum) || progressNum < 0 || progressNum > 100) {
            alert('Please enter a valid number between 0 and 100');
            return;
        }
        
        fetch('/api/challenges/progress', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                challengeId,
                progress: progressNum
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update progress. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>
