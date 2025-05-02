<?php
// Włącz raportowanie błędów dla debugowania
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../app/models/Database.php';
require_once __DIR__.'/../app/controllers/UserController.php';
require_once __DIR__.'/../app/controllers/WalletController.php';
require_once __DIR__.'/../app/controllers/TransactionController.php';
require_once __DIR__.'/../app/Repositories/UserRepository.php';
require_once __DIR__.'/../app/Repositories/WalletRepository.php';
require_once __DIR__.'/../app/Repositories/NominalRepository.php';
require_once __DIR__.'/../app/Repositories/TransactionRepository.php';
require_once __DIR__.'/../app/models/Exchanger.php';
require_once __DIR__.'/../app/models/Amount.php';
require_once __DIR__.'/../app/factories/StrategyFactory.php';

// Inicjalizacja połączenia z bazą danych za pomocą wzorca Singleton
$db = Database::getInstance();

// Inicjalizacja repozytoriów
$userRepository = new UserRepository($db);
$walletRepository = new WalletRepository($db);
$nominalRepository = new NominalRepository($db);
$transactionRepository = new TransactionRepository($db);

// Inicjalizacja kontrolerów
$userController = new UserController($userRepository);
$walletController = new WalletController($walletRepository, $nominalRepository);
$transactionController = new TransactionController($transactionRepository, $walletRepository, $nominalRepository);
$exchanger = new Exchanger();

// Tworzenie użytkownika testowego z wypełnionym portfelem
try {
    $testUsername = "test";
    $testPassword = "test";

    // Rejestracja użytkownika testowego (jeśli nie istnieje)
    $userController->registerUser($testUsername, $testPassword);

    // Logowanie użytkownika testowego
    $testUser = $userController->loginUser($testUsername, $testPassword);
    if (!$testUser) {
        throw new Exception("Nie udało się zalogować użytkownika testowego.");
    }

    // Tworzenie portfela dla użytkownika testowego
    $wallet = $walletController->createWallet($testUser->getId());
    if (!$wallet) {
        throw new Exception("Nie udało się utworzyć portfela testowego.");
    }

    // Dodawanie nominałów do portfela testowego
    $transactionController->addNominal($wallet->getId(), 50, 'banknote', 2); // 2x 50 zł
    $transactionController->addNominal($wallet->getId(), 20, 'banknote', 3); // 3x 20 zł
    $transactionController->addNominal($wallet->getId(), 10, 'banknote', 5); // 5x 10 zł
    $transactionController->addNominal($wallet->getId(), 5, 'coin', 10);     // 10x 5 zł
    $transactionController->addNominal($wallet->getId(), 2, 'coin', 15);     // 15x 2 zł
    $transactionController->addNominal($wallet->getId(), 1, 'coin', 20);     // 20x 1 zł

    echo "<p class='success'>✓ Użytkownik testowy został utworzony. Login: <strong>$testUsername</strong>, Hasło: <strong>$testPassword</strong></p>";
    echo "<p class='success'>✓ Portfel testowy został wypełniony przykładowymi nominałami.</p>";
} catch (Exception $e) {
    echo "<p class='success'>✓ Użytkownik już istnieje: Login: <strong>$testUsername</strong>, Hasło: <strong>$testPassword</strong></p>";
}

include __DIR__.'/../app/components/header.php';
?>

<!-- 1. Utwórz Nowy Portfel -->
<h3>1. Utwórz Nowy Portfel</h3>
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
    <input type="text" name="username" placeholder="Nazwa użytkownika" required>
    <input type="password" name="password" placeholder="Hasło" required><br><br>
    <input type="submit" name="create_wallet" value="Utwórz portfel">
</form>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_wallet'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Logowanie lub rejestracja użytkownika
        $user = $userController->loginUser($username, $password);
        if (!$user) {
            $userController->registerUser($username, $password);
            $user = $userController->loginUser($username, $password);
            if (!$user) {
                throw new Exception("Nie udało się utworzyć nowego użytkownika.");
            }
            echo "<p class='success'>✓ Utworzono nowego użytkownika: <strong>$username</strong></p>";
        }
        // Tworzenie portfela
        $wallet = $walletController->createWallet($user->getId());
        echo "<p class='success'>✓ Portfel został utworzony pomyślnie (ID: {$wallet->getId()})</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Błąd: {$e->getMessage()}</p>";
    }
}
?>

<!-- 2. Pokaż Zawartość Portfela -->
<h3>2. Pokaż Zawartość Portfela</h3>
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
    <input type="text" name="username" placeholder="Nazwa użytkownika" required>
    <input type="password" name="password" placeholder="Hasło" required><br><br>
    <input type="submit" name="show_wallet" value="Pokaż portfel">
</form>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['show_wallet'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $user = $userController->loginUser($username, $password);
        if (!$user) {
            throw new Exception("Nieprawidłowe dane logowania.");
        }

        $wallet = $walletController->getWallet($user->getId());
        if (!$wallet) {
            throw new Exception("Użytkownik nie posiada portfela.");
        }

        $walletId = $wallet->getId();
        $nominals = $wallet->getNominals();
        $total = $walletController->getTotal($walletId);
        $transactionBalance = $transactionController->getBalance($walletId);

        echo "<div class='wallet-content'>";
        echo "<h4>Zawartość portfela (ID: $walletId)</h4>";
        echo "<p><strong>Łączna suma nominałów:</strong> $total PLN</p>";
        echo "<p><strong>Saldo transakcji:</strong> {$transactionBalance} PLN</p>";
        echo "<h5>Spis nominałów:</h5>";
        if (count($nominals) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Nominał</th><th>Typ</th><th>Ilość</th><th>Wartość</th></tr>";
            foreach ($nominals as $nominal) {
                $value = $nominal->getValue() * $nominal->getCount();
                $type = $nominal->getType() === 'banknote' ? 'Banknot' : 'Moneta';
                echo "<tr>";
                echo "<td>{$nominal->getValue()} PLN</td>";
                echo "<td>$type</td>";
                echo "<td>{$nominal->getCount()} szt.</td>";
                echo "<td>$value PLN</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Portfel jest pusty.</p>";
        }
        echo "</div>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Błąd: {$e->getMessage()}</p>";
    }
}
?>

<!-- 3. Dodaj Nominały do Portfela -->
<h3>3. Dodaj Nominały do Portfela</h3>
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
        $user = $userController->loginUser($username, $password);
        if (!$user) {
            throw new Exception("Nieprawidłowe dane logowania.");
        }
        $wallet = $walletController->getWallet($user->getId());
        if (!$wallet) {
            throw new Exception("Użytkownik nie posiada portfela.");
        }
        $walletId = $wallet->getId();
        $transactionController->addNominal($walletId, $nominal, $type, $count);
        echo "<p class='success'>✓ Dodano $count × $nominal zł ($type) do portfela (ID: $walletId)</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Błąd: {$e->getMessage()}</p>";
    }
}
?>

<!-- 4. Wypłać Kwotę -->
<h3>4. Wypłać Kwotę</h3>
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $amount = (int)$_POST['amount'];
    $strategy = $_POST['strategy'];

    try {
        $user = $userController->loginUser($username, $password);
        if (!$user) {
            throw new Exception("Nieprawidłowe dane logowania.");
        }

        $wallet = $walletController->getWallet($user->getId());
        if (!$wallet) {
            throw new Exception("Użytkownik nie posiada portfela.");
        }
        $walletId = $wallet->getId();
        $exchangeStrategy = StrategyFactory::createStrategy($strategy);
        $result = $transactionController->withdrawAmount($walletId, $amount, $exchangeStrategy, $exchanger);

        echo "<div class='success'>";
        echo "<p>✓ Wypłata zakończona sukcesem: $amount PLN</p>";
        echo "<p>Użyta strategia: $strategy</p>";
        echo "<p>Wydane nominały:</p>";
        echo "<ul>";
        foreach ($result as $value => $nominal) {
            echo "<li>$value PLN ({$nominal['type']}): {$nominal['count']} szt.</li>";
        }
        echo "</ul>";
        echo "</div>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Błąd: {$e->getMessage()}</p>";
    }
}
?>