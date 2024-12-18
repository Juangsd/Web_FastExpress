<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "encomiendas";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Capturar los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_remitente = $_POST['nombre_remitente'];
    $nombre_destinatario = $_POST['nombre_destinatario'];
    $descripcion = $_POST['descripcion'];
    $peso = $_POST['peso'];
    $destino = $_POST['destino'];
    $tipo_envio = $_POST['tipo_envio'];

    // Insertar datos en la tabla
    $sql = "INSERT INTO paquetes (nombre_remitente, nombre_destinatario, descripcion, peso, destino)
            VALUES ('$nombre_remitente', '$nombre_destinatario', '$descripcion', '$peso', '$destino')";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Registro exitoso.</p>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Mostrar los registros almacenados
$sql = "SELECT * FROM paquetes";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Paquetes</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #004080;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Lista de Paquetes Registrados</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Remitente</th>
            <th>Destinatario</th>
            <th>Descripción</th>
            <th>Peso (kg)</th>
            <th>Destino</th>
            <th>Fecha de Registro</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . $row['id'] . "</td>
                    <td>" . htmlspecialchars($row['nombre_remitente']) . "</td>
                    <td>" . htmlspecialchars($row['nombre_destinatario']) . "</td>
                    <td>" . htmlspecialchars($row['descripcion']) . "</td>
                    <td>" . $row['peso'] . "</td>
                    <td>" . htmlspecialchars($row['destino']) . "</td>
                    <td>" . $row['fecha_registro'] . "</td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No hay registros disponibles.</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>
