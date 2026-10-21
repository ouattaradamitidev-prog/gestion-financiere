/**
 * Script pour le formulaire de transaction - Intégré avec backend PHP
 * Fichier: utilisateur/js/transaction_form.js
 */

// Définir la date minimum comme aujourd'hui
document.getElementById('dateNecessaire').min = new Date().toISOString().split('T')[0];

// Afficher/masquer l'avertissement d'urgence
document.getElementById('urgence').addEventListener('change', function() {
    const warning = document.getElementById('urgenceWarning');
    if (this.value === 'urgente') {
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }
});

/**
 * Soumettre la transaction au backend
 */
async function submitTransaction() {
    // Récupérer les valeurs
    const typeRadio = document.querySelector('input[name="type"]:checked');
    const montant = document.getElementById('montant').value;
    const dateNecessaire = document.getElementById('dateNecessaire').value;
    const urgence = document.getElementById('urgence').value;
    const motif = document.getElementById('motif').value;

    // Validation côté client
    if (!typeRadio) {
        if (typeof notifyError === 'function') {
            notifyError('Veuillez sélectionner un type d\'opération');
        } else {
            alert('Veuillez sélectionner un type d\'opération');
        }
        return;
    }

    if (!montant || !dateNecessaire || !motif) {
        if (typeof notifyError === 'function') {
            notifyError('Veuillez remplir tous les champs obligatoires');
        } else {
            alert('Veuillez remplir tous les champs obligatoires');
        }
        return;
    }

    if (parseFloat(montant) <= 0) {
        if (typeof notifyError === 'function') {
            notifyError('Le montant doit être supérieur à 0');
        } else {
            alert('Le montant doit être supérieur à 0');
        }
        return;
    }

    if (motif.length < 10) {
        if (typeof notifyError === 'function') {
            notifyError('Le motif doit contenir au moins 10 caractères');
        } else {
            alert('Le motif doit contenir au moins 10 caractères');
        }
        return;
    }

    // Afficher le chargement
    if (typeof notifyInfo === 'function') {
        notifyInfo('Envoi de la transaction...');
    }

    // Préparer les données de la transaction
    const transactionData = {
        type: typeRadio.value,
        montant: parseFloat(montant),
        motif: motif.trim(),
        date_necessaire: dateNecessaire,
        urgence: urgence
    };

    try {
        // Appel API pour créer la transaction
        const result = await TransactionService.create(transactionData);

        if (result.success) {
            // Afficher le message de succès
            const successMsg = document.getElementById('successMessage');
            const successDetails = document.getElementById('successDetails');
            successDetails.textContent = `Votre demande de ${transactionData.type} de ${parseInt(transactionData.montant).toLocaleString()} FCFA est en attente de validation.`;
            successMsg.classList.remove('hidden');

            if (typeof notifySuccess === 'function') {
                notifySuccess('Transaction soumise avec succès !');
            }

            // Réinitialiser le formulaire
            document.querySelector('input[name="type"]:checked').checked = false;
            document.getElementById('montant').value = '';
            document.getElementById('dateNecessaire').value = '';
            document.getElementById('urgence').value = 'normale';
            document.getElementById('motif').value = '';
            document.getElementById('urgenceWarning').classList.add('hidden');

            // Rediriger après 2 secondes
            setTimeout(() => {
                window.location.href = './mes_transactions.html';
            }, 2000);

        } else {
            // Erreur lors de la création
            if (typeof notifyError === 'function') {
                notifyError(result.message || 'Erreur lors de la création de la transaction');
            } else {
                alert(result.message || 'Erreur lors de la création de la transaction');
            }

            // Afficher les erreurs de validation si présentes
            if (result.errors && Array.isArray(result.errors)) {
                result.errors.forEach(error => {
                    if (typeof notifyError === 'function') {
                        notifyError(error);
                    }
                });
            }
        }
    } catch (error) {
        console.error('Erreur:', error);
        if (typeof notifyError === 'function') {
            notifyError('Erreur de connexion au serveur. Vérifiez que le backend est démarré.');
        } else {
            alert('Erreur de connexion au serveur');
        }
    }
}

// Vérifier l'authentification au chargement de la page
window.addEventListener('DOMContentLoaded', async () => {
    const isAuthenticated = localStorage.getItem('isAuthenticated');
    
    if (isAuthenticated !== 'true') {
        // Rediriger vers la page de connexion si non authentifié
        if (typeof notifyWarning === 'function') {
            notifyWarning('Vous devez être connecté pour accéder à cette page');
        }
        setTimeout(() => {
            window.location.href = './login.html';
        }, 1500);
    } else {
        // Vérifier la session côté serveur
        try {
            const sessionCheck = await AuthService.checkSession();
            
            if (!sessionCheck.success || !sessionCheck.authenticated) {
                // Session expirée
                localStorage.removeItem('user');
                localStorage.removeItem('isAuthenticated');
                
                if (typeof notifyWarning === 'function') {
                    notifyWarning('Session expirée. Veuillez vous reconnecter.');
                }
                
                setTimeout(() => {
                    window.location.href = './login.html';
                }, 1500);
            }
        } catch (error) {
            console.error('Erreur vérification session:', error);
        }
    }
});