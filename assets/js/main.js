// Fichier JavaScript principal pour l'application de gestion financière

 
// UTILITAIRES GÉNÉRAUX
 

/**
 * Formatte un nombre en devise FCFA
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0
    }).replace('XOF', 'FCFA');
}

/**
 * Formatte une date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Valide un email
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Valide un numéro de téléphone
 */
function isValidPhone(phone) {
    const regex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
    return regex.test(phone.replace(/\s/g, ''));
}

/**
 * Échappe les caractères HTML pour éviter les injections XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

 
// GESTION DES MESSAGES ET NOTIFICATIONS
 

/**
 * Affiche un message de notification
 */
function showMessage(message, type = 'info', duration = 3000) {
    const messageDiv = document.getElementById('message') || createMessageContainer();
    
    const icons = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
        info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
    };
    
    const colors = {
        success: 'bg-green-100 border border-green-300 text-green-800',
        error: 'bg-red-100 border border-red-300 text-red-800',
        warning: 'bg-yellow-100 border border-yellow-300 text-yellow-800',
        info: 'bg-blue-100 border border-blue-300 text-blue-800'
    };
    
    messageDiv.className = `p-4 rounded-lg ${colors[type]} animate-fadeIn`;
    messageDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${icons[type]}
            </svg>
            <span class="font-semibold">${escapeHtml(message)}</span>
        </div>
    `;
    messageDiv.classList.remove('hidden');
    
    if (duration > 0) {
        setTimeout(() => {
            messageDiv.classList.add('hidden');
        }, duration);
    }
}

/**
 * Crée un conteneur de message s'il n'existe pas
 */
function createMessageContainer() {
    const container = document.createElement('div');
    container.id = 'message';
    container.className = 'hidden fixed top-4 right-4 z-50 max-w-md';
    document.body.appendChild(container);
    return container;
}

 
// VALIDATION DES FORMULAIRES
 

/**
 * Valide un formulaire de transaction
 */
function validateTransactionForm(formData) {
    const errors = [];
    
    if (!formData.type) {
        errors.push('Le type de transaction est requis');
    }
    
    if (!formData.montant || formData.montant <= 0) {
        errors.push('Le montant doit être supérieur à 0');
    }
    
    if (!formData.motif || formData.motif.trim().length < 10) {
        errors.push('Le motif doit contenir au moins 10 caractères');
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
}

/**
 * Valide un formulaire d'utilisateur
 */
function validateUserForm(formData) {
    const errors = [];
    
    if (!formData.nom || formData.nom.trim().length < 3) {
        errors.push('Le nom doit contenir au moins 3 caractères');
    }
    
    if (!isValidEmail(formData.email)) {
        errors.push('Email invalide');
    }
    
    if (!isValidPhone(formData.tel)) {
        errors.push('Numéro de téléphone invalide');
    }
    
    if (formData.password && formData.password.length < 6) {
        errors.push('Le mot de passe doit contenir au moins 6 caractères');
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
}

 
// GESTION DES MODALS
 

/**
 * Ouvre un modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Ferme un modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

/**
 * Ferme un modal en cliquant à l'extérieur
 */
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
});

 
// GESTION DE LA SESSION ET DÉCONNEXION
 

/**
 * Déconnexion automatique après inactivité
 */
let inactivityTimer;
const INACTIVITY_TIMEOUT = 30 * 60 * 1000; // 30 minutes

function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(() => {
        showMessage('Session expirée pour inactivité', 'warning', 5000);
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 2000);
    }, INACTIVITY_TIMEOUT);
}

// Réinitialiser le timer sur les interactions utilisateur
['mousedown', 'keypress', 'scroll', 'touchstart'].forEach(event => {
    document.addEventListener(event, resetInactivityTimer, true);
});

// Initialiser le timer au chargement
resetInactivityTimer();

/**
 * Vérifie si l'utilisateur est connecté (simulation)
 */
function checkAuthentication() {
    // Dans une vraie application, cela vérifierait le token de session
    const isLoggedIn = sessionStorage.getItem('isLoggedIn');
    if (!isLoggedIn && !window.location.pathname.includes('login.html')) {
        window.location.href = 'login.html';
    }
}

 
// UTILITAIRES DE TABLEAUX
 

/**
 * Trie un tableau par colonne
 */
function sortTable(tableId, columnIndex, type = 'string') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.querySelectorAll('td')[columnIndex].textContent.trim();
        const bValue = b.querySelectorAll('td')[columnIndex].textContent.trim();
        
        if (type === 'number') {
            return parseFloat(aValue.replace(/[^\d.-]/g, '')) - parseFloat(bValue.replace(/[^\d.-]/g, ''));
        } else if (type === 'date') {
            return new Date(aValue) - new Date(bValue);
        } else {
            return aValue.localeCompare(bValue);
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Filtre un tableau
 */
function filterTable(tableId, searchValue, columnIndex = null) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    const search = searchValue.toLowerCase();
    
    rows.forEach(row => {
        if (columnIndex !== null) {
            const cell = row.querySelectorAll('td')[columnIndex];
            const text = cell.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        } else {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        }
    });
}

 
// GESTION DU STOCKAGE LOCAL
 

/**
 * Sauvegarde des données dans le localStorage
 */
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
        return true;
    } catch (e) {
        console.error('Erreur de sauvegarde:', e);
        return false;
    }
}

/**
 * Récupération des données du localStorage
 */
function getFromLocalStorage(key, defaultValue = null) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : defaultValue;
    } catch (e) {
        console.error('Erreur de lecture:', e);
        return defaultValue;
    }
}

/**
 * Suppression des données du localStorage
 */
function removeFromLocalStorage(key) {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (e) {
        console.error('Erreur de suppression:', e);
        return false;
    }
}

 
// REQUÊTES AJAX (pour intégration avec PHP)
 

/**
 * Effectue une requête AJAX
 */
async function makeRequest(url, method = 'GET', data = null) {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Erreur de requête:', error);
        showMessage('Erreur de connexion au serveur', 'error');
        return null;
    }
}

 
// CONFIRMATION D'ACTIONS
 

/**
 * Affiche une boîte de dialogue de confirmation
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

 
// EXPORT DE DONNÉES
 

/**
 * Exporte un tableau en CSV
 */
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => csvRow.push(col.textContent));
        csv.push(csvRow.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

 
// INITIALISATION AU CHARGEMENT DE LA PAGE
 

document.addEventListener('DOMContentLoaded', function() {
    console.log('Application de Gestion Financière chargée');
    
    // Ajouter la classe active au lien de navigation actuel
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
    
    // Initialiser les tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.classList.add('tooltip');
    });
});

 
// GESTION DES ERREURS GLOBALES
 

window.addEventListener('error', function(e) {
    console.error('Erreur JavaScript:', e.error);
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Promesse rejetée:', e.reason);
});

 
// EXPORT DES FONCTIONS POUR USAGE GLOBAL
 

// Les fonctions sont déjà accessibles globalement car elles sont déclarées avec function
// Si vous utilisez des modules ES6, vous pouvez les exporter ainsi :
// export { formatCurrency, formatDate, showMessage, validateTransactionForm, ... };