<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
requireLogin();

$errors = [];
$success = false;

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    
    try {
        if (empty($title)) {
            $errors[] = 'Title is required';
        }
        if (empty($content)) {
            $errors[] = 'Content is required';
        }
        
        if (empty($errors)) {
            $db = Database::getInstance();
            $db->insert('community_posts', [
                'user_id' => $_SESSION['user_id'],
                'title' => $title,
                'content' => $content,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $success = true;
            setFlashMessage('success', 'Your post has been published!');
            redirect('modules/community/forums.php');
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $errors[] = 'Failed to create post. Please try again.';
    }
}

try {
    $db = Database::getInstance();
    
    // Get page number
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    // Get total posts
    $totalPosts = $db->fetchOne(
        "SELECT COUNT(*) as count FROM community_posts WHERE status = 'active'"
    )['count'];
    
    $totalPages = ceil($totalPosts / $limit);
    
    // Get posts with user info
    $posts = $db->fetchAll(
        "SELECT p.*, u.username, u.profile_picture,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'active') as comment_count
         FROM community_posts p
         JOIN users u ON p.user_id = u.id
         WHERE p.status = 'active'
         ORDER BY p.created_at DESC
         LIMIT ? OFFSET ?",
        [$limit, $offset]
    );
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $errors[] = 'Failed to load posts.';
}

$pageTitle = 'Community Forum';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="mb-4">Community Forum</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <button class="btn btn-primary mb-4" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#newPostForm">
                        <i class="fas fa-plus me-2"></i>New Post
                    </button>
                    
                    <div class="collapse mb-4" id="newPostForm">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="title" 
                                               required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Content</label>
                                        <textarea class="form-control" 
                                                  name="content" 
                                                  rows="4" 
                                                  required></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        Publish Post
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($posts)): ?>
                        <div class="alert alert-info">
                            No posts yet. Be the first to start a discussion!
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="<?= asset('uploads/profiles/' . ($post['profile_picture'] ?? 'default.png')) ?>" 
                                             class="rounded-circle me-2" 
                                             width="40" 
                                             height="40" 
                                             alt="Profile">
                                        <div>
                                            <h5 class="mb-0"><?= htmlspecialchars($post['username']) ?></h5>
                                            <small class="text-muted">
                                                <?= formatDate($post['created_at']) ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <h4><?= htmlspecialchars($post['title']) ?></h4>
                                    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                                    
                                    <div class="d-flex align-items-center">
                                        <a href="post.php?id=<?= $post['id'] ?>" class="text-decoration-none">
                                            <i class="far fa-comment me-1"></i>
                                            <?= $post['comment_count'] ?> Comments
                                        </a>
                                        
                                        <?php if ($post['user_id'] === $_SESSION['user_id']): ?>
                                            <div class="dropdown ms-auto">
                                                <button class="btn btn-link text-muted" 
                                                        data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" 
                                                           href="edit_post.php?id=<?= $post['id'] ?>">
                                                            Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" 
                                                           href="#"
                                                           onclick="deletePost(<?= $post['id'] ?>)">
                                                            Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4>Forum Guidelines</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Be respectful and constructive
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Share your eco-friendly experiences
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Help others with their questions
                        </li>
                        <li>
                            <i class="fas fa-times text-danger me-2"></i>
                            No spam or self-promotion
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4>Active Challenges</h4>
                    <p>
                        Check out our <a href="challenges.php">community challenges</a> 
                        and start participating!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post?')) {
        fetch('/api/posts/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ postId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete post. Please try again.');
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
