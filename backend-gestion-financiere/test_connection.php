<?php
/**
 * ============================================================
 * TEST DE CONNEXION À LA BASE DE DONNÉES
 * Fichier: backend/test_connection.php
 * ============================================================
 * Testez ce fichier en accédant à :
 * http://localhost/backend/test_connection.php
 */

header("Content-Type: text/html; charset=UTF-8");

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test de connexion BDD</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
        h1 { margin-top: 0; }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #e5e7eb;
            background: #f9fafb;
        }
        .test-item.success { border-color: #10b981; background: #d1fae5; }
        .test-item.error { border-color: #ef4444; background: #fee2e2; }
        code {
            background: #1f2937;
            color: #10b981;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class='card'>";

echo "<h1>🔧 Test de Connexion Backend</h1>";
echo "<p class='info'>Test effectué le : " . date('d/m/Y à H:i:s') . "</p>";
echo "<hr>";

// Test 1: Fichier de configuration
echo "<div class='test-item ";
if (file_exists(__DIR__ . '/config/database.php')) {
    echo "success'>";
    echo "<h3 class='success'>✓ Fichier de configuration trouvé</h3>";
    echo "<p>Le fichier <code>config/database.php</code> existe.</p>";
} else {
    echo "error'>";
    echo "<h3 class='error'>✕ Fichier de configuration manquant</h3>";
    echo "<p>Le fichier <code>config/database.php</code> n'existe pas.</p>";
}
echo "</div>";

// Test 2: Connexion à la base de données
echo "<div class='test-item ";
try {
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "success'>";
        echo "<h3 class='success'>✓ Connexion à la base de données réussie</h3>";
        echo "<p>La connexion à MySQL a été établie avec succès.</p>";
        
        // Test 3: Vérification des tables
        echo "</div>";
        echo "<div class='test-item ";
        
        $tables_required = ['users', 'transactions', 'password_resets', 'logs'];
        $tables_found = [];
        $tables_missing = [];
        
        foreach ($tables_required as $table) {
            $query = "SHOW TABLES LIKE '{$table}'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $tables_found[] = $table;
            } else {
                $tables_missing[] = $table;
            }
        }
        
        if (empty($tables_missing)) {
            echo "success'>";
            echo "<h3 class='success'>✓ Toutes les tables existent</h3>";
            echo "<p>Tables trouvées : " . implode(', ', $tables_found) . "</p>";
        } else {
            echo "error'>";
            echo "<h3 class='error'>✕ Tables manquantes</h3>";
            echo "<p>Tables trouvées : " . implode(', ', $tables_found) . "</p>";
            echo "<p>Tables manquantes : " . implode(', ', $tables_missing) . "</p>";
            echo "<p><strong>Action requise :</strong> Exécutez le fichier SQL <code>backend/sql/schema.sql</code> dans phpMyAdmin.</p>";
        }
        
        // Test 4: Vérification des utilisateurs de test
        echo "</div>";
        echo "<div class='test-item ";
        
        $query = "SELECT COUNT(*) as count FROM users";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_count = $result['count'];
        
        if ($user_count > 0) {
            echo "success'>";
            echo "<h3 class='success'>✓ Utilisateurs trouvés</h3>";
            echo "<p>Nombre d'utilisateurs dans la base : <strong>{$user_count}</strong></p>";
            
            // Liste des utilisateurs
            $query = "SELECT username, email, role FROM users LIMIT 5";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Utilisateurs disponibles pour test :</strong></p>";
            echo "<ul>";
            foreach ($users as $user) {
                echo "<li><code>{$user['username']}</code> - {$user['email']} ({$user['role']})</li>";
            }
            echo "</ul>";
        } else {
            echo "error'>";
            echo "<h3 class='error'>✕ Aucun utilisateur trouvé</h3>";
            echo "<p>La table users est vide. Exécutez le fichier SQL pour créer les utilisateurs de test.</p>";
        }
        
    } else {
        throw new Exception("Connexion nulle");
    }
    
} catch (Exception $e) {
    echo "error'>";
    echo "<h3 class='error'>✕ Erreur de connexion</h3>";
    echo "<p>Message d'erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Vérifiez :</strong></p>";
    echo "<ul>
        <li>MySQL est bien démarré (XAMPP/WAMP)</li>
        <li>Le nom de la base de données est correct dans <code>config/database.php</code></li>
        <li>L'utilisateur et le mot de passe sont corrects</li>
        <li>La base de données <code>gestion_financiere</code> existe</li>
    </ul>";
}
echo "</div>";

// Test 5: Vérification des modèles
echo "<div class='test-item ";
$models = ['User.php', 'Transaction.php'];
$models_found = [];
$models_missing = [];

foreach ($models as $model) {
    if (file_exists(__DIR__ . '/models/' . $model)) {
        $models_found[] = $model;
    } else {
        $models_missing[] = $model;
    }
}

if (empty($models_missing)) {
    echo "success'>";
    echo "<h3 class='success'>✓ Tous les modèles existent</h3>";
    echo "<p>Modèles trouvés : " . implode(', ', $models_found) . "</p>";
} else {
    echo "error'>";
    echo "<h3 class='error'>✕ Modèles manquants</h3>";
    echo "<p>Modèles manquants : " . implode(', ', $models_missing) . "</p>";
}
echo "</div>";

// Résumé final
echo "<hr>";
echo "<h2>📋 Résumé</h2>";
echo "<p>Si tous les tests sont verts (✓), votre backend est prêt à être utilisé !</p>";
echo "<p>Sinon, suivez les instructions pour corriger les erreurs.</p>";

echo "<h3>🚀 Prochaines étapes :</h3>";
echo "<ol>
    <li>Testez la connexion : <a href='login.html'>Page de connexion</a></li>
    <li>Créez un compte de test si nécessaire</li>
    <li>Testez les fonctionnalités principales</li>
</ol>";

echo "</div>
</body>
</html>";
?>