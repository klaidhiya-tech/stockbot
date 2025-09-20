<?php
// Fichier de diagnostic
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Diagnostic du Système</h2>";

// Test de connexion à la base de données
require_once 'config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo === null) {
        die("Erreur: La connexion à la base de données est null");
    }
    
    echo "<div style='color: green;'>✓ Connexion à la base de données réussie</div>";
    
    // Test de la table produits
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM produits");
    $result = $stmt->fetch();
    echo "<div style='color: green;'>✓ Table 'produits' accessible (" . $result['count'] . " produits)</div>";
    
    // Test de la table fournisseurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM fournisseurs");
    $result = $stmt->fetch();
    echo "<div style='color: green;'>✓ Table 'fournisseurs' accessible (" . $result['count'] . " fournisseurs)</div>";
    
    // Test de la table categories
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $result = $stmt->fetch();
    echo "<div style='color: green;'>✓ Table 'categories' accessible (" . $result['count'] . " catégories)</div>";
    
    // Test du système de recommandation
    require_once 'core/RecommandationSystem.php';
    $recommandation = new RecommandationSystem($pdo);
    $recommandations = $recommandation->getRecommandations();
    
    echo "<div style='color: green;'>✓ Système de recommandation fonctionnel (" . count($recommandations) . " recommandations)</div>";
    
    // Affichage de quelques recommandations
    echo "<h3>Exemples de recommandations:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Produit</th><th>Stock</th><th>Seuil Min</th><th>Quantité</th><th>Urgence</th></tr>";
    
    $count = 0;
    foreach ($recommandations as $cmd) {
        if ($count++ >= 5) break;
        echo "<tr>";
        echo "<td>" . htmlspecialchars($cmd['nom']) . "</td>";
        echo "<td>" . $cmd['stock_actuel'] . "</td>";
        echo "<td>" . $cmd['seuil_min'] . "</td>";
        echo "<td>" . $cmd['quantite_recommandee'] . "</td>";
        echo "<td>" . $cmd['urgence'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Erreur: " . $e->getMessage() . "</div>";
}
?>