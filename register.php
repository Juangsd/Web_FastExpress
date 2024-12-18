<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir datos del formulario
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // Validar campos obligatorios
    if (empty($username) || empty($email) || empty($password)) {
        echo "<div class='message error'>Todos los campos son obligatorios.</div>";
        exit;
    }

    // Validar que el correo electrónico sea válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='message error'>El formato del correo electrónico no es válido.</div>";
        exit;
    }

    // Preparar la consulta de inserción
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "<div class='message error'>Error en la preparación de la consulta: " . $conn->error . "</div>";
        exit;
    }

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        echo "<div class='message success'>Registro exitoso. <a href='login.php'>Iniciar sesión</a></div>";
    } else {
        if ($conn->errno == 1062) { // Código de error para duplicados
            echo "<div class='message error'>El nombre de usuario o el correo ya están registrados.</div>";
        } else {
            echo "<div class='message error'>Error: " . $stmt->error . "</div>";
        }
    }

    // Cerrar conexiones
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <style>
        body {
            font-family: sans-serif;
            background: linear-gradient(135deg, rgb(86, 240, 145), rgb(47, 180, 129));
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: calc(100% - 12px);
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #0056b3;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #004494;
        }

        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message a {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registrarse</h2>
        <form method="POST" action="register.php">
            <label for="username">Usuario:</label>
            <input type="text" name="username" id="username" required>

            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Registrarse</button>
        </form>
    </div>
</body>
</html>
