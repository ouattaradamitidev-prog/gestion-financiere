/**
 * Script pour la page de profil utilisateur - Intégré avec le backend
 * Fichier: utilisateur/js/profil.js
 */

document.addEventListener('DOMContentLoaded', async function() {
    // Vérifier l'authentification
    await checkAuthentication();
    
    // Charger les informations du profil
    await loadUserProfile();
    
    // Attacher les événements
    attachEventListeners();
});

/**
 * Vérifier si l'utilisateur est authentifié
 */
async function checkAuthentication() {
    try {
        const result = await AuthService.checkSession();
        
        if (!result.success || !result.authenticated) {
            notifyError('Session expirée. Veuillez vous reconnecter.');
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
            return;
        }
        
        // Stocker les infos utilisateur
        localStorage.setItem('user', JSON.stringify(result.user));
        
        // Afficher le nom dans l'interface
        updateUserDisplay(result.user);
        
    } catch (error) {
        console.error('Erreur de vérification de session:', error);
        notifyError('Erreur de connexion au serveur');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 2000);
    }
}

/**
 * Charger les informations du profil
 */
async function loadUserProfile() {
    try {
        showLoader('profile-loader');
        
        const result = await UserService.getProfile();
        
        if (result.success) {
            // Remplir le formulaire avec les données
            document.getElementById('username').value = result.user.username || '';
            document.getElementById('nom').value = result.user.nom || '';
            document.getElementById('email').value = result.user.email || '';
            document.getElementById('role').value = result.user.role || '';
            
            // Afficher la date de création
            if (result.user.created_at) {
                const date = new Date(result.user.created_at);
                document.getElementById('member-since').textContent = 
                    'Membre depuis ' + date.toLocaleDateString('fr-FR', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
            }
            
            notifySuccess('Profil chargé');
        } else {
            throw new Error(result.message || 'Erreur de chargement du profil');
        }
    } catch (error) {
        console.error('Erreur de chargement du profil:', error);
        notifyError('Impossible de charger le profil: ' + error.message);
    } finally {
        hideLoader('profile-loader');
    }
}

/**
 * Mettre à jour le profil utilisateur
 */
async function updateProfile(event) {
    event.preventDefault();
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Désactiver le bouton
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mise à jour...';
        
        // Récupérer les données du formulaire
        const formData = {
            nom: document.getElementById('nom').value.trim(),
            email: document.getElementById('email').value.trim()
        };
        
        // Validation côté client
        if (formData.nom.length < 2) {
            throw new Error('Le nom doit contenir au moins 2 caractères');
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(formData.email)) {
            throw new Error('Email invalide');
        }
        
        // Envoyer la mise à jour
        const result = await UserService.updateProfile(formData);
        
        if (result.success) {
            notifySuccess('Profil mis à jour avec succès');
            
            // Mettre à jour le localStorage
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            user.nom = formData.nom;
            user.email = formData.email;
            localStorage.setItem('user', JSON.stringify(user));
            
            // Mettre à jour l'affichage
            updateUserDisplay(user);
        } else {
            throw new Error(result.message || 'Erreur de mise à jour');
        }
        
    } catch (error) {
        console.error('Erreur de mise à jour du profil:', error);
        notifyError(error.message);
    } finally {
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

/**
 * Changer le mot de passe
 */
async function changePassword(event) {
    event.preventDefault();
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Désactiver le bouton
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Changement...';
        
        // Récupérer les données
        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        
        // Validation côté client
        if (newPassword.length < 8) {
            throw new Error('Le nouveau mot de passe doit contenir au moins 8 caractères');
        }
        
        if (newPassword !== confirmPassword) {
            throw new Error('Les mots de passe ne correspondent pas');
        }
        
        if (currentPassword === newPassword) {
            throw new Error('Le nouveau mot de passe doit être différent de l\'ancien');
        }
        
        // Envoyer la demande
        const result = await UserService.changePassword(currentPassword, newPassword);
        
        if (result.success) {
            notifySuccess('Mot de passe changé avec succès');
            
            // Réinitialiser le formulaire
            event.target.reset();
            
            // Déconnecter après 2 secondes
            setTimeout(() => {
                notifyInfo('Reconnexion requise...');
                logout();
            }, 2000);
        } else {
            throw new Error(result.message || 'Erreur de changement de mot de passe');
        }
        
    } catch (error) {
        console.error('Erreur de changement de mot de passe:', error);
        notifyError(error.message);
        
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

/**
 * Mettre à jour l'affichage du nom utilisateur
 */
function updateUserDisplay(user) {
    const userNameElements = document.querySelectorAll('.user-name');
    userNameElements.forEach(el => {
        el.textContent = user.nom || user.username;
    });
}

/**
 * Attacher les événements
 */
function attachEventListeners() {
    // Formulaire de mise à jour du profil
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', updateProfile);
    }
    
    // Formulaire de changement de mot de passe
    const passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', changePassword);
    }
    
    // Vérification des mots de passe en temps réel
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const passwordMatch = document.getElementById('password-match');
    
    if (confirmPasswordInput && passwordMatch) {
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value === '') {
                passwordMatch.textContent = '';
                return;
            }
            
            if (this.value === newPasswordInput.value) {
                passwordMatch.textContent = '✓ Les mots de passe correspondent';
                passwordMatch.className = 'text-sm text-green-600 mt-1';
            } else {
                passwordMatch.textContent = '✗ Les mots de passe ne correspondent pas';
                passwordMatch.className = 'text-sm text-red-600 mt-1';
            }
        });
    }
}

/**
 * Afficher le loader
 */
function showLoader(loaderId) {
    const loader = document.getElementById(loaderId);
    if (loader) {
        loader.classList.remove('hidden');
    }
}

/**
 * Masquer le loader
 */
function hideLoader(loaderId) {
    const loader = document.getElementById(loaderId);
    if (loader) {
        loader.classList.add('hidden');
    }
}