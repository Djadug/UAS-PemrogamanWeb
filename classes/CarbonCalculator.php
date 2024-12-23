<?php
class CarbonCalculator {
    private $db;
    private $userId;
    
    // Conversion factors (simplified for example)
    private const FACTORS = [
        'transportation' => 0.14, // kg CO2 per km
        'energy' => 0.47,        // kg CO2 per kWh
        'waste' => 0.11          // kg CO2 per kg waste
    ];
    
    public function __construct($userId) {
        $this->db = Database::getInstance();
        $this->userId = $userId;
    }
    
    /**
     * Calculate carbon footprint
     * 
     * @param float $transportation Daily transportation in km
     * @param float $energy Monthly energy usage in kWh
     * @param float $waste Weekly waste in kg
     * @param string $description Optional description
     * @return array Calculation results
     */
    public function calculate($transportation, $energy, $waste, $description = '') {
        $total = ($transportation * self::FACTORS['transportation']) +
                ($energy * self::FACTORS['energy']) +
                ($waste * self::FACTORS['waste']);
        
        try {
            $id = $this->db->insert('carbon_footprints', [
                'user_id' => $this->userId,
                'transportation' => $transportation,
                'energy' => $energy,
                'waste' => $waste,
                'total' => $total,
                'description' => $description,
                'date' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'id' => $id,
                'total' => $total,
                'breakdown' => [
                    'transportation' => $transportation * self::FACTORS['transportation'],
                    'energy' => $energy * self::FACTORS['energy'],
                    'waste' => $waste * self::FACTORS['waste']
                ]
            ];
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to save calculation');
        }
    }
    
    /**
     * Get user's carbon footprint history
     * 
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return array Carbon footprint records
     */
    public function getHistory($startDate = null, $endDate = null) {
        try {
            $sql = "SELECT * FROM carbon_footprints WHERE user_id = ?";
            $params = [$this->userId];
            
            if ($startDate) {
                $sql .= " AND date >= ?";
                $params[] = $startDate;
            }
            if ($endDate) {
                $sql .= " AND date <= ?";
                $params[] = $endDate;
            }
            
            $sql .= " ORDER BY date DESC";
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to fetch history');
        }
    }
    
    /**
     * Get summary statistics
     * 
     * @return array Summary data
     */
    public function getSummary() {
        try {
            return $this->db->fetchOne(
                "SELECT 
                    COUNT(*) as total_entries,
                    AVG(transportation) as avg_transportation,
                    AVG(energy) as avg_energy,
                    AVG(waste) as avg_waste,
                    AVG(total) as avg_total,
                    MIN(total) as min_total,
                    MAX(total) as max_total
                 FROM carbon_footprints
                 WHERE user_id = ?",
                [$this->userId]
            );
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to fetch summary');
        }
    }
    
    /**
     * Get monthly trends
     * 
     * @param int $months Number of months to look back
     * @return array Monthly data
     */
    public function getMonthlyTrends($months = 6) {
        try {
            return $this->db->fetchAll(
                "SELECT DATE_FORMAT(date, '%Y-%m') as month,
                        AVG(total) as average,
                        SUM(total) as total
                 FROM carbon_footprints
                 WHERE user_id = ?
                 AND date >= DATE_SUB(CURRENT_DATE, INTERVAL ? MONTH)
                 GROUP BY DATE_FORMAT(date, '%Y-%m')
                 ORDER BY month ASC",
                [$this->userId, $months]
            );
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception('Failed to fetch trends');
        }
    }
    
    /**
     * Get recommendations based on usage patterns
     * 
     * @return array Recommendations
     */
    public function getRecommendations() {
        $summary = $this->getSummary();
        $recommendations = [];
        
        // Transportation recommendations
        if ($summary['avg_transportation'] > 20) { // Example threshold
            $recommendations['transportation'] = [
                'Use public transportation when possible',
                'Consider carpooling',
                'Combine multiple errands into one trip'
            ];
        }
        
        // Energy recommendations
        if ($summary['avg_energy'] > 300) { // Example threshold
            $recommendations['energy'] = [
                'Switch to LED bulbs',
                'Use energy-efficient appliances',
                'Optimize heating/cooling settings'
            ];
        }
        
        // Waste recommendations
        if ($summary['avg_waste'] > 10) { // Example threshold
            $recommendations['waste'] = [
                'Start composting organic waste',
                'Reduce single-use plastics',
                'Implement recycling system'
            ];
        }
        
        return $recommendations;
    }
}
