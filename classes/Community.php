<?php
class Community {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a forum post
     * 
     * @param array $data Post data
     * @return int Post ID
     */
    public function createPost($data) {
        try {
            return $this->db->insert('community_posts', [
                'user_id' => $data['user_id'],
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to create post');
        }
    }
    
    /**
     * Get forum posts
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array Posts and pagination data
     */
    public function getPosts($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            
            $totalPosts = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM community_posts WHERE status = 'active'"
            )['count'];
            
            $posts = $this->db->fetchAll(
                "SELECT p.*, u.username, u.profile_picture,
                        (SELECT COUNT(*) FROM comments 
                         WHERE post_id = p.id AND status = 'active') as comment_count
                 FROM community_posts p
                 JOIN users u ON p.user_id = u.id
                 WHERE p.status = 'active'
                 ORDER BY p.created_at DESC
                 LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
            
            return [
                'posts' => $posts,
                'total' => $totalPosts,
                'pages' => ceil($totalPosts / $limit),
                'current_page' => $page
            ];
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to fetch posts');
        }
    }
    
    /**
     * Add comment to post
     * 
     * @param array $data Comment data
     * @return int Comment ID
     */
    public function addComment($data) {
        try {
            return $this->db->insert('comments', [
                'post_id' => $data['post_id'],
                'user_id' => $data['user_id'],
                'content' => $data['content'],
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to add comment');
        }
    }
    
    /**
     * Get leaderboard data
     * 
     * @param int $limit Number of users to return
     * @return array Leaderboard data
     */
    public function getLeaderboard($limit = 10) {
        try {
            return $this->db->fetchAll(
                "SELECT u.id, u.username, u.profile_picture,
                        COUNT(DISTINCT uc.challenge_id) as challenges_completed,
                        SUM(c.points) as total_points
                 FROM users u
                 LEFT JOIN user_challenges uc ON u.id = uc.user_id 
                 AND uc.status = 'completed'
                 LEFT JOIN challenges c ON uc.challenge_id = c.id
                 GROUP BY u.id
                 ORDER BY total_points DESC, challenges_completed DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to fetch leaderboard');
        }
    }
    
    /**
     * Get user's rank
     * 
     * @param int $userId User ID
     * @return int User's rank
     */
    public function getUserRank($userId) {
        try {
            $result = $this->db->fetchOne(
                "SELECT rank
                 FROM (
                     SELECT id, 
                            RANK() OVER (ORDER BY total_points DESC) as rank
                     FROM (
                         SELECT u.id,
                                SUM(COALESCE(c.points, 0)) as total_points
                         FROM users u
                         LEFT JOIN user_challenges uc ON u.id = uc.user_id 
                         AND uc.status = 'completed'
                         LEFT JOIN challenges c ON uc.challenge_id = c.id
                         GROUP BY u.id
                     ) ranked_users
                 ) user_ranks
                 WHERE id = ?",
                [$userId]
            );
            
            return $result ? $result['rank'] : null;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to fetch user rank');
        }
    }
}
