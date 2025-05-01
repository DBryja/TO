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
require_once __DIR__.'/../app/models/User.php';
require_once __DIR__.'/../app/models/Wallet.php';
require_once __DIR__.'/../app/models/Transaction.php';
require_once __DIR__.'/../app/models/Nominal.php';
require_once __DIR__.'/../app/models/Exchanger.php';
require_once __DIR__.'/../app/models/Amount.php';
require_once __DIR__.'/../app/factories/StrategyFactory.php';

// Inicjalizacja połączenia z bazą danych za pomocą wzorca Singleton
$db = Database::getInstance();

// Inicjalizacja modeli
$userModel = new User($db);
$walletModel = new Wallet($db);
$transactionModel = new Transaction($db);

// Inicjalizacja kontrolerów
$walletController = new WalletController($walletModel);
$userController = new UserController($userModel);
$transactionController = new TransactionController($transactionModel, $walletModel);
$exchanger = new Exchanger($transactionController, $walletController);

// Tworzenie użytkownika testowego z wypełnionym portfelem
try {
    $testUsername = "test";
    $testPassword = "test";

    // Rejestracja użytkownika testowego
    $userController->registerUser($testUsername, $testPassword);

    // Logowanie użytkownika testowego
    $testUser = $userController->loginUser($testUsername, $testPassword);
    if (!$testUser) {
        throw new Exception("Nie udało się zalogować użytkownika testowego.");
    }

    // Tworzenie portfela dla użytkownika testowego
    $testWalletId = $walletModel->createWallet($testUser['id']);
    if (!$testWalletId) {
        throw new Exception("Nie udało się utworzyć portfela testowego.");
    }

    // Dodawanie nominałów do portfela testowego
    $transactionController->addNominal($testWalletId, 50, 'banknote', 2); // 2x 50 zł
    $transactionController->addNominal($testWalletId, 20, 'banknote', 3); // 3x 20 zł
    $transactionController->addNominal($testWalletId, 10, 'banknote', 5); // 5x 10 zł
    $transactionController->addNominal($testWalletId, 5, 'coin', 10);     // 10x 5 zł
    $transactionController->addNominal($testWalletId, 2, 'coin', 15);     // 15x 2 zł
    $transactionController->addNominal($testWalletId, 1, 'coin', 20);     // 20x 1 zł

    echo "<p class='success'>✓ Użytkownik testowy został utworzony. Login: <strong>$testUsername</strong>, Hasło: <strong>$testPassword</strong></p>";
    echo "<p class='success'>✓ Portfel testowy został wypełniony przykładowymi nominałami.</p>";
} catch (Exception $e) {
    echo "<p class='success'>✓ Użytkownik już istnieje: Login: <strong>$testUsername</strong>, Hasło: <strong>$testPassword</strong></p>";
}

include __DIR__.'/../app/views/header.php';
?>

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
        // Sprawdź, czy użytkownik istnieje
        $user = $userController->loginUser($username, $password);

        if (!$user) {
            // Jeśli użytkownik nie istnieje, zarejestruj go
            $userController->registerUser($username, $password);
            $user = $userController->loginUser($username, $password);

            if (!$user) {
                throw new Exception("Nie udało się utworzyć nowego użytkownika.");
            }

            echo "<p class='success'>✓ Utworzono nowego użytkownika: <strong>$username</strong></p>";
        }

        // Tworzenie portfela
        $walletId = $walletModel->createWallet($user['id']);
        echo "<p class='success'>✓ Portfel został utworzony pomyślnie (ID: $walletId)</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Błąd: {$e->getMessage()}</p>";
    }
}
?>

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
        // Logowanie użytkownika
        $user = $userController->loginUser($username, $password);
        if (!$user) {
            throw new Exception("Nieprawidłowe dane logowania.");
        }

        // Pobierz portfel użytkownika
        $wallet = $walletModel->getWallet($user['id']);
        if (!$wallet) {
            throw new Exception("Użytkownik nie posiada portfela.");
        }

        $walletId = $wallet['id'];

        // Pobierz nominały i oblicz sumę
        $nominals = $walletModel->getNominals($walletId);
        $total = $walletModel->getTotal($walletId);

        // Pobierz saldo transakcji
        $transactionBalance = $transactionController->getBalance($walletId);

        // Wyświetl zawartość portfela
        echo "<div class='wallet-content'>";
        echo "<h4>Zawartość portfela (ID: $walletId)</h4>";
        echo "<p><strong>Łączna suma nominałów:</strong> $total PLN</p>";
        echo "<p><strong>Saldo transakcji:</strong> {$transactionBalance} PLN</p>";

        echo "<h5>Spis nominałów:</h5>";
        if (count($nominals) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Nominał</th><th>Typ</th><th>Ilość</th><th>Wartość</th></tr>";

            foreach ($nominals as $nominal) {
                $value = $nominal['nominal'] * $nominal['count'];
                $type = $nominal['type'] === 'banknote' ? 'Banknot' : 'Moneta';
                echo "<tr>";
                echo "<td>{$nominal['nominal']} PLN</td>";
                echo "<td>$type</td>";
                echo "<td>{$nominal['count']} szt.</td>";
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
        // Logowanie użytkownika
        $user = $userController->loginUser($username, $password);
        if (!$user) {
            throw new Exception("Nieprawidłowe dane logowania.");
        }

        // Pobierz portfel użytkownika
        $walletId = ($walletController->getWallet($user['id']))['id'];
        if (!$walletId) {
            throw new Exception("Użytkownik nie posiada portfela.");
        }
        echo "<p class='success'>✓ Portfel użytkownika (ID: {$walletId})</p>";

        // Dodaj nominały do portfela i zarejestruj transakcję
        $transactionController->addNominal($walletId, $nominal, $type, $count);

        echo "<p class='success'>✓ Dodano $count × $nominal zł ($type) do portfela (ID: $walletId)</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Błąd: {$e->getMessage()}</p>";
    }
}
?>

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
        // Logowanie użytkownika
        $user = $userController->loginUser($username, $password);
        if (!$user) {
            throw new Exception("Nieprawidłowe dane logowania.");
        }

        // Pobierz portfel użytkownika
        $wallet = $walletModel->getWallet($user['id']);
        if (!$wallet) {
            throw new Exception("Użytkownik nie posiada portfela.");
        }

        $walletId = $wallet['id'];

        // Ustaw strategię wymiany
        $exchangeStrategy = StrategyFactory::createStrategy($strategy);

        // Wykonaj wypłatę i zarejestruj transakcję
        $result = $transactionController->withdrawAmount($walletId, $amount, $exchangeStrategy, $exchanger);

        // Wyświetl szczegóły wypłaty
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