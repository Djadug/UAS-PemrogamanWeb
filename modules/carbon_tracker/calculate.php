<?php
define('BASEPATH', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
requireLogin();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        
        // Get form data
        $transportation = floatval($_POST['transportation'] ?? 0);
        $energy = floatval($_POST['energy'] ?? 0);
        $waste = floatval($_POST['waste'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');
        
        // Calculate total carbon footprint
        // Using simplified conversion factors
        $total = ($transportation * 0.14) + ($energy * 0.47) + ($waste * 0.11);
        
        // Save to database
        $db->insert('carbon_footprints', [
            'user_id' => $_SESSION['user_id'],
            'transportation' => $transportation,
            'energy' => $energy,
            'waste' => $waste,
            'total' => $total,
            'description' => $description,
            'date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $success = true;
        setFlashMessage('success', 'Carbon footprint calculation saved successfully!');
    } catch (Exception $e) {
        error_log($e->getMessage());
        $errors[] = 'An error occurred while saving your calculation.';
    }
}

$pageTitle = 'Calculate Carbon Footprint';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Calculate Your Carbon Footprint</h2>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Calculation saved successfully! View your <a href="history.php">history</a>.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="carbonCalculatorForm" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label class="form-label">Transportation (km/day)</label>
                            <input type="number" 
                                   class="form-control" 
                                   name="transportation" 
                                   id="transportation" 
                                   step="0.01" 
                                   min="0" 
                                   required>
                            <div class="form-text">Include car, public transport, etc.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Energy Usage (kWh/month)</label>
                            <input type="number" 
                                   class="form-control" 
                                   name="energy" 
                                   id="energy" 
                                   step="0.01" 
                                   min="0" 
                                   required>
                            <div class="form-text">Check your electricity bill</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Waste (kg/week)</label>
                            <input type="number" 
                                   class="form-control" 
                                   name="waste" 
                                   id="waste" 
                                   step="0.01" 
                                   min="0" 
                                   required>
                            <div class="form-text">Estimate your weekly waste</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Description (optional)</label>
                            <textarea class="form-control" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Add any notes about your calculation"></textarea>
                        </div>
                        
                        <div id="carbonResults" class="mb-4"></div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Calculation</button>
                            <button type="button" class="btn btn-secondary" onclick="calculatePreview()">Preview Result</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4 shadow-sm">
                <div class="card-body">
                    <h4>Tips to Reduce Your Carbon Footprint</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-leaf text-success me-2"></i> Use public transportation or bike when possible</li>
                        <li class="mb-2"><i class="fas fa-lightbulb text-warning me-2"></i> Switch to energy-efficient appliances</li>
                        <li class="mb-2"><i class="fas fa-recycle text-primary me-2"></i> Reduce, reuse, and recycle waste</li>
                        <li><i class="fas fa-solar-panel text-info me-2"></i> Consider renewable energy sources</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculatePreview() {
    const transportation = parseFloat(document.getElementById('transportation').value) || 0;
    const energy = parseFloat(document.getElementById('energy').value) || 0;
    const waste = parseFloat(document.getElementById('waste').value) || 0;
    
    const total = (transportation * 0.14) + (energy * 0.47) + (waste * 0.11);
    
    const resultsDiv = document.getElementById('carbonResults');
    resultsDiv.innerHTML = `
        <div class="alert alert-info">
            <h4 class="alert-heading">Estimated Carbon Footprint</h4>
            <p class="mb-0">Your estimated carbon footprint is ${total.toFixed(2)} tonnes CO2e/year</p>
        </div>
    `;
}
</script>

<?php require_once '../../includes/footer.php'; ?>
