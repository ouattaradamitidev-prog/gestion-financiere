/**
 * SYSTÈME DE NOTIFICATIONS - VERSION SIMPLIFIÉE
 * Affiche des notifications en haut à droite de l'écran
 */

 
// FONCTION PRINCIPALE POUR AFFICHER UNE NOTIFICATION
 

/**
 * Affiche une notification
 * @param {string} message - Le message à afficher
 * @param {string} type - Type: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Durée en ms (0 = permanent)
 */
function showNotification(message, type = 'info', duration = 5000) {
    // Créer le conteneur s'il n'existe pas
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; width: 350px;';
        document.body.appendChild(container);
    }

    // Créer la notification
    const notif = document.createElement('div');
    const notifId = 'notif-' + Date.now();
    notif.id = notifId;
    
    // Styles selon le type
    const styles = {
        success: {
            bg: '#d1fae5',
            border: '#10b981',
            text: '#065f46',
            icon: '✓'
        },
        error: {
            bg: '#fee2e2',
            border: '#ef4444',
            text: '#991b1b',
            icon: '✕'
        },
        warning: {
            bg: '#fef3c7',
            border: '#f59e0b',
            text: '#92400e',
            icon: '⚠'
        },
        info: {
            bg: '#dbeafe',
            border: '#3b82f6',
            text: '#1e40af',
            icon: 'ℹ'
        }
    };

    const style = styles[type] || styles.info;

    // Style de la notification
    notif.style.cssText = `
        background: ${style.bg};
        border-left: 4px solid ${style.border};
        color: ${style.text};
        padding: 16px;
        margin-bottom: 10px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: flex-start;
        animation: slideIn 0.3s ease-out;
        opacity: 0;
        transform: translateX(400px);
    `;

    // Contenu HTML
    notif.innerHTML = `
        <div style="font-size: 20px; margin-right: 12px; font-weight: bold;">${style.icon}</div>
        <div style="flex: 1;">
            <div style="font-weight: 600; margin-bottom: 4px;">
                ${type.charAt(0).toUpperCase() + type.slice(1)}
            </div>
            <div style="font-size: 14px;">${escapeHtml(message)}</div>
        </div>
        <button onclick="removeNotification('${notifId}')" style="background: none; border: none; color: ${style.text}; font-size: 20px; cursor: pointer; padding: 0; margin-left: 12px; opacity: 0.7;">
            ×
        </button>
    `;

    // Ajouter au conteneur
    container.appendChild(notif);

    // Animation d'entrée
    setTimeout(() => {
        notif.style.opacity = '1';
        notif.style.transform = 'translateX(0)';
        notif.style.transition = 'all 0.3s ease-out';
    }, 10);

    // Jouer un son (optionnel)
    playNotificationSound(type);

    // Auto-suppression
    if (duration > 0) {
        setTimeout(() => {
            removeNotification(notifId);
        }, duration);
    }

    return notifId;
}

/**
 * Supprime une notification
 */
function removeNotification(notifId) {
    const notif = document.getElementById(notifId);
    if (notif) {
        notif.style.opacity = '0';
        notif.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (notif.parentNode) {
                notif.parentNode.removeChild(notif);
            }
        }, 300);
    }
}

/**
 * Supprime toutes les notifications
 */
function clearAllNotifications() {
    const container = document.getElementById('notification-container');
    if (container) {
        container.innerHTML = '';
    }
}

/**
 * Échappe le HTML pour éviter les injections XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Joue un son de notification
 */
function playNotificationSound(type) {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        const audioContext = new AudioContext();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        const frequencies = {
            success: 800,
            error: 400,
            warning: 600,
            info: 500
        };

        oscillator.frequency.value = frequencies[type] || 500;
        oscillator.type = 'sine';
        gainNode.gain.value = 0.05;

        oscillator.start();
        setTimeout(() => oscillator.stop(), 100);
    } catch (e) {
        // Ignorer les erreurs de son
    }
}

 
// FONCTIONS RACCOURCIES (pour faciliter l'utilisation)
 

/**
 * Notification de succès (vert)
 */
function notifySuccess(message, duration = 5000) {
    return showNotification(message, 'success', duration);
}

/**
 * Notification d'erreur (rouge)
 */
function notifyError(message, duration = 5000) {
    return showNotification(message, 'error', duration);
}

/**
 * Notification d'avertissement (jaune)
 */
function notifyWarning(message, duration = 5000) {
    return showNotification(message, 'warning', duration);
}

/**
 * Notification d'information (bleu)
 */
function notifyInfo(message, duration = 5000) {
    return showNotification(message, 'info', duration);
}

 
// FONCTIONS SPÉCIFIQUES POUR L'APPLICATION
 

/**
 * Notification pour transaction soumise
 */
function notifyTransactionSubmitted(transactionId, montant) {
    return notifySuccess(
        `Transaction ${transactionId} soumise : ${parseInt(montant).toLocaleString()} FCFA`,
        6000
    );
}

/**
 * Notification pour transaction validée
 */
function notifyTransactionValidated(transactionId) {
    return notifySuccess(
        `✓ Transaction ${transactionId} validée avec succès`,
        7000
    );
}

/**
 * Notification pour transaction refusée
 */
function notifyTransactionRejected(transactionId, raison = '') {
    const message = raison 
        ? `Transaction ${transactionId} refusée : ${raison}`
        : `Transaction ${transactionId} refusée`;
    return notifyError(message, 7000);
}

/**
 * Notification pour nouvelle demande (admin)
 */
function notifyNewRequest(userName, montant) {
    return notifyInfo(
        `📩 Nouvelle demande de ${userName} : ${parseInt(montant).toLocaleString()} FCFA`,
        10000
    );
}

 
// STYLES CSS POUR LES ANIMATIONS
 

// Ajouter les styles d'animation dans le DOM
if (!document.getElementById('notification-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'notification-styles';
    styleSheet.textContent = `
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(400px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    `;
    document.head.appendChild(styleSheet);
}

 
// INITIALISATION
 

console.log('✅ Système de notifications chargé et prêt !');

//  pour tester
// setTimeout(() => {
//     notifySuccess('Le système de notifications fonctionne !');
// }, 1000);