<?php
class RecommandationSystem {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getRecommandations() {
        $produits = $this->getProduitsActifs();
        $recommandations = [];
        
        foreach ($produits as $produit) {
            $recommandation = $this->analyserProduit($produit);
            
          
                $recommandations[] = $recommandation;
          
        }
        
        return $recommandations;
    }
    
    private function getProduitsActifs() {
        $query = "
            SELECT p.*, c.nom as categorie_nom, f.nom as fournisseur_nom, f.code_fournisseur
            FROM produits p
            LEFT JOIN categories c ON p.categorie_id = c.id
            LEFT JOIN fournisseurs f ON p.fournisseur_id = f.id
            WHERE p.actif = true
            ORDER BY p.nom
        ";
        
        $stmt = $this->pdo->query($query);
        if ($stmt === false) {
            throw new Exception("Erreur lors de l'exécution de la requête: " . implode(" ", $this->pdo->errorInfo()));
        }
        
        return $stmt->fetchAll();
    }
    
    private function analyserProduit($produit) {
        $besoin = $this->calculerBesoin($produit);
        $quantite = $this->calculerQuantite($produit, $besoin);
        
    
        $urgence = $this->calculerUrgence($produit);
        $jours_restants = $this->calculerJoursRestants($produit);
        
        return [
            'id' => $produit['id'],
            'code_produit' => $produit['code_produit'],
            'code_barre' => $produit['code_barre'],
            'nom' => $produit['nom'],
            'categorie' => $produit['categorie_nom'],
            'stock_actuel' => $produit['stock_actuel'],
            'seuil_min' => $produit['seuil_min'],
            'seuil_max' => $produit['seuil_max'],
            'consommation_moyenne' => $produit['consommation_moyenne'],
            'consommation_maximale' => $produit['consommation_maximale'],
            'besoin_calcule' => $besoin,
            'quantite_recommandee' => $quantite,
            'fournisseur' => $produit['fournisseur_nom'],
            'code_fournisseur' => $produit['code_fournisseur'],
            'prix_achat' => $produit['prix_achat'],
            'prix_estime' => $produit['prix_achat'] * $quantite,
            'urgence' => $urgence,
            'jours_restants' => $jours_restants
        ];
    }
    
    private function calculerBesoin($produit) {
        $delai = $produit['delai_livraison'] ?: 7;
        $consommation = $produit['consommation_moyenne'] ?: 1; 
        $consommation_journaliere = $consommation / 30;
        
        return $consommation_journaliere * $delai * (1 + ($produit['marge_securite'] ?: 0.2));
    }
    
    private function calculerQuantite($produit, $besoin) {
        $quantite = max(0, $besoin - $produit['stock_actuel']);
        return min($quantite, $produit['seuil_max'] - $produit['stock_actuel']);
    }
    
    private function calculerUrgence($produit) {
        if ($produit['seuil_min'] <= 0) return 'faible'; 
        
        $ratio = $produit['stock_actuel'] / $produit['seuil_min'];
        if ($ratio <= 0.2) return 'critique';
        if ($ratio <= 0.5) return 'élevé';
        if ($ratio <= 0.8) return 'moyen';
        return 'faible';
    }
    
    private function calculerJoursRestants($produit) {
        if ($produit['consommation_moyenne'] <= 0) return 999;
        $consommation_journaliere = $produit['consommation_moyenne'] / 30;
        return $consommation_journaliere > 0 ? floor($produit['stock_actuel'] / $consommation_journaliere) : 999;
    }
}
?>