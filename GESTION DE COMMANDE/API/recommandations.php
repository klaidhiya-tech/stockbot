<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../core/RecommandationSystem.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $system = new RecommandationSystem($pdo);
    $recommandations = $system->getRecommandations();
    
    echo json_encode([
        'success' => true,
        'data' => $recommandations,
        'count' => count($recommandations),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
