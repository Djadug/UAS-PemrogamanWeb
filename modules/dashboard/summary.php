<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
requireLogin();

try {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    
    // Get date range from query parameters
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    
    // Get summary data
    $summary = $db->fetchOne(
        "SELECT 
            COUNT(*) as total_entries,
            AVG(transportation) as avg_transportation,
            AVG(energy) as avg_energy,
            AVG(waste) as avg_waste,
            AVG(total) as avg_total,
            MIN(total) as min_total,
            MAX(total) as max_total
         FROM carbon_footprints
         WHERE user_id = ?
         AND date BETWEEN ? AND ?",
        [$userId, $startDate, $endDate]
    );
    
    // Get daily data for chart
    $dailyData = $db->fetchAll(
        "SELECT date, total
         FROM carbon_footprints
         WHERE user_id = ?
         AND date BETWEEN ? AND ?
         ORDER BY date ASC",
        [$userId, $startDate, $endDate]
    );
    
    // Get category breakdown
    $categoryBreakdown = [
        'transportation' => ($summary['avg_transportation'] / $summary['avg_total']) * 100,
        'energy' => ($summary['avg_energy'] / $summary['avg_total']) * 100,
        'waste' => ($summary['avg_waste'] / $summary['avg_total']) * 100
    ];
    
} catch (Exception $e) {
    error_log($e->getMessage());
    setFlashMessage('error', 'Failed to load summary data.');
    redirect('index.php');
}

$pageTitle = 'Carbon Footprint Summary';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Carbon Footprint Summary</h2>
                        
                        <!-- Date Range Filter -->
                        <form class="d-flex gap-2">
                            <input type="date" 
                                   class="form-control" 
                                   name="start_date" 
                                   value="<?= $startDate ?>">
                            <input type="date" 
                                   class="form-control" 
                                   name="end_date" 
                                   value="<?= $endDate ?>">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>
                    </div>
                    
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5>Total Entries</h5>
                                    <h3><?= $summary['total_entries'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5>Average CO2e</h5>
                                    <h3><?= formatNumber($summary['avg_total']) ?></h3>
                                    <small>tonnes/entry</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5>Lowest CO2e</h5>
                                    <h3><?= formatNumber($summary['min_total']) ?></h3>
                                    <small>tonnes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5>Highest CO2e</h5>
                                    <h3><?= formatNumber($summary['max_total']) ?></h3>
                                    <small>tonnes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts -->
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="trendChart" height="300"></canvas>
                        </div>
                        <div class="col-md-4">
                            <canvas id="categoryChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recommendations -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4>Recommendations</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5>
                                        <i class="fas fa-car text-primary me-2"></i>
                                        Transportation
                                    </h5>
                                    <p>
                                        Your average transportation footprint: 
                                        <?= formatNumber($summary['avg_transportation']) ?> km/day
                                    </p>
                                    <ul>
                                        <li>Consider carpooling or public transport</li>
                                        <li>Plan trips to combine multiple errands</li>
                                        <li>Maintain your vehicle for better efficiency</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5>
                                        <i class="fas fa-bolt text-warning me-2"></i>
                                        Energy
                                    </h5>
                                    <p>
                                        Your average energy usage: 
                                        <?= formatNumber($summary['avg_energy']) ?> kWh/month
                                    </p>
                                    <ul>
                                        <li>Use energy-efficient appliances</li>
                                        <li>Optimize heating and cooling</li>
                                        <li>Consider renewable energy options</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5>
                                        <i class="fas fa-trash text-danger me-2"></i>
                                        Waste
                                    </h5>
                                    <p>
                                        Your average waste generation: 
                                        <?= formatNumber($summary['avg_waste']) ?> kg/week
                                    </p>
                                    <ul>
                                        <li>Practice proper recycling</li>
                                        <li>Reduce single-use plastics</li>
                                        <li>Start composting organic waste</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($dailyData, 'date')) ?>,
        datasets: [{
            label: 'Daily CO2e (tonnes)',
            data: <?= json_encode(array_column($dailyData, 'total')) ?>,
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

// Category Breakdown Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: ['Transportation', 'Energy', 'Waste'],
        datasets: [{
            data: [
                <?= $categoryBreakdown['transportation'] ?>,
                <?= $categoryBreakdown['energy'] ?>,
                <?= $categoryBreakdown['waste'] ?>
            ],
            backgroundColor: [
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(255, 99, 132)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Carbon Footprint by Category'
            }
        }
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
