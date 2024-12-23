<?php
// Memuat koneksi database dan fungsi utama
define('BASEPATH', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
// Load helpers
require_once __DIR__ . '/../includes/helpers.php';

// Load includes
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';



try {
    // Initialize database connection
    $db = Database::getInstance();
    
    // Test connection
    Database::testConnection();
}   
  catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Konten Utama
echo "<div class='container mt-5'>";
echo "<h1>Daftar Pengguna</h1>";

$query = "SELECT * FROM users";
$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<li>Nama: " . $row['name'] . " | Email: " . $row['email'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "0 hasil";
}
echo "</div>";

// Memuat footer
require_once __DIR__ . '/../includes/footer.php';

?>
