<?php
ob_start();
require_once base_path('db/config.php');
require_once base_path('db/functions.php');


$stateId = $_GET['state_id'];
$lgas = [];

$stmt = $pdo->prepare("SELECT id, name FROM local_governments WHERE state_id = ?");
$stmt->execute([$stateId]);
$lgas = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($lgas);




