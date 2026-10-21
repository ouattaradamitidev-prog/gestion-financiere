/**
 * Configuration API Frontend - Version Adaptée
 * Fichier: assets/js/api-config.js
 */

// ============================================================
// CONFIGURATION DE BASE
// ============================================================
const API_CONFIG = {
    // ⚠️ IMPORTANT: Modifie cette URL selon ton installation
    // Pour XAMPP/WAMP avec ton projet dans htdocs :
    BASE_URL: 'http://localhost/gestion-financiere/backend-gestion-financiere/api',
    
    // Alternative si tu as mis le projet ailleurs :
    // BASE_URL: 'http://localhost/mon-projet/backend-gestion-financiere/api',
    
    TIMEOUT: 30000,
    DEBUG: true
};

// ============================================================
// CLIENT API
// ============================================================
class ApiClient {
    constructor() {
        this.baseURL = API_CONFIG.BASE_URL;
        this.timeout = API_CONFIG.TIMEOUT;
        this.debug = API_CONFIG.DEBUG;
    }
    
    log(message, data) {
        if (this.debug) {
            console.log(`[API] ${message}`, data);
        }
    }
    
    handleError(error, endpoint) {
        console.error(`[API Error] ${endpoint}:`, error);
        
        if (error.name === 'AbortError') {
            return {
                success: false,
                message: 'La requête a expiré. Veuillez réessayer.'
            };
        }
        
        return {
            success: false,
            message: error.message || 'Une erreur est survenue'
        };
    }
    
    async request(method, endpoint, body = null) {
        try {
            const url = this.baseURL + endpoint;
            this.log(`${method} Request`, { url, body });
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.timeout);
            
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                signal: controller.signal
            };
            
            if (body && (method === 'POST' || method === 'PUT')) {
                options.body = JSON.stringify(body);
            }
            
            const response = await fetch(url, options);
            clearTimeout(timeoutId);
            
            const data = await response.json();
            this.log(`${method} Response`, data);
            
            return data;
        } catch (error) {
            return this.handleError(error, endpoint);
        }
    }
    
    async get(endpoint, params = {}) {
        const queryParams = new URLSearchParams(params).toString();
        const fullEndpoint = queryParams ? `${endpoint}?${queryParams}` : endpoint;
        return this.request('GET', fullEndpoint);
    }
    
    async post(endpoint, body = {}) {
        return this.request('POST', endpoint, body);
    }
}

const api = new ApiClient();

// ============================================================
// SERVICES
// ============================================================

// Service d'authentification
const AuthService = {
    async login(username, password) {
        return await api.post('/auth.php?action=login', {
            username,
            password
        });
    },
    
    async register(userData) {
        return await api.post('/auth.php?action=register', {
            nom: userData.lastName,
            prenom: userData.firstName,
            email: userData.email,
            username: userData.username,
            password: userData.password,
            confirm_password: userData.confirmPassword,
            role: userData.accountType === 'admin' ? 'Administrateur' : 'Utilisateur',
            admin_code: userData.adminCode || null
        });
    },
    
    async logout() {
        return await api.post('/auth.php?action=logout');
    },
    
    async forgotPassword(email) {
        return await api.post('/auth.php?action=forgot_password', { email });
    },
    
    async resetPassword(token, newPassword, confirmPassword) {
        return await api.post('/auth.php?action=reset_password', {
            token,
            new_password: newPassword,
            confirm_password: confirmPassword
        });
    },
    
    async checkSession() {
        return await api.get('/auth.php?action=check_session');
    }
};

// Service de transactions
const TransactionService = {
    async create(transactionData) {
        return await api.post('/transactions.php?action=create', transactionData);
    },
    
    async list(filters = {}) {
        return await api.get('/transactions.php?action=list', filters);
    },
    
    async get(id) {
        return await api.get('/transactions.php?action=get', { id });
    }
};

// Service utilisateur
const UserService = {
    async getProfile() {
        return await api.get('/users.php?action=get_profile');
    },
    
    async updateProfile(profileData) {
        return await api.post('/users.php?action=update_profile', profileData);
    },
    
    async changePassword(currentPassword, newPassword) {
        return await api.post('/users.php?action=change_password', {
            currentPassword,
            newPassword
        });
    }
};

// Service admin
const AdminService = {
    async getDashboard(filters = {}) {
        return await api.get('/admin/dashboard.php', filters);
    },
    
    async getPending(filters = {}) {
        return await api.get('/admin/pending.php', filters);
    },
    
    async validateTransaction(transactionId, action, commentaire = null) {
        return await api.post('/admin/validate.php', {
            transaction_id: transactionId,
            action,
            commentaire
        });
    },
    
    async getHistory(filters = {}) {
        return await api.get('/admin/history.php', filters);
    },
    
    async getUsers(filters = {}) {
        return await api.get('/admin/users.php?action=list', filters);
    },
    
    async createUser(userData) {
        return await api.post('/admin/users.php?action=create', userData);
    },
    
    async updateUser(userId, userData) {
        return await api.post('/admin/users.php?action=update', {
            user_id: userId,
            ...userData
        });
    },
    
    async deactivateUser(userId) {
        return await api.post('/admin/users.php?action=deactivate', {
            user_id: userId
        });
    }
};

// Export global
window.API_CONFIG = API_CONFIG;
window.api = api;
window.AuthService = AuthService;
window.TransactionService = TransactionService;
window.UserService = UserService;
window.AdminService = AdminService;

console.log('✅ API Configuration chargée - URL:', API_CONFIG.BASE_URL);