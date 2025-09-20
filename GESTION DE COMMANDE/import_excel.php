<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier_excel'])) {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $file = $_FILES['fichier_excel']['tmp_name'];
    
    // Lire le fichier Excel
    require_once 'vendor/autoload.php';
    
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();
        
        // Ignorer l'en-tête
        array_shift($data);
        
        $imported = 0;
        $errors = 0;
        
        foreach ($data as $row) {
            if (count($row) >= 6) {
                try {
                    // Traiter chaque ligne
                    $famille = $row[0] ?? '';
                    $fournisseur_nom = $row[1] ?? '';
                    $code_fournisseur = $row[2] ?? '';
                    $code_produit = $row[3] ?? '';
                    $nom = $row[4] ?? '';
                    $code_barre = $row[5] ?? '';
                    $stock_actuel = $row[6] ?? 0;
                    
                    // Trouver ou créer le fournisseur
                    $stmt = $pdo->prepare("SELECT id FROM fournisseurs WHERE code_fournisseur = ?");
                    $stmt->execute([$code_fournisseur]);
                    $fournisseur = $stmt->fetch();
                    
                    if (!$fournisseur) {
                        $stmt = $pdo->prepare("INSERT INTO fournisseurs (code_fournisseur, nom) VALUES (?, ?)");
                        $stmt->execute([$code_fournisseur, $fournisseur_nom]);
                        $fournisseur_id = $pdo->lastInsertId();
                    } else {
                        $fournisseur_id = $fournisseur['id'];
                    }
                    
                    // Trouver ou créer la catégorie
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE nom = ?");
                    $stmt->execute([$famille]);
                    $categorie = $stmt->fetch();
                    
                    if (!$categorie) {
                        $stmt = $pdo->prepare("INSERT INTO categories (nom) VALUES (?)");
                        $stmt->execute([$famille]);
                        $categorie_id = $pdo->lastInsertId();
                    } else {
                        $categorie_id = $categorie['id'];
                    }
                    
                    // Insérer ou mettre à jour le produit
                    $query = "
                        INSERT INTO produits (code_produit, code_barre, nom, categorie_id, stock_actuel, fournisseur_id)
                        VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        nom = VALUES(nom),
                        code_barre = VALUES(code_barre),
                        stock_actuel = VALUES(stock_actuel),
                        categorie_id = VALUES(categorie_id),
                        fournisseur_id = VALUES(fournisseur_id)
                    ";
                    
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$code_produit, $code_barre, $nom, $categorie_id, $stock_actuel, $fournisseur_id]);
                    
                    $imported++;
                } catch (Exception $e) {
                    $errors++;
                }
            }
        }
        
        echo "<div class='alert alert-success'>Import réussi: $imported produits importés, $errors erreurs</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Erreur lors de l'importation: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importation Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Importation de données Excel</h2>
        
        <div class="card">
            <div class="card-header">
                Importer des produits depuis Excel
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="fichier_excel" class="form-label">Fichier Excel</label>
                        <input type="file" class="form-control" id="fichier_excel" name="fichier_excel" accept=".xlsx,.xls" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <h4>Format attendu</h4>
            <div class="alert alert-info">
                Le fichier Excel doit contenir les colonnes suivantes dans cet ordre :
                <ol>
                    <li>Famille</li>
                    <li>Fournisseur</li>
                    <li>Code Fournisseur</li>
                    <li>ID Produit</li>
                    <li>Désignation</li>
                    <li>Code Barre</li>
                    <li>Stock Actuel</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>