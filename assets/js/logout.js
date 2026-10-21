/**
 * Script de déconnexion - Intégré avec le backend
 * Fichier: assets/js/logout.js
 */

/**
 * Fonction de déconnexion
 */
async function logout() {
    try {
        // Afficher un message de chargement
        if (typeof notifyInfo === 'function') {
            notifyInfo('Déconnexion en cours...');
        }

        // Appeler l'API de déconnexion
        const result = await AuthService.logout();

        // Nettoyer le localStorage
        localStorage.removeItem('user');
        localStorage.removeItem('isAuthenticated');

        if (result.success) {
            if (typeof notifySuccess === 'function') {
                notifySuccess('Déconnexion réussie');
            }
        }

        // Rediriger vers la page de connexion
        setTimeout(() => {
            window.location.href = '../utilisateur/login.html';
        }, 1000);

    } catch (error) {
        console.error('Erreur de déconnexion:', error);
        
        // Même en cas d'erreur, on nettoie le localStorage et on redirige
        localStorage.removeItem('user');
        localStorage.removeItem('isAuthenticated');
        
        if (typeof notifyWarning === 'function') {
            notifyWarning('Déconnexion locale effectuée');
        }
        
        setTimeout(() => {
            window.location.href = '../utilisateur/login.html';
        }, 1000);
    }
}

// Attacher la fonction de déconnexion aux liens de déconnexion
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les liens/boutons de déconnexion
    const logoutButtons = document.querySelectorAll('[href*="login.html"]');
    
    logoutButtons.forEach(button => {
        // Vérifier si c'est un bouton de déconnexion (texte contient "Déconnexion" ou classe bg-red)
        const isLogoutButton = button.textContent.includes('Déconnexion') || 
                              button.textContent.includes('déconnexion') ||
                              button.classList.contains('bg-red-600') ||
                              button.classList.contains('bg-red-500');
        
        if (isLogoutButton) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                logout();
            });
        }
    });
});

// Export global
window.logout = logout;