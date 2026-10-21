/**
 * Script Validation Admin - INTÉGRÉ AVEC BACKEND PHP
 * Fichier: admin/js/validation_admin.js
 */

let pendingTransactions = [];
let currentAction = null;
let currentTransactionId = null;

/**
 * Charger les transactions en attente depuis le backend PHP
 */
async function loadPendingTransactions() {
    try {
        const tbody = document.getElementById('pendingTable');
        const emptyState = document.getElementById('emptyState');
        
        // Afficher loader
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
                    <p>Chargement des transactions...</p>
                </td>
            </tr>
        `;
        
        // Appel API Backend PHP
        const result = await AdminService.getPending();
        
        if (result.success) {
            pendingTransactions = result.transactions || [];
            
            if (pendingTransactions.length === 0) {
                renderEmptyState();
            } else {
                renderPendingTransactions();
                renderUrgentAlerts();
            }
        } else {
            renderEmptyState();
            if (typeof notifyError === 'function') {
                notifyError(result.message || 'Erreur de chargement');
            }
        }
    } catch (error) {
        console.error('Erreur chargement:', error);
        renderEmptyState();
        if (typeof notifyError === 'function') {
            notifyError('Erreur de connexion au serveur');
        }
    }
}

/**
 * Afficher les transactions en attente
 */
function renderPendingTransactions() {
    const tbody = document.getElementById('pendingTable');
    const emptyState = document.getElementById('emptyState');
    const countElement = document.getElementById('pendingCount');
    
    emptyState.classList.add('hidden');
    countElement.textContent = pendingTransactions.length;
    
    // Trier par urgence
    const sorted = [...pendingTransactions].sort((a, b) => {
        const urgenceOrder = { 'urgente': 0, 'importante': 1, 'normale': 2 };
        return urgenceOrder[a.urgence] - urgenceOrder[b.urgence];
    });
    
    tbody.innerHTML = sorted.map(tx => `
        <tr class="hover:bg-gray-50 transition ${tx.urgence === 'urgente' ? 'bg-red-50' : ''}">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">${tx.id}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${tx.user_nom || tx.username}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${tx.type === 'Entrée' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    <i class="fas fa-arrow-${tx.type === 'Entrée' ? 'up' : 'down'}"></i> ${tx.type}
                </span>
            </td>
            <td class="px-6 py-4 text-sm font-semibold text-gray-900">${parseFloat(tx.montant).toLocaleString()} FCFA</td>
            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">${tx.motif}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${formatDate(tx.date_creation)}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${formatDateOnly(tx.date_necessaire)}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 rounded text-xs font-semibold ${getUrgenceClass(tx.urgence)}">
                    ${tx.urgence === 'urgente' ? '🔴' : tx.urgence === 'importante' ? '🟠' : '🟢'} ${tx.urgence}
                </span>
            </td>
            <td class="px-6 py-4 text-sm">
                <div class="flex justify-center gap-2">
                    <button onclick="showConfirmModal('validate', '${tx.id}')"
                        class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition font-semibold">
                        <i class="fas fa-check mr-1"></i>Valider
                    </button>
                    <button onclick="showConfirmModal('reject', '${tx.id}')"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition font-semibold">
                        <i class="fas fa-times mr-1"></i>Refuser
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * Afficher les alertes urgentes
 */
function renderUrgentAlerts() {
    const alertsContainer = document.getElementById('urgentAlerts');
    const urgentTxs = pendingTransactions.filter(tx => tx.urgence === 'urgente');
    
    if (urgentTxs.length === 0) {
        alertsContainer.innerHTML = '';
        return;
    }
    
    alertsContainer.innerHTML = urgentTxs.map(tx => `
        <div class="urgent-alert bg-red-100 border-2 border-red-500 rounded-lg p-4 shadow-lg">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl mt-1"></i>
                <div class="flex-1">
                    <h3 class="font-bold text-red-800 text-lg mb-2">
                        🔴 DEMANDE URGENTE - ${tx.id}
                    </h3>
                    <div class="space-y-1 text-sm">
                        <p><strong>Demandeur:</strong> ${tx.user_nom || tx.username}</p>
                        <p><strong>Montant:</strong> ${parseFloat(tx.montant).toLocaleString()} FCFA (${tx.type})</p>
                        <p><strong>Date nécessaire:</strong> ${formatDateOnly(tx.date_necessaire)}</p>
                        <p><strong>Motif:</strong> ${tx.motif}</p>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button onclick="showConfirmModal('validate', '${tx.id}')"
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-semibold text-sm">
                            <i class="fas fa-check mr-1"></i>Valider Maintenant
                        </button>
                        <button onclick="showConfirmModal('reject', '${tx.id}')"
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-semibold text-sm">
                            <i class="fas fa-times mr-1"></i>Refuser
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * État vide
 */
function renderEmptyState() {
    document.getElementById('pendingTable').innerHTML = '';
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('countPending').innerHTML = '<i class="fas fa-check mr-1"></i>Aucune transaction en attente';
}

/**
 * Classes CSS pour l'urgence
 */
function getUrgenceClass(urgence) {
    switch(urgence) {
        case 'urgente': return 'bg-red-100 text-red-800 border border-red-300';
        case 'importante': return 'bg-orange-100 text-orange-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

/**
 * Formater une date
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('fr-FR');
}

function formatDateOnly(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR');
}

/**
 * Afficher la modal de confirmation
 */
function showConfirmModal(action, txId) {
    currentAction = action;
    currentTransactionId = txId;
    
    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const confirmBtn = document.getElementById('confirmBtn');
    
    const tx = pendingTransactions.find(t => t.id === txId);
    
    if (action === 'validate') {
        modalTitle.innerHTML = '<i class="fas fa-check-circle mr-2 text-green-600"></i>Valider la transaction';
        modalMessage.textContent = `Êtes-vous sûr de vouloir valider la transaction ${txId} de ${parseFloat(tx.montant).toLocaleString()} FCFA ?`;
        confirmBtn.className = 'flex-1 bg-green-500 hover:bg-green-600 py-2 px-4 rounded-lg font-semibold text-white transition';
    } else {
        modalTitle.innerHTML = '<i class="fas fa-times-circle mr-2 text-red-600"></i>Refuser la transaction';
        modalMessage.textContent = `Êtes-vous sûr de vouloir refuser la transaction ${txId} de ${parseFloat(tx.montant).toLocaleString()} FCFA ?`;
        confirmBtn.className = 'flex-1 bg-red-500 hover:bg-red-600 py-2 px-4 rounded-lg font-semibold text-white transition';
    }
    
    modal.classList.remove('hidden');
}

/**
 * Fermer la modal
 */
function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    currentAction = null;
    currentTransactionId = null;
}

/**
 * Confirmer l'action (valider/refuser) via Backend PHP
 */
async function confirmAction() {
    try {
        if (typeof notifyInfo === 'function') {
            notifyInfo('Traitement en cours...');
        }
        
        // Appel API Backend PHP
        const result = await AdminService.validateTransaction(
            currentTransactionId,
            currentAction,
            null
        );
        
        if (result.success) {
            const message = currentAction === 'validate' 
                ? `Transaction ${currentTransactionId} validée avec succès`
                : `Transaction ${currentTransactionId} refusée`;
            
            if (typeof notifySuccess === 'function') {
                notifySuccess(message);
            } else {
                alert(message);
            }
            
            closeModal();
            loadPendingTransactions();
        } else {
            throw new Error(result.message || 'Erreur lors du traitement');
        }
    } catch (error) {
        console.error('Erreur:', error);
        if (typeof notifyError === 'function') {
            notifyError(error.message || 'Erreur lors du traitement');
        } else {
            alert('Erreur lors du traitement');
        }
    }
}

// Charger au démarrage
window.addEventListener('DOMContentLoaded', async () => {
    // Vérifier l'authentification admin
    const isAuthenticated = localStorage.getItem('isAuthenticated');
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    
    if (isAuthenticated !== 'true' || user.role !== 'Administrateur') {
        if (typeof notifyWarning === 'function') {
            notifyWarning('Accès réservé aux administrateurs');
        }
        setTimeout(() => {
            window.location.href = '../utilisateur/login.html';
        }, 1500);
        return;
    }
    
    // Charger les transactions
    await loadPendingTransactions();
});

// Recharger toutes les 30 secondes
setInterval(async () => {
    const isAuthenticated = localStorage.getItem('isAuthenticated');
    if (isAuthenticated === 'true') {
        await loadPendingTransactions();
    }
}, 30000);