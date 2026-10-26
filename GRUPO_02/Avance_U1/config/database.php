
<?php

function conectarBD(): PDO
{
    $host = "127.0.0.1";
    $dbname = "helpdesk_rag_db";
    $usuario = "root";
    $password = "";

    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    return new PDO($dsn, $usuario, $password, $opciones);
}
