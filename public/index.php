<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include required files
require_once __DIR__.'/../app/models/Database.php';
require_once __DIR__.'/../app/controllers/UserController.php';
require_once __DIR__.'/../app/controllers/WalletController.php';
require_once __DIR__.'/../app/controllers/TransactionController.php';
require_once __DIR__.'/../app/controllers/ExchangeController.php';
require_once __DIR__.'/../app/models/User.php';
require_once __DIR__.'/../app/models/Wallet.php';
require_once __DIR__.'/../app/models/Transaction.php';
require_once __DIR__.'/../app/models/Nominal.php';
require_once __DIR__.'/../app/models/Exchanger.php';
require_once __DIR__.'/../app/models/Amount.php';
require_once __DIR__.'/../app/models/ExchangeStrategy.php';

?> <html><head>
<title>Wallet Exchange Demonstration</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; color: #333; max-width: 1000px; margin: 0 auto; }
    h1 { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    h2 { color: #3498db; margin-top: 30px; }
    h3 { color: #2980b9; }
    h4 { color: #16a085; }
    ul { list-style-type: square; }
    .success { color: #27ae60; font-weight: bold; }
    .error { color: #c0392b; font-weight: bold; }
    .explanation { background-color: #f9f9f9; padding: 10px; border-left: 4px solid #3498db; margin: 15px 0; }
    .code-example { background-color: #f5f5f5; padding: 10px; font-family: monospace; overflow-x: auto; }
    .algorithm-steps { background-color: #fffde7; padding: 10px; border-left: 4px solid #fbc02d; }
</style>
</head><body>

<h1>Smart Wallet Exchange System</h1>
<div class='explanation'>
    <p>This system demonstrates a digital wallet implementation with advanced exchange strategies.</p>
    <p>It allows users to:
        <ul>
            <li>Create a wallet</li>
            <li>Add various denominations (coins and banknotes) to their wallet</li>
            <li>Exchange amounts using different algorithms tailored to specific needs</li>
        </ul>
    </p>
</div>

<h2>System Initialization</h2>
<?php
// Connect to database
try {
    $db = new Database();
    echo "<p class='success'>✓ Database connected successfully</p>";
    
    // Initialize models and controllers
    $userModel = new User($db);
    $walletModel = new Wallet($db);
    $transactionModel = new Transaction($db);
    $nominalModel = new Nominal($db);
    $exchanger = new Exchanger($nominalModel);
    
    $userController = new UserController($userModel);
    $walletController = new WalletController($walletModel);
    $transactionController = new TransactionController($transactionModel);
    $exchangeController = new ExchangeController($exchanger, $nominalModel);
    echo "<p class='success'>✓ System components initialized</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ System initialization failed: {$e->getMessage()}</p>";
    exit;
}

// User registration and login
echo "<h2>User Authentication</h2>";
$username = "john_doe";
$password = "securepassword";

try {
    $userController->registerUser($username, $password);
    echo "<p class='success'>✓ New user registered successfully</p>";
} catch (Exception $e) {
    echo "<p>Note: " . $e->getMessage() . " (Using existing account)</p>";
}

// Login user
try {
    $user = $userController->loginUser($username, $password);
    if (!$user) {
        echo "<p class='error'>✗ Login failed - Invalid credentials</p>";
        exit;
    }
    
    echo "<p class='success'>✓ Logged in successfully as $username (ID: {$user['id']})</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Login failed: {$e->getMessage()}</p>";
    exit;
}

// Wallet operations
echo "<h2>Wallet Management System</h2>";

// Create wallet
try {
    $wallet_id = $walletController->createWallet($user['id']);
    echo "<p class='success'>✓ Created wallet (ID: $wallet_id)</p>";
    
    // Get user's wallets
    $wallets = $walletController->getWallets($user['id']);
    echo "<p class='success'>✓ Retrieved " . count($wallets) . " wallet(s)</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Wallet creation failed: {$e->getMessage()}</p>";
    exit;
}

if (!empty($wallets)) {
    $walletId = $wallets[0]['id'];
    
    echo "<h3>Setting Up Wallet with Denominations</h3>";
    echo "<div class='explanation'>
        <p>For our exchange demonstration, we'll populate the wallet with various denominations:</p>
        <ul>
            <li>Banknotes: 50 zł, 20 zł, 10 zł</li>
            <li>Coins:5 zł, 2 zł, 1 zł</li>
        </ul>
        <p>This mix of denominations will allow us to test different exchange strategies.</p>
    </div>";
    
    try {
        // Add denominations to the wallet
        $exchangeController->addNominalToWallet($walletId, 50, 'banknote', 1);
        $exchangeController->addNominalToWallet($walletId, 20, 'banknote', 2);
        $exchangeController->addNominalToWallet($walletId, 10, 'banknote', 3);
        $exchangeController->addNominalToWallet($walletId, 5, 'coin', 5);
        $exchangeController->addNominalToWallet($walletId, 2, 'coin', 5);
        $exchangeController->addNominalToWallet($walletId, 1, 'coin', 10);
        echo "<p class='success'>✓ Successfully added various denominations to wallet</p>";
        
        // Show wallet content
        $nominals = $exchangeController->getNominalsInWallet($walletId);
        echo "<h4>Current Wallet Content:</h4>";
        echo "<ul>";
        foreach ($nominals as $nominal) {
            echo "<li>{$nominal['count']}× {$nominal['nominal']} zł ({$nominal['type']})</li>";
        }
        echo "</ul>";
        
        // Exchange amount using different strategies
        echo "<h2>Exchange Demonstration - 37 zł</h2>";
        
        // Strategy 1: Fewest Bills
        echo "<h3>Strategy 1: Fewest Bills</h3>";
        echo "<div class='algorithm-steps'>
            <p><strong>Algorithm:</strong> Greedy approach starting with the largest denominations first.</p>
            <p><strong>Use case:</strong> When you want to minimize the number of physical items exchanged.</p>
        </div>";
        
        try {
            $result = $exchangeController->exchangeAmount($walletId, 37, 'fewestBills');
            echo "<h4>Result:</h4>";
            echo "<ul>";
            $totalCount = 0;
            foreach ($result as $value => $nominal) {
                echo "<li>{$nominal['count']}× {$value} zł ({$nominal['type']})</li>";
                $totalCount += $nominal['count'];
            }
            echo "</ul>";
            echo "<p>Total items: $totalCount</p>";
        } catch (Exception $e) {
            echo "<p class='error'>✗ Exchange failed: {$e->getMessage()}</p>";
        }
        
        // Reload wallet
        $exchangeController->addNominalToWallet($walletId, 50, 'banknote', 1);
        $exchangeController->addNominalToWallet($walletId, 20, 'banknote', 2);
        $exchangeController->addNominalToWallet($walletId, 10, 'banknote', 3);
        $exchangeController->addNominalToWallet($walletId, 5, 'coin', 5);
        $exchangeController->addNominalToWallet($walletId, 2, 'coin', 5);
        $exchangeController->addNominalToWallet($walletId, 1, 'coin', 10);
        
        // Strategy 2: Most Coins
        echo "<h3>Strategy 2: Most Coins</h3>";
        echo "<div class='algorithm-steps'>
            <p><strong>Algorithm:</strong> Uses the minimum necessary banknotes, then maximizes coin usage.</p>
            <p><strong>Use case:</strong> When you want to use up small change.</p>
        </div>";
        
        try {
            $result = $exchangeController->exchangeAmount($walletId, 37, 'mostCoins');
            echo "<h4>Result:</h4>";
            echo "<ul>";
            $coinCount = 0;
            $banknoteCount = 0;
            foreach ($result as $value => $nominal) {
                echo "<li>{$nominal['count']}× {$value} zł ({$nominal['type']})</li>";
                if ($nominal['type'] == 'coin') {
                    $coinCount += $nominal['count'];
                } else {
                    $banknoteCount += $nominal['count'];
                }
            }
            echo "</ul>";
            echo "<p>Coins: $coinCount, Banknotes: $banknoteCount</p>";
        } catch (Exception $e) {
            echo "<p class='error'>✗ Exchange failed: {$e->getMessage()}</p>";
        }
        
        // Reload wallet
        $exchangeController->addNominalToWallet($walletId, 50, 'banknote', 1);
        $exchangeController->addNominalToWallet($walletId, 20, 'banknote', 2);
        $exchangeController->addNominalToWallet($walletId, 10, 'banknote', 3);
        $exchangeController->addNominalToWallet($walletId, 5, 'coin', 5);
        $exchangeController->addNominalToWallet($walletId, 2, 'coin', 5);
        $exchangeController->addNominalToWallet($walletId, 1, 'coin', 10);
        
        // Strategy 3: Preserve Large Denominations
        echo "<h3>Strategy 3: Preserve Large Denominations</h3>";
        echo "<div class='algorithm-steps'>
            <p><strong>Algorithm:</strong> Prioritizes using smaller denominations to keep larger ones available.</p>
            <p><strong>Use case:</strong> When you want to keep larger bills for future transactions.</p>
        </div>";
        
        try {
            $result = $exchangeController->exchangeAmount($walletId, 37, 'preserveLarge');
            echo "<h4>Result:</h4>";
            echo "<ul>";
            foreach ($result as $value => $nominal) {
                echo "<li>{$nominal['count']}× {$value} zł ({$nominal['type']})</li>";
            }
            echo "</ul>";
        } catch (Exception $e) {
            echo "<p class='error'>✗ Exchange failed: {$e->getMessage()}</p>";
        }
        
        // Show comparison of strategies
        echo "<h2>Strategy Comparison</h2>";
        echo "<div class='explanation'>
            <p>Each exchange strategy has specific advantages depending on the use case.</p>
        </div>";
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Wallet operation failed: {$e->getMessage()}</p>";
    }
} else {
    echo "<p class='error'>❌ Wallet not found</p>";
}

echo "<h2>System Design Notes</h2>";
echo "<div class='explanation'>
    <p>This system uses the Strategy design pattern to implement different exchange algorithms.</p>
</div>";

echo '</body></html>';
?>


<!-- HARD CODED -->