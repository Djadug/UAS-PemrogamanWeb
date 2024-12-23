<?php
class Challenge {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new challenge
     * 
     * @param array $data Challenge data
     * @return int Challenge ID
     */
    public function create($data) {
        try {
            return $this->db->insert('challenges', [
                'title' => $data['title'],
                'description' => $data['description'],
                'points' => $data['points'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to create challenge');
        }
    }
    
    /**
     * Get active challenges
     * 
     * @param int $userId Optional user ID for participation status
     * @return array Active challenges
     */
    public function getActive($userId = null) {
        try {
            $sql = "SELECT c.*, 
                           COUNT(DISTINCT uc.user_id) as participants";
            
            if ($userId) {
                $sql .= ", uc2.status as user_status, 
                          uc2.progress as user_progress";
            }
            
            $sql .= " FROM challenges c
                     LEFT JOIN user_challenges uc ON c.id = uc.challenge_id";
            
            if ($userId) {
                $sql .= " LEFT JOIN user_challenges uc2 ON c.id = uc2.challenge_id 
                         AND uc2.user_id = ?";
            }
            
            $sql .= " WHERE c.status = 'active'
                     GROUP BY c.id
                     ORDER BY c.end_date ASC";
            
            return $this->db->fetchAll($sql, $userId ? [$userId] : []);
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to fetch challenges');
        }
    }
    
    /**
     * Join a challenge
     * 
     * @param int $challengeId Challenge ID
     * @param int $userId User ID
     * @return bool Success status
     */
    public function join($challengeId, $userId) {
        try {
            $this->db->insert('user_challenges', [
                'user_id' => $userId,
                'challenge_id' => $challengeId,
                'status' => 'joined',
                'progress' => 0,
                'joined_at' => date('Y-m-d H:i:s')
            ]);
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to join challenge');
        }
    }
    
    /**
     * Update challenge progress
     * 
     * @param int $challengeId Challenge ID
     * @param int $userId User ID
     * @param int $progress Progress percentage
     * @return bool Success status
     */
    public function updateProgress($challengeId, $userId, $progress) {
        try {
            $status = $progress >= 100 ? 'completed' : 'in_progress';
            $completedAt = $progress >= 100 ? date('Y-m-d H:i:s') : null;
            
            $this->db->update(
                'user_challenges',
                [
                    'progress' => $progress,
                    'status' => $status,
                    'completed_at' => $completedAt
                ],
                'challenge_id = ? AND user_id = ?',
                [$challengeId, $userId]
            );
            
            // Award points if completed
            if ($status === 'completed') {
                $challenge = $this->db->fetchOne(
                    "SELECT points FROM challenges WHERE id = ?",
                    [$challengeId]
                );
                
                // Update user points (assuming there's a points column in users table)
                $this->db->query(
                    "UPDATE users SET points = points + ? WHERE id = ?",
                    [$challenge['points'], $userId]
                );
            }
            
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to update progress');
        }
    }
    
    /**
     * Get user's challenge history
     * 
     * @param int $userId User ID
     * @return array Challenge history
     */
    public function getUserHistory($userId) {
        try {
            return $this->db->fetchAll(
                "SELECT c.*, uc.status, uc.progress, uc.completed_at
                 FROM challenges c
                 JOIN user_challenges uc ON c.id = uc.challenge_id
                 WHERE uc.user_id = ?
                 ORDER BY uc.completed_at DESC, uc.joined_at DESC",
                [$userId]
            );
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to fetch challenge history');
        }
    }
}
