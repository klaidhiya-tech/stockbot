function commander(produitId, quantite) {
    if (!confirm(`Confirmer la commande de ${quantite} unités ?`)) return;

    fetch('api/commander.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            produit_id: produitId,
            quantite: quantite
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Commande créée avec succès !');
            location.reload();
        } else {
            alert('Erreur: ' + data.error);
        }
    })
    .catch(error => {
        alert('Erreur réseau: ' + error.message);
    });
}

function appliquerFiltres() {
    const categorie = document.getElementById('filterCategorie').value;
    const fournisseur = document.getElementById('filterFournisseur').value;
    const urgence = document.getElementById('filterUrgence').value;
    
    const lignes = document.querySelectorAll('tbody tr');
    
    lignes.forEach(ligne => {
        let afficher = true;
        const celluleCategorie = ligne.cells[1].textContent;
        const celluleFournisseur = ligne.cells[6].textContent;
        const celluleUrgence = ligne.classList.contains('urgence-' + urgence) || !urgence;
        
        if (categorie && !celluleCategorie.includes(categorie)) afficher = false;
        if (fournisseur && !celluleFournisseur.includes(fournisseur)) afficher = false;
        if (urgence && !celluleUrgence) afficher = false;
        
        ligne.style.display = afficher ? '' : 'none';
    });
}

function rafraichir() {
    location.reload();
}

function exporterExcel() {
    // Fonction simplifiée pour l'export Excel
    let csv = [];
    const rows = document.querySelectorAll('table tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let text = cols[j].textContent;
            // Nettoyer le texte pour CSV
            text = text.replace(/"/g, '""');
            row.push('"' + text + '"');
        }
        csv.push(row.join(','));
    }
    
    // Télécharger le fichier CSV
    let csvString = csv.join('\n');
    let a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvString);
    a.target = '_blank';
    a.download = 'etat-stocks.csv';
    a.click();
}

// Charger les filtres disponibles
document.addEventListener('DOMContentLoaded', function() {
    const categories = new Set();
    const fournisseurs = new Set();
    
    document.querySelectorAll('tbody tr').forEach(ligne => {
        categories.add(ligne.cells[1].textContent);
        fournisseurs.add(ligne.cells[6].textContent);
    });
    
    const selectCategorie = document.getElementById('filterCategorie');
    const selectFournisseur = document.getElementById('filterFournisseur');
    
    categories.forEach(categorie => {
        if (categorie) {
            const option = document.createElement('option');
            option.value = categorie;
            option.textContent = categorie;
            selectCategorie.appendChild(option);
        }
    });
    
    fournisseurs.forEach(fournisseur => {
        if (fournisseur) {
            const option = document.createElement('option');
            option.value = fournisseur;
            option.textContent = fournisseur;
            selectFournisseur.appendChild(option);
        }
    });
});