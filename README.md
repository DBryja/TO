# Smart Wallet Exchange System

## Opis projektu

Smart Wallet Exchange System to aplikacja napisana w PHP, modelująca złożony problem podzielony na pod-problemy o charakterze nieliniowym. System umożliwia użytkownikowi tworzenie portfela cyfrowego, zarządzanie nominałami (monety i banknoty) oraz dokonywanie wypłat przy zastosowaniu różnych strategii wymiany. Ponadto dla bezpieczeństwa zapisywane są transakcje, dzięki którym możemy weryfikować zgodność zawartości portfela na podstawie śledzonych w nim zmian. Rozwiązanie zostało zaimplementowane zgodnie z zasadami SOLID oraz wykorzystuje wybrane wzorce projektowe (MVC, Repository, Strategy, Dependency Injection, Factory Method, Singleton pattern), dzięki czemu kod jest czytelny, łatwy do testowania i rozbudowy.

## Funkcjonalności

- **Zarządzanie użytkownikami:** Rejestracja, logowanie (prymitywne bez sesji itp.).
- **Zarządzanie portfelem:** Tworzenie portfela, dodawanie nominałów oraz przeglądanie zawartości portfela.
- **Wypłaty:** Wypłacanie określonej kwoty z portfela przy użyciu różnych strategii wymiany.

## Struktura projektu

```
TO
├── app\
│   ├── controllers\
│   │   └── ExchangeController.php
│   │   └── TransactionController.php
│   │   ├── UserController.php
│   │   ├── WalletController.php
│   ├── factories\
│   │   └── StrategyFactory.php
│   ├── models\
│   │   ├── Amount.php
│   │   ├── Database.php
│   │   ├── Exchanger.php
│   │   └── ExchangeStrategy.php
│   │   ├── Nominal.php
│   │   ├── User.php
│   │   ├── Wallet.php
│   ├── repositories\
│   │   ├── NominalRepository.php
│   │   ├── Repository.php
│   │   └── TransactionRepository.php
│   │   ├── UserRepository.php
│   │   ├── WalletRepository.php
│   └── components\
│       └── header.php
├── public\
│   └── index.php
```

## Diagram UML

![Diagram UML](/docs/diagramUML.svg)

## Zrzuty ekranu z działania programu

![Create User](/docs/createUser.png)
![Show Wallet](/docs/showWallet.png)
![Add To Wallet](/docs/addToWallet.png)
![Withdraw](/docs/withdraw.png)
![After Withdraw](/docs/afterWithdraw.png)

## Zasady SOLID oraz wykorzystane wzorce projektowe

- **SOLID:**

  - **Single Responsibility Principle:** Każda klasa odpowiada za jedną, wyraźnie określoną funkcjonalność – np. kontrolery (UserController, WalletController) zarządzają logiką interakcji, a repozytoria (UserRepository, WalletRepository itd.) odpowiadają jedynie za dostęp do danych.
  - **Open/Closed Principle:** Nowe strategie wymiany lub dodatkowe funkcje mogą być dodawane bez modyfikowania istniejącego kodu.
  - **Liskov Substitution & Interface Segregation:** Korzystanie z interfejsów (np. interfejs strategii wymiany) umożliwia zamienność implementacji.
  - **Dependency Inversion:** Warstwy wyższego poziomu (kontrolery) nie zależą od konkretnych implementacji repozytoriów, lecz od abstrakcji, w które są wstrzykiwane.

- **Wykorzystane wzorce:**
  - **MVC:** Model – reprezentacja danych (User, Wallet, Nominal itd.); Widok – interfejs użytkownika (index.php, header.php); Kontroler – logika biznesowa.
  - **Repository Pattern:** Centralizacja logiki dostępu do bazy w dedykowanych klasach repozytoryjnych.
  - **Strategy Pattern:** Implementacja wymiennych strategii wymiany przy wypłacie środków.
  - **Dependency Injection:** Wstrzykiwanie zależności (np. połączenie z bazą, repozytoria, strategie) odbywa się przez konstruktory.
  - **Factory Method:** Wykorzystany w klasie StrategyFactory, która odpowiada za tworzenie obiektów strategii wymiany. Na podstawie przekazanego parametru (np. nazwy strategii) zwraca konkretną implementację interfejsu strategii.
  - **Singleton:** Zaimplementowany w klasie Database, aby zapewnić istnienie tylko jednej instancji połączenia z bazą danych w całej aplikacji.
