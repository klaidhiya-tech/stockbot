<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $database = new Database();
        $pdo = $database->getConnection();
        
       
        $numero_commande = 'CMD-' . date('Ymd-His');
        
        $stmt = $pdo->prepare("
            INSERT INTO historique_commandes 
            (numero_commande, produit_id, quantite_commandee, date_commande, statut)
            VALUES (?, ?, ?, CURDATE(), 'en_attente')
        ");
        
        $stmt->execute([
            $numero_commande,
            $input['produit_id'],
            $input['quantite']
        ]);
        
        echo json_encode([
            'success' => true,
            'numero_commande' => $numero_commande,
            'message' => 'Commande créée avec succès'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Méthode non autorisée'
    ]);
}
?>