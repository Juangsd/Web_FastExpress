<?php
$servername = "localhost"; // Cambia a tu servidor de base de datos
$username = "root"; // Cambia a tu usuario
$password = ""; // Cambia a tu contraseña
$dbname = "fast_express"; // Nombre de la base de datos

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
