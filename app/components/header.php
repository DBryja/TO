<!DOCTYPE html>
<html>
<head>
    <title>System Wymiany Portfela</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 1000px; margin: 64px auto; }
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
        .wallet-content {
        margin: 20px 0;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .wallet-content table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    
    .wallet-content th {
        background-color: #f2f2f2;
    }
    
    .wallet-content th, .wallet-content td {
        padding: 8px;
        text-align: left;
        border: 1px solid #ddd;
    }
    </style>
</head>
<body>
<h1>System Wymiany Portfela</h1>

<div class='explanation'>
    <p>Ten system demonstruje implementację cyfrowego portfela z zaawansowanymi strategiami wymiany.</p>
    <p>Umożliwia użytkownikom:
        <ul>
            <li>Tworzenie portfela</li>
            <li>Dodawanie różnych nominałów (monet i banknotów) do portfela</li>
            <li>Wymianę kwot przy użyciu różnych algorytmów dostosowanych do konkretnych potrzeb</li>
        </ul>
    </p>
</div>

<h2>Inicjalizacja Systemu</h2>