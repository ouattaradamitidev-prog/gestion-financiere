/**
 * Script pour la liste des transactions - Intégré avec backend PHP
 * Fichier: utilisateur/js/mes_transactions.js
 */

let allTransactions = [];
let filteredTransactions = [];

/**
 * Charger les transactions depuis le backend
 */
async function loadTransactions() {
    try {
        // Afficher un loader
        const tbody = document.getElementById('transactionsTable');
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
                    <p>Chargement des transactions...</p>
                </td>
            </tr>
        `;

        // Appel API pour récupérer les transactions
        const result = await TransactionService.list();

        if (result.success) {
            allTransactions = result.transactions || [];
            filteredTransactions = [...allTransactions];
            renderTransactions();
            updateStats();
        } else {
            renderEmptyState();
            if (typeof notifyError === 'function') {
                notifyError(result.message || 'Erreur lors du chargement des transactions');
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
 * Afficher les transactions dans le tableau
 */
function renderTransactions() {
    const tbody = document.getElementById('transactionsTable');
    
    if (filteredTransactions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>Aucune transaction trouvée</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filteredTransactions.map(tx => `
        <tr class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">${tx.id || tx.transaction_id}</td>
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
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatutClass(tx.statut)}">
                    ${capitalizeFirst(tx.statut)}
                </span>
            </td>
            <td class="px-6 py-4 text-sm">
                ${tx.statut === 'validee' || tx.statut === 'validée' ? 
                    `<button onclick="downloadReceipt('${tx.id || tx.transaction_id}')" class="text-green-600 hover:text-green-800 font-semibold">
                        <i class="fas fa-download"></i> Reçu
                    </button>` : 
                    '<span class="text-gray-400">-</span>'}
            </td>
        </tr>
    `).join('');
}

/**
 * Afficher l'état vide
 */
function renderEmptyState() {
    const tbody = document.getElementById('transactionsTable');
    tbody.innerHTML = `
        <tr>
            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3"></i>
                <p>Aucune transaction pour le moment</p>
                <a href="./transaction_form.html" class="text-green-600 hover:underline mt-2 inline-block">
                    Créer votre première transaction
                </a>
            </td>
        </tr>
    `;
}

/**
 * Mettre à jour les statistiques
 */
function updateStats() {
    document.getElementById('statTotal').textContent = filteredTransactions.length;
    document.getElementById('statAttente').textContent = filteredTransactions.filter(t => 
        t.statut === 'en_attente' || t.statut === 'en attente'
    ).length;
    document.getElementById('statValidees').textContent = filteredTransactions.filter(t => 
        t.statut === 'validee' || t.statut === 'validée'
    ).length;
    document.getElementById('statRefusees').textContent = filteredTransactions.filter(t => 
        t.statut === 'refusee' || t.statut === 'refusée'
    ).length;
}

/**
 * Obtenir la classe CSS pour le statut
 */
function getStatutClass(statut) {
    const statutNormalized = statut.toLowerCase().replace('é', 'e').replace(' ', '_');
    
    switch(statutNormalized) {
        case 'validee':
        case 'validée':
            return 'bg-green-100 text-green-800';
        case 'en_attente':
        case 'en attente':
            return 'bg-yellow-100 text-yellow-800';
        case 'refusee':
        case 'refusée':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

/**
 * Obtenir la classe CSS pour l'urgence
 */
function getUrgenceClass(urgence) {
    switch(urgence) {
        case 'urgente':
            return 'bg-red-100 text-red-800 border border-red-300';
        case 'importante':
            return 'bg-orange-100 text-orange-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

/**
 * Formater une date complète
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('fr-FR');
}

/**
 * Formater une date simple (sans heure)
 */
function formatDateOnly(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

/**
 * Capitaliser la première lettre
 */
function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Appliquer les filtres de période
 */
function applyPeriodFilter() {
    const period = document.getElementById('periodFilter').value;
    const customStart = document.getElementById('customDateStart');
    const customEnd = document.getElementById('customDateEnd');

    if (period === 'custom') {
        customStart.classList.remove('hidden');
        customEnd.classList.remove('hidden');
    } else {
        customStart.classList.add('hidden');
        customEnd.classList.add('hidden');
        filterByPeriod(period);
    }
}

/**
 * Filtrer par période
 */
function filterByPeriod(period) {
    const now = new Date();
    let startDate;

    switch(period) {
        case 'today':
            startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            break;
        case 'week':
            startDate = new Date(now);
            startDate.setDate(now.getDate() - 7);
            break;
        case 'month':
            startDate = new Date(now.getFullYear(), now.getMonth(), 1);
            break;
        default:
            filteredTransactions = [...allTransactions];
            applyFilters();
            return;
    }

    filteredTransactions = allTransactions.filter(tx => {
        const txDate = new Date(tx.date_creation);
        return txDate >= startDate;
    });

    applyFilters();
}

/**
 * Appliquer les filtres personnalisés
 */
function applyCustomFilter() {
    const startDate = document.getElementById('dateDebut').value;
    const endDate = document.getElementById('dateFin').value;

    if (startDate && endDate) {
        filteredTransactions = allTransactions.filter(tx => {
            const txDate = new Date(tx.date_creation).toISOString().split('T')[0];
            return txDate >= startDate && txDate <= endDate;
        });
        applyFilters();
    }
}

/**
 * Appliquer les filtres de type et statut
 */
function applyFilters() {
    const type = document.getElementById('typeFilter').value;
    const statut = document.getElementById('statutFilter').value;

    let result = [...filteredTransactions];

    if (type) {
        result = result.filter(tx => tx.type === type);
    }

    if (statut) {
        result = result.filter(tx => {
            const txStatut = tx.statut.toLowerCase().replace('é', 'e').replace(' ', '_');
            const filterStatut = statut.toLowerCase().replace('é', 'e').replace(' ', '_');
            return txStatut === filterStatut;
        });
    }

    filteredTransactions = result;
    renderTransactions();
    updateStats();
}

/**
 * Exporter vers Excel (simulation)
 */
function exportToExcel() {
    if (filteredTransactions.length === 0) {
        if (typeof notifyWarning === 'function') {
            notifyWarning('Aucune transaction à exporter');
        } else {
            alert('Aucune transaction à exporter');
        }
        return;
    }

    if (typeof notifyInfo === 'function') {
        notifyInfo('Export Excel : Cette fonction nécessite une bibliothèque JavaScript supplémentaire (SheetJS)');
    } else {
        alert('Export Excel : Cette fonction nécessite une bibliothèque JavaScript supplémentaire');
    }
}

/**
 * Exporter vers PDF
 */
function exportToPDF() {
    if (typeof notifyInfo === 'function') {
        notifyInfo('Export PDF : Utilisez l\'impression du navigateur (Ctrl+P)');
    } else {
        alert('Export PDF : Utilisez l\'impression du navigateur');
    }
    window.print();
}

/**
 * Télécharger un reçu
 */
function downloadReceipt(id) {
    window.location.href = `./recu.html?id=${id}`;
}

// Vérifier l'authentification et charger les transactions au démarrage
window.addEventListener('DOMContentLoaded', async () => {
    const isAuthenticated = localStorage.getItem('isAuthenticated');
    
    if (isAuthenticated !== 'true') {
        if (typeof notifyWarning === 'function') {
            notifyWarning('Vous devez être connecté pour accéder à cette page');
        }
        setTimeout(() => {
            window.location.href = './login.html';
        }, 1500);
        return;
    }

    // Vérifier la session et charger les transactions
    try {
        const sessionCheck = await AuthService.checkSession();
        
        if (sessionCheck.success && sessionCheck.authenticated) {
            // Charger les transactions
            await loadTransactions();
        } else {
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
        console.error('Erreur:', error);
        if (typeof notifyError === 'function') {
            notifyError('Erreur de connexion au serveur');
        }
    }
});

// Recharger les transactions toutes les 30 secondes
setInterval(async () => {
    const isAuthenticated = localStorage.getItem('isAuthenticated');
    if (isAuthenticated === 'true') {
        await loadTransactions();
    }
}, 30000);