// Document Ready
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    initializeCharts();
    setupFormValidation();
    setupCarbonCalculator();
});

// Initialize Bootstrap Tooltips
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Carbon Calculator Functions
const setupCarbonCalculator = () => {
    const calculatorForm = document.getElementById('carbonCalculatorForm');
    if (calculatorForm) {
        calculatorForm.addEventListener('submit', calculateCarbonFootprint);
    }
}

function calculateCarbonFootprint(e) {
    e.preventDefault();
    
    const transportation = parseFloat(document.getElementById('transportation').value) || 0;
    const energy = parseFloat(document.getElementById('energy').value) || 0;
    const waste = parseFloat(document.getElementById('waste').value) || 0;
    
    const total = (transportation * 0.14) + (energy * 0.47) + (waste * 0.11);
    
    updateCarbonResults(total);
}

function updateCarbonResults(total) {
    const resultDiv = document.getElementById('carbonResults');
    if (resultDiv) {
        resultDiv.innerHTML = `
            <div class="alert alert-info">
                <h4>Your Carbon Footprint</h4>
                <p>Total: ${total.toFixed(2)} tonnes CO2e/year</p>
            </div>
        `;
    }
}

// Charts Initialization (using Chart.js)
function initializeCharts() {
    setupProgressChart();
    setupTrendsChart();
}

function setupProgressChart() {
    const ctx = document.getElementById('progressChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Remaining'],
                datasets: [{
                    data: [65, 35],
                    backgroundColor: [
                        'var(--primary-color)',
                        'var(--light-color)'
                    ]
                }]
            },
            options: {
                responsive: true,
                cutout: '70%'
            }
        });
    }
}

function setupTrendsChart() {
    const ctx = document.getElementById('trendsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Carbon Footprint',
                    data: [12, 19, 15, 17, 14, 13],
                    borderColor: 'var(--primary-color)',
                    tension: 0.4
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
    }
}

// Form Validation
function setupFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// AJAX Functions
async function fetchUserData(userId) {
    try {
        const response = await fetch(`/api/users/${userId}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching user data:', error);
        return null;
    }
}

// Challenge Progress Update
function updateChallengeProgress(challengeId, progress) {
    fetch('/api/challenges/progress', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            challengeId,
            progress
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateProgressUI(challengeId, progress);
        }
    })
    .catch(error => console.error('Error updating progress:', error));
}

// UI Updates
function updateProgressUI(challengeId, progress) {
    const progressBar = document.querySelector(`#challenge-${challengeId} .progress-bar-eco`);
    if (progressBar) {
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);
    }
}

// Notifications
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.getElementById('notifications').appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
} 