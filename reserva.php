<?php

session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['username'])) {
        echo json_encode(['success' => false, 'error' => 'Debe iniciar sesión para realizar una reserva.']);
        exit;
    }

    $nombre = trim($_POST['nombre']);
    $dni = trim($_POST['dni']);
    $origen = trim($_POST['origen']);
    $destino = trim($_POST['destino']);
    $fecha_viaje = $_POST['fecha'];
    $cantidad = intval($_POST['cantidad']);
    $metodo_pago = trim($_POST['pago']);

    if (empty($nombre) || empty($dni) || empty($origen) || empty($destino) || empty($fecha_viaje) || $cantidad <= 0) {
        echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios.']);
        exit;
    }

    if ($origen === $destino) {
        echo json_encode(['success' => false, 'error' => 'El origen y el destino no pueden ser iguales.']);
        exit;
    }

    $stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
    if (!$stmt_user) {
        echo json_encode(['success' => false, 'error' => 'Error en la consulta de usuario: ' . $conn->error]);
        exit;
    }

    $stmt_user->bind_param("s", $_SESSION['username']);
    $stmt_user->execute();
    $user = $stmt_user->get_result()->fetch_assoc();

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Usuario no válido.']);
        exit;
    }
    $user_id = $user['id'];
    $stmt_user->close();

    $costos = [
        "Puno-Lima" => 80, "Lima-Puno" => 80,
        "Puno-Cusco" => 35, "Cusco-Puno" => 35,
        "Puno-Arequipa" => 25, "Arequipa-Puno" => 25,
        "Puno-Tacna" => 25, "Tacna-Puno" => 25
    ];

    $ruta = "$origen-$destino";

    if (!isset($costos[$ruta])) {
        echo json_encode(['success' => false, 'error' => 'La ruta seleccionada no está disponible.']);
        exit;
    }

    $costo_total = $costos[$ruta] * $cantidad;

    // Inserción en la tabla reservas
    $stmt_reservas = $conn->prepare("INSERT INTO reservas 
        (nombre, dni, origen, destino, fecha_viaje, cantidad, metodo_pago, user_id, costo_total) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt_reservas) {
        echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta: ' . $conn->error]);
        exit;
    }

    $stmt_reservas->bind_param(
        "sssssisid",
        $nombre, $dni, $origen, $destino, $fecha_viaje, $cantidad, $metodo_pago, $user_id, $costo_total
    );    

    if ($stmt_reservas->execute()) {
        // Obtener el ID de la reserva recién insertada
        $reserva_id = $stmt_reservas->insert_id;

        // Inserción en la tabla historial_viajes
        $stmt_historial = $conn->prepare("INSERT INTO historial_viajes 
        (user_id, nombre, dni, origen, destino, fecha_viaje, cantidad, metodo_pago, costo_total) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt_historial) {
        echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta de historial: ' . $conn->error]);
        exit;
        }

        $stmt_historial->bind_param(
        "isssssiid",  // 9 parámetros: 1 int, 5 strings, 1 int, 1 string, 1 double
        $user_id, $nombre, $dni, $origen, $destino, $fecha_viaje, $cantidad, $metodo_pago, $costo_total
        );


        if ($stmt_historial->execute()) {
            echo json_encode(['success' => true, 'costo' => $costo_total]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al guardar en historial_viajes: ' . $stmt_historial->error]);
        }

        $stmt_historial->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar en la base de datos de reservas: ' . $stmt_reservas->error]);
    }

    $stmt_reservas->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva de Boletos</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            text-align: center;
            font-size: 2.5em;
            margin: 30px 0;
            color: #4CAF50;
        }

        /* Contenedor del formulario */
        #reservation-form {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            font-size: 1.2em;
            margin-bottom: 10px;
            display: block;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1em;
            color: #333;
        }

        input[type="number"] {
            max-width: 150px;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px 25px;
            border: none;
            font-size: 1.2em;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Estilos para la selección de asientos */
        #asientos {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 20px;
        }

        .asiento {
            display: inline-block;
            width: 40px;
            height: 40px;
            margin: 5px;
            background-color: #ddd;
            text-align: center;
            line-height: 40px;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .asiento.seleccionado {
            background-color: #4CAF50;
            color: white;
        }

        .asiento:hover {
            background-color: #b0e57c;
        }

        /* Estilos para el boleto */
        #ticket {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            margin-top: 20px;
            width: 80%;
            max-width: 350px;
            margin-left: auto;
            margin-right: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #ticket h3 {
            text-align: center;
            color: #4CAF50;
            font-size: 1.6em;
        }

        #ticket p {
            font-size: 1.2em;
            margin: 10px 0;
        }

        #ticket strong {
            color: #555;
        }

        /* Estilos para el botón de descarga */
        #descargar-boleto {
            background-color: #4CAF50;
            color: white;
            padding: 12px 25px;
            border: none;
            font-size: 1.2em;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }

        #descargar-boleto:hover {
            background-color: #45a049;
        }

        /* Estilo de los mensajes de error */
        .error {
            color: red;
            font-size: 1.1em;
            text-align: center;
            margin-top: 20px;
        }

    </style>
</head>
<body>
    <h1>Reserva de Boletos</h1>
    <form id="reservation-form" method="POST" action="">
        <label for="nombre">Nombre Completo:</label>
        <input type="text" id="nombre" name="nombre" required>
        
        <label for="dni">DNI:</label>
        <input type="text" id="dni" name="dni" minlength="8" maxlength="8" required>

        <label for="origen">Origen:</label>
        <select id="origen" name="origen" required>
            <option value="Puno">Puno</option>
            <option value="Lima">Lima</option>
            <option value="Arequipa">Arequipa</option>
            <option value="Cusco">Cusco</option>
            <option value="Tacna">Tacna</option>
        </select>

        <label for="destino">Destino:</label>
        <select id="destino" name="destino" required>
            <option value="Puno">Puno</option>
            <option value="Lima">Lima</option>
            <option value="Arequipa">Arequipa</option>
            <option value="Cusco">Cusco</option>
            <option value="Tacna">Tacna</option>
        </select>

        <label for="fecha">Fecha del Viaje:</label>
        <input type="date" id="fecha" name="fecha" required>

        <label for="cantidad">Cantidad de Boletos:</label>
        <input type="number" id="cantidad" name="cantidad" min="1" required>

        <label for="pago">Método de Pago:</label>
        <select id="pago" name="pago" required>
            <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
            <option value="Tarjeta de Débito">Tarjeta de Débito</option>
            <option value="PayPal">PayPal</option>
        </select>
        <label for="asientos">Seleccionar Asientos:</label>
        <select id="asientos" name="asientos[]" multiple required>
            <option value="1A">1A</option>
            <option value="1B">1B</option>
            <option value="2A">2A</option>
            <option value="2B">2B</option>
            <option value="3A">3A</option>
            <option value="3B">3B</option>
        </select>

        <button type="submit">Reservar</button>
        <button type="button" id="descargar-boleto">Descargar Boleto</button>
    </form>

    <div id="boleto" style="display: none;">
        <h2>Boleto de Reserva</h2>
        <p><strong>Nombre:</strong> <span id="boleto-nombre"></span></p>
        <p><strong>DNI:</strong> <span id="boleto-dni"></span></p>
        <p><strong>Origen:</strong> <span id="boleto-origen"></span></p>
        <p><strong>Destino:</strong> <span id="boleto-destino"></span></p>
        <p><strong>Fecha:</strong> <span id="boleto-fecha"></span></p>
        <p><strong>Cantidad:</strong> <span id="boleto-cantidad"></span></p>
        <p><strong>Asientos:</strong> <span id="boleto-asientos"></span></p>
        <p><strong>Método de Pago:</strong> <span id="boleto-pago"></span></p>
    </div>
    </form>
</body>
</html>