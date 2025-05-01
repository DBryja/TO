<?php
// Włącz raportowanie błędów dla debugowania
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Dołącz wymagane pliki
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
require_once __DIR__.'/../app/decorators/WalletDecorator.php';
require_once __DIR__.'/../app/factories/StrategyFactory.php';

// Inicjalizacja połączenia z bazą danych za pomocą wzorca Singleton
$db = Database::getInstance();

// Inicjalizacja modeli
$userModel = new User($db);
$walletModel = new Wallet($db);
$transactionModel = new Transaction($db);
$nominalModel = new Nominal($db);
$exchanger = new Exchanger($nominalModel);

// Inicjalizacja kontrolerów
$userController = new UserController($userModel);
$walletController = new WalletController($walletModel);
$transactionController = new TransactionController($transactionModel);
$exchangeController = new ExchangeController($exchanger, $nominalModel);

// Tworzenie użytkownika testowego z wypełnionym portfelem
try {
    $testUsername = "test_user";
    $testPassword = "test_password";

    // Rejestracja użytkownika testowego
    $userController->registerUser($testUsername, $testPassword);

    // Logowanie użytkownika testowego
    $testUser = $userController->loginUser($testUsername, $testPassword);
    if (!$testUser) {
        throw new Exception("Nie udało się zalogować użytkownika testowego.");
    }

    // Tworzenie portfela dla użytkownika testowego
    $testWalletId = $walletController->createWallet($testUser['id']);

    // Dodawanie nominałów do portfela testowego
    $walletController->addNominal($testWalletId, 50, 'banknote', 2); // 2x 50 zł
    $walletController->addNominal($testWalletId, 20, 'banknote', 3); // 3x 20 zł
    $walletController->addNominal($testWalletId, 10, 'banknote', 5); // 5x 10 zł
    $walletController->addNominal($testWalletId, 5, 'coin', 10);     // 10x 5 zł
    $walletController->addNominal($testWalletId, 2, 'coin', 15);     // 15x 2 zł
    $walletController->addNominal($testWalletId, 1, 'coin', 20);     // 20x 1 zł

    echo "<p class='success'>✓ Użytkownik testowy został utworzony. Login: <strong>$testUsername</strong>, Hasło: <strong>$testPassword</strong></p>";
    echo "<p class='success'>✓ Portfel testowy został wypełniony przykładowymi nominałami.</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Nie udało się utworzyć użytkownika testowego: {$e->getMessage()}</p>";
}

include __DIR__.'/../app/views/header.php';

// Formularz tworzenia portfela
?>
<h3>1. Utwórz Nowy Portfel</h3>
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
    <input type="text" name="username" placeholder="Nazwa użytkownika" required>
    <input type="password" name="password" placeholder="Hasło" required><br><br>

    <select name="decorator" required>
        <option value="none">Brak dekoratora</option>
        <option value="limitedWallet">Portfel z limitem dziennym</option>
    </select><br><br>

    <input type="number" name="limit" min="1" placeholder="Limit dzienny (opcjonalne)" disabled id="limitInput"><br><br>

    <input type="submit" name="create_wallet" value="Utwórz portfel">
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_wallet'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $decorator = $_POST['decorator'];
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : null;

    try {
        // Logowanie użytkownika
        $user = $userController->loginUser($username, $password);
        if (!$user) {
            throw new Exception("Nieprawidłowe dane logowania.");
        }

        // Tworzenie portfela
        $walletId = $walletController->createWallet($user['id']);
        $wallet = new BaseWallet($walletModel);

        // Dodaj dekorator, jeśli wybrano
        if ($decorator === 'limitedWallet' && $limit) {
            $wallet = new LimitedWalletDecorator($wallet, $limit);
        }

        echo "<p class='success'>✓ Portfel został utworzony pomyślnie (ID: $walletId)</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Błąd: {$e->getMessage()}</p>";
    }
}

// Formularz dodawania nominałów
?>
<h3>2. Dodaj Nominały do Portfela</h3>
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
    <input type="text" name="username" placeholder="Nazwa użytkownika" required>
    <input type="password" name="password" placeholder="Hasło" required><br><br>
    
    <select name="nominal" required>
        <option value="">Wybierz nominał</option>
        <option value="50">50 zł</option>
        <option value="20">20 zł</option>
        <option value="10">10 zł</option>
        <option value="5">5 zł</option>
        <option value="2">2 zł</option>
        <option value="1">1 zł</option>
    </select>

    <select name="type" required>
        <option value="">Wybierz typ</option>
        <option value="banknote">Banknot</option>
        <option value="coin">Moneta</option>
    </select>

    <input type="number" name="count" min="1" placeholder="Ilość" required>
    <input type="submit" name="add_nominal" value="Dodaj nominały">
</form>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_nominal'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nominal = (int)$_POST['nominal'];
    $type = $_POST['type'];
    $count = (int)$_POST['count'];

    try {
        // Logowanie użytkownika
        $user = $userController->loginUser($username, $password);
        if (!$user) {
            throw new Exception("Nieprawidłowe dane logowania.");
        }

        // Pobierz portfel użytkownika
        $wallets = $walletController->getWallets($user['id']);
        if (empty($wallets)) {
            throw new Exception("Użytkownik nie posiada portfela.");
        }

        $walletId = $wallets[0]['id'];

        // Dodaj nominały do portfela
        $walletController->addNominal($walletId, $nominal, $type, $count);

        echo "<p class='success'>✓ Dodano $count × $nominal zł ($type) do portfela (ID: $walletId)</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Błąd: {$e->getMessage()}</p>";
    }
}
?>

<h3>3. Wypłać Kwotę</h3>
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
    <input type="text" name="username" placeholder="Nazwa użytkownika" required>
    <input type="password" name="password" placeholder="Hasło" required><br><br>
    <input type="number" name="amount" min="1" placeholder="Kwota do wypłaty" required><br><br>

    <select name="strategy" required>
        <option value="">Wybierz strategię</option>
        <option value="fewestBills">Najmniej banknotów/monet</option>
        <option value="mostCoins">Najwięcej monet</option>
        <option value="preserveLarge">Zachowaj duże nominały</option>
    </select>

    <input type="submit" name="withdraw" value="Wypłać">
</form>
<?php
// Obsługa formularza wypłaty z dekoratorem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $amount = (int)$_POST['amount'];
    $strategy = $_POST['strategy'];

    try {
        // Logowanie użytkownika
        $user = $userController->loginUser($username, $password);
        if (!$user) {
            throw new Exception("Nieprawidłowe dane logowania.");
        }

        // Pobierz portfel użytkownika
        $wallets = $walletController->getWallets($user['id']);
        if (empty($wallets)) {
            throw new Exception("Użytkownik nie posiada portfela.");
        }

        $walletId = $wallets[0]['id'];

        // Dodaj dekorator, jeśli wybrano
        $wallet = new BaseWallet($walletModel);
        if ($decorator === 'limitedWallet') {
            $wallet = new LimitedWalletDecorator($wallet, 1000); // Limit dzienny: 1000 zł
        }

        // Ustaw strategię wymiany
        $exchangeStrategy = StrategyFactory::createStrategy($strategy);
        $exchanger->setStrategy($exchangeStrategy);

        // Wykonaj wymianę
        $result = $exchanger->exchange(new Amount($amount), $walletId);
        echo "<p class='success'>✓ Wypłata zakończona sukcesem: " . json_encode($result) . "</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Błąd: {$e->getMessage()}</p>";
    }
}
?>


<script>
    // Włącz/wyłącz pole limitu w zależności od wybranego dekoratora
    document.querySelector('select[name="decorator"]').addEventListener('change', function () {
        const limitInput = document.getElementById('limitInput');
        if (this.value === 'limitedWallet') {
            limitInput.disabled = false;
        } else {
            limitInput.disabled = true;
            limitInput.value = '';
        }
    });
</script>
</body>
</html>
<?php

/* 
// Zakomentowana hard-coded funkcjonalność
try {
    $db = Database::getInstance();
    echo "<p class='success'>✓ Połączono z bazą danych pomyślnie</p>";
    
    // Inicjalizacja modeli i kontrolerów
    $userModel = new User($db);
    $walletModel = new Wallet($db);
    $transactionModel = new Transaction($db);
    $nominalModel = new Nominal($db);
    $exchanger = new Exchanger($nominalModel);
    
    $userController = new UserController($userModel);
    $walletController = new WalletController($walletModel);
    $transactionController = new TransactionController($transactionModel);
    $exchangeController = new ExchangeController($exchanger, $nominalModel);
    echo "<p class='success'>✓ Komponenty systemu zostały zainicjalizowane</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Inicjalizacja systemu nie powiodła się: {$e->getMessage()}</p>";
    exit;
}

// Rejestracja i logowanie użytkownika
echo "<h2>Uwierzytelnianie użytkownika</h2>";
$username = "john_doe";
$password = "securepassword";

try {
    $userController->registerUser($username, $password);
    echo "<p class='success'>✓ Nowy użytkownik został pomyślnie zarejestrowany</p>";
} catch (Exception $e) {
    echo "<p>Uwaga: " . $e->getMessage() . " (Użycie istniejącego konta)</p>";
}

// Logowanie użytkownika
try {
    $user = $userController->loginUser($username, $password);
    if (!$user) {
        echo "<p class='error'>✗ Logowanie nie powiodło się - nieprawidłowe dane</p>";
        exit;
    }
    
    echo "<p class='success'>✓ Zalogowano pomyślnie jako $username (ID: {$user['id']})</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Logowanie nie powiodło się: {$e->getMessage()}</p>";
    exit;
}

// Operacje na portfelu
echo "<h2>Zarządzanie portfelem</h2>";

// Tworzenie portfela
try {
    $wallet_id = $walletController->createWallet($user['id']);
    echo "<p class='success'>✓ Utworzono portfel (ID: $wallet_id)</p>";
    
    // Pobierz portfele użytkownika
    $wallets = $walletController->getWallets($user['id']);
    echo "<p class='success'>✓ Pobrano " . count($wallets) . " portfel(e)</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Tworzenie portfela nie powiodło się: {$e->getMessage()}</p>";
    exit;
}
*/
