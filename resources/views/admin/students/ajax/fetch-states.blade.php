<?php
ob_start();
require_once base_path('db/config.php');
require_once base_path('db/functions.php');
// Fetch states based on the selected country

$countryId = $_GET['country_id'];
$countryName = strtolower(trim($_GET['country_name'] ?? ''));
$data = [];

if ($countryName === 'nigeria') {
    // Fetch from 'state' table
    $stmt = $pdo->prepare("SELECT id, name FROM state ORDER BY name ASC");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fetch from 'states' table
    $stmt = $pdo->prepare("SELECT id, name FROM states WHERE country_id = ? ORDER BY name ASC");
    $stmt->execute([$countryId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

header('Content-Type: application/json');
echo json_encode($data);



