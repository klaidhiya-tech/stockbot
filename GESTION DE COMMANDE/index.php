<?php
// Activation de l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'core/RecommandationSystem.php';

// Vérification de la connexion à la base de données
try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo === null) {
        die("Erreur: Impossible de se connecter à la base de données");
    }
    
    $recommandation = new RecommandationSystem($pdo);
    $recommandations = $recommandation->getRecommandations();
    
} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Gestion des Commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-boxes"></i> Gestion Commandes
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-filter"></i> Filtres
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Catégorie</label>
                            <select class="form-select" id="filterCategorie">
                                <option value="">Toutes</option>
                                <?php
                                // Récupérer les catégories distinctes
                                $query = "SELECT DISTINCT nom FROM categories ORDER BY nom";
                                $stmt = $pdo->query($query);
                                $categories = $stmt->fetchAll();
                                foreach ($categories as $categorie) {
                                    echo '<option value="' . htmlspecialchars($categorie['nom']) . '">' . htmlspecialchars($categorie['nom']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fournisseur</label>
                            <select class="form-select" id="filterFournisseur">
                                <option value="">Tous</option>
                                <?php
                                // Récupérer les fournisseurs distincts
                                $query = "SELECT DISTINCT nom FROM fournisseurs ORDER BY nom";
                                $stmt = $pdo->query($query);
                                $fournisseurs = $stmt->fetchAll();
                                foreach ($fournisseurs as $fournisseur) {
                                    echo '<option value="' . htmlspecialchars($fournisseur['nom']) . '">' . htmlspecialchars($fournisseur['nom']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Niveau d'urgence</label>
                            <select class="form-select" id="filterUrgence">
                                <option value="">Tous</option>
                                <option value="critique">Critique</option>
                                <option value="élevé">Élevé</option>
                                <option value="moyen">Moyen</option>
                                <option value="faible">Faible</option>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100" onclick="appliquerFiltres()">
                            <i class="fas fa-check"></i> Appliquer
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-list"></i> État des Stocks (<?= count($recommandations) ?> produits)</span>
                        <div>
                            <button class="btn btn-sm btn-success" onclick="exporterExcel()">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button class="btn btn-sm btn-info" onclick="rafraichir()">
                                <i class="fas fa-sync"></i> Actualiser
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Produit</th>
                                        <th>Stock</th>
                                        <th>Seuil Min</th>
                                        <th>Cons. Moy.</th>
                                        <th>Quantité</th>
                                        <th>Fournisseur</th>
                                        <th>Urgence</th>
                                        <th>Jours Rest.</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recommandations as $cmd): ?>
                                    <tr class="urgence-<?= $cmd['urgence'] ?>">
                                        <td><?= htmlspecialchars($cmd['code_produit']) ?></td>
                                        <td><?= htmlspecialchars($cmd['nom']) ?></td>
                                        <td><?= $cmd['stock_actuel'] ?></td>
                                        <td><?= $cmd['seuil_min'] ?></td>
                                        <td><?= round($cmd['consommation_moyenne'], 2) ?></td>
                                        <td>
                                            <?php if ($cmd['quantite_recommandee'] > 0): ?>
                                                <strong><?= $cmd['quantite_recommandee'] ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($cmd['fournisseur']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $cmd['urgence'] === 'critique' ? 'danger' : 
                                                ($cmd['urgence'] === 'élevé' ? 'warning' : 
                                                ($cmd['urgence'] === 'moyen' ? 'info' : 'success')) 
                                            ?>">
                                                <?= ucfirst($cmd['urgence']) ?>
                                            </span>
                                        </td>
                                        <td><?= $cmd['jours_restants'] ?></td>
                                        <td>
                                            <?php if ($cmd['quantite_recommandee'] > 0): ?>
                                                <button class="btn btn-sm btn-primary" 
                                                        onclick="commander(<?= $cmd['id'] ?>, <?= $cmd['quantite_recommandee'] ?>)">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>