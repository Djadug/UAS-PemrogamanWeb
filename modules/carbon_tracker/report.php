<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
requireLogin();

try {
    $db = Database::getInstance();
    
    // Get monthly totals for the past 6 months
    $monthlyData = $db->fetchAll(
        "SELECT DATE_FORMAT(date, '%Y-%m') as month,
                SUM(total) as total,
                AVG(total) as average,
                COUNT(*) as count
         FROM carbon_footprints
         WHERE user_id = ?
         AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
         GROUP BY DATE_FORMAT(date, '%Y-%m')
         ORDER BY month ASC",
        [$_SESSION['user_id']]
    );
    
    // Get category breakdown
    $categoryData = $db->fetchOne(
        "SELECT 
            AVG(transportation) as avg_transportation,
            AVG(energy) as avg_energy,
            AVG(waste) as avg_waste
         FROM carbon_footprints
         WHERE user_id = ?",
        [$_SESSION['user_id']]
    );
    
} catch (Exception $e) {
    error_log($e->getMessage());
    setFlashMessage('error', 'Failed to generate report.');
    redirect('modules/carbon_tracker/history.php');
}

$pageTitle = 'Carbon Footprint Report';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-4">Carbon Footprint Analysis</h2>
                    
                    <?php if (empty($monthlyData)): ?>
                        <div class="alert alert-info">
                            Not enough data to generate a report. Start by <a href="calculate.php">calculating your carbon footprint</a>.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="monthlyChart" height="300"></canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="categoryChart" height="300"></canvas>
                            </div>
                        </div>
                        
                        <div class="row mt-5">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5>Transportation Impact</h5>
                                        <h3 class="text-primary"><?= formatNumber($categoryData['avg_transportation']) ?> km/day</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5>Energy Usage</h5>
                                        <h3 class="text-success"><?= formatNumber($categoryData['avg_energy']) ?> kWh/month</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5>Waste Generation</h5>
                                        <h3 class="text-warning"><?= formatNumber($categoryData['avg_waste']) ?> kg/week</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <h4>Recommendations</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5><i class="fas fa-car text-primary me-2"></i>Transportation</h5>
                                            <ul class="list-unstyled">
                                                <li>Consider carpooling</li>
                                                <li>Use public transport</li>
                                                <li>Try cycling for short distances</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5><i class="fas fa-bolt text-success me-2"></i>Energy</h5>
                                            <ul class="list-unstyled">
                                                <li>Use LED bulbs</li>
                                                <li>Optimize heating/cooling</li>
                                                <li>Unplug unused devices</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5><i class="fas fa-trash text-warning me-2"></i>Waste</h5>
                                            <ul class="list-unstyled">
                                                <li>Start composting</li>
                                                <li>Reduce plastic usage</li>
                                                <li>Recycle properly</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($monthlyData)): ?>
<script>
// Monthly trend chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthlyData, 'month')) ?>,
        datasets: [{
            label: 'Total CO2e (tonnes)',
            data: <?= json_encode(array_column($monthlyData, 'total')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Monthly Carbon Footprint Trend'
            }
        }
    }
});

// Category breakdown chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: ['Transportation', 'Energy', 'Waste'],
        datasets: [{
            data: [
                <?= $categoryData['avg_transportation'] ?>,
                <?= $categoryData['avg_energy'] ?>,
                <?= $categoryData['avg_waste'] ?>
            ],
            backgroundColor: [
                'rgb(54, 162, 235)',
                'rgb(75, 192, 192)',
                'rgb(255, 205, 86)'
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
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>
