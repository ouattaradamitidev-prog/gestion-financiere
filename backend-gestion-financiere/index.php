<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backend - Système de Gestion Financière</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 900px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        
        .status {
            background: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .status.success {
            background: #f0fdf4;
            border-color: #22c55e;
        }
        
        .status h3 {
            color: #0ea5e9;
            margin-bottom: 5px;
        }
        
        .status.success h3 {
            color: #22c55e;
        }
        
        .endpoints {
            margin-top: 30px;
        }
        
        .endpoint-group {
            margin-bottom: 30px;
        }
        
        .endpoint-group h3 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .endpoint {
            background: #f9fafb;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .method {
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.85em;
            min-width: 60px;
            text-align: center;
        }
        
        .method.post {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .method.get {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .method.put {
            background: #fef3c7;
            color: #d97706;
        }
        
        .method.delete {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .path {
            color: #374151;
            font-family: 'Courier New', monospace;
            flex: 1;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        
        .feature {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }
        
        .feature h4 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .feature p {
            color: #666;
            font-size: 0.9em;
        }
        
        .test-accounts {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .test-accounts h3 {
            color: #d97706;
            margin-bottom: 15px;
        }
        
        .account {
            background: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .account strong {
            color: #333;
        }
        
        .docs-link {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .docs-link:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Backend API</h1>
        <p class="subtitle">Système de Gestion Financière - Version 1.0.0</p>
        
        <div class="status success">
            <h3>✅ Système Opérationnel</h3>
            <p>L'API backend est installée et fonctionnelle</p>
        </div>
        
        <div class="features">
            <div class="feature">
                <h4>🔐 Authentification</h4>
                <p>Login, Register, Reset Password</p>
            </div>
            <div class="feature">
                <h4>💰 Transactions</h4>
                <p>Création et suivi des transactions</p>
            </div>
            <div class="feature">
                <h4>👥 Gestion Utilisateurs</h4>
                <p>CRUD complet des utilisateurs</p>
            </div>
            <div class="feature">
                <h4>📊 Dashboard Admin</h4>
                <p>Statistiques et rapports</p>
            </div>
        </div>
        
        <div class="endpoints">
            <div class="endpoint-group">
                <h3>🔑 Authentification</h3>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="path">/api/auth.php?action=login</span>
                </div>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="path">/api/auth.php?action=register</span>
                </div>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="path">/api/auth.php?action=logout</span>
                </div>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="path">/api/auth.php?action=forgot_password</span>
                </div>
            </div>
            
            <div class="endpoint-group">
                <h3>💸 Transactions Utilisateur</h3>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="path">/api/transactions.php?action=create</span>
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/api/transactions.php?action=list</span>
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/api/transactions.php?action=get&id={id}</span>
                </div>
            </div>
            
            <div class="endpoint-group">
                <h3>👨‍💼 Administration</h3>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/api/admin/dashboard.php</span>
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/api/admin/pending.php</span>
                </div>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="path">/api/admin/validate.php</span>
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/api/admin/history.php</span>
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/api/admin/users.php?action=list</span>
                </div>
            </div>
        </div>
        
        <div class="test-accounts">
            <h3>🔐 Comptes de Test</h3>
            <div class="account">
                <strong>Administrateur:</strong> username = <code>admin</code> | password = <code>Admin123!</code>
            </div>
            <div class="account">
                <strong>Utilisateur:</strong> username = <code>amara.kone</code> | password = <code>Admin123!</code>
            </div>
        </div>
        
        <a href="INSTALLATION.md" class="docs-link">📖 Consulter la Documentation</a>
        <a href="test_connection.php" class="docs-link" style="background: #22c55e; margin-left: 10px;">✅ Tester la Connexion</a>
    </div>
</body>
</html>