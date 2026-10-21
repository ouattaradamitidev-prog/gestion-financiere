/**
 * Script de connexion - Intégré avec le backend PHP
 * Fichier: utilisateur/js/login.js
 */

// Gestion de la sélection du type de compte
function selectAccountType(type) {
    const userBtn = document.getElementById('btn-user');
    const adminBtn = document.getElementById('btn-admin');
    const accountTypeInput = document.getElementById('accountType');

    if (type === 'user') {
        userBtn.classList.remove('border-gray-300', 'bg-white', 'text-gray-700');
        userBtn.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
        adminBtn.classList.remove('border-green-500', 'bg-green-50', 'text-green-700');
        adminBtn.classList.add('border-gray-300', 'bg-white', 'text-gray-700');
    } else {
        adminBtn.classList.remove('border-gray-300', 'bg-white', 'text-gray-700');
        adminBtn.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
        userBtn.classList.remove('border-green-500', 'bg-green-50', 'text-green-700');
        userBtn.classList.add('border-gray-300', 'bg-white', 'text-gray-700');
    }

    accountTypeInput.value = type;
}

// Gestion du formulaire de connexion avec backend réel
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const login = document.getElementById('login').value.trim();
    const password = document.getElementById('password').value;
    const accountType = document.getElementById('accountType').value;
    const remember = document.getElementById('remember').checked;
    const loginBtn = this.querySelector('button[type="submit"]');

    // Validation
    if (!login || !password) {
        if (typeof notifyError === 'function') {
            notifyError('Veuillez remplir tous les champs');
        } else {
            alert('Veuillez remplir tous les champs');
        }
        return;
    }

    // Désactiver le bouton et afficher le chargement
    loginBtn.disabled = true;
    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Connexion en cours...';

    if (typeof notifyInfo === 'function') {
        notifyInfo('Connexion en cours...');
    }

    try {
        // Appel API de connexion
        const result = await AuthService.login(login, password);

        if (result.success) {
            // Stocker les informations utilisateur dans localStorage
            localStorage.setItem('user', JSON.stringify(result.user));
            localStorage.setItem('isAuthenticated', 'true');
            
            if (typeof notifySuccess === 'function') {
                notifySuccess(`Connexion réussie en tant que ${result.user.role} !`);
            }

            // Redirection selon le rôle
            setTimeout(() => {
                if (result.user.role === 'Administrateur') {
                    window.location.href = '../admin/dashboard_admin.html';
                } else {
                    window.location.href = './mes_transactions.html';
                }
            }, 1000);
        } else {
            // Erreur de connexion
            loginBtn.disabled = false;
            loginBtn.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Se connecter';
            
            if (typeof notifyError === 'function') {
                notifyError(result.message || 'Identifiants invalides');
            } else {
                alert(result.message || 'Identifiants invalides');
            }
        }
    } catch (error) {
        console.error('Erreur de connexion:', error);
        loginBtn.disabled = false;
        loginBtn.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Se connecter';
        
        if (typeof notifyError === 'function') {
            notifyError('Erreur de connexion au serveur. Vérifiez que le backend est démarré.');
        } else {
            alert('Erreur de connexion au serveur');
        }
    }
});

// Vérifier si déjà connecté au chargement
window.addEventListener('DOMContentLoaded', async () => {
    const isAuthenticated = localStorage.getItem('isAuthenticated');
    const user = localStorage.getItem('user');
    
    if (isAuthenticated === 'true' && user) {
        const userData = JSON.parse(user);
        
        // Vérifier la session côté serveur
        try {
            const sessionCheck = await AuthService.checkSession();
            
            if (sessionCheck.success && sessionCheck.authenticated) {
                // Rediriger vers la bonne page
                if (userData.role === 'Administrateur') {
                    window.location.href = '../admin/dashboard_admin.html';
                } else {
                    window.location.href = './mes_transactions.html';
                }
            } else {
                // Session expirée, nettoyer le localStorage
                localStorage.removeItem('user');
                localStorage.removeItem('isAuthenticated');
            }
        } catch (error) {
            console.error('Erreur vérification session:', error);
        }
    }
});