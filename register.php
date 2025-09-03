<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "equipos";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $password = trim($_POST['password']);

    if (!empty($nombre) && !empty($password)) {
        // Encriptar contraseña
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $hashed_pass);

        if ($stmt->execute()) {
            echo "✅ Usuario registrado. <a href='login.php'>Inicia sesión</a>";
        } else {
            echo "⚠️ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "⚠️ Por favor llena todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <style>
    body { font-family: Arial; background: #f5f5f5; }
    .box { max-width: 400px; margin: 5rem auto; padding: 2rem; background: #fff;
           border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.2); }
    input { width: 100%; padding: 10px; margin: 8px 0; }
    button { width: 100%; padding: 10px; background: #013369; color: #fff;
             border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background: #0a4a92; }
  </style>
</head>
<body>
<div class="box">
  <h2>Registro</h2>
  <form method="POST">
    <input type="text" name="nombre" placeholder="Nombre de usuario" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Registrarse</button>
  </form>
  <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
</div>
</body>
</html>
