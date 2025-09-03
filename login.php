<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "equipos";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, nombre, password FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_nombre, $hashed_pass);
        $stmt->fetch();

        if (password_verify($password, $hashed_pass)) {
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario_nombre'] = $db_nombre;
            header("Location: index.php");
            exit();
        } else {
            $error_message = "❌ Contraseña incorrecta.";
        }
    } else {
        $error_message = "⚠️ Usuario no encontrado.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - Quiniela NFL</title>
  <style>
    body { 
        font-family: Arial, sans-serif; 
        background: linear-gradient(135deg, #013369, #0a4a92);
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .box { 
        max-width: 400px; 
        width: 90%;
        padding: 2rem; 
        background: #fff;
        border-radius: 12px; 
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .logo {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .logo h1 {
        color: #013369;
        margin: 0;
        font-size: 1.8rem;
    }
    .logo p {
        color: #666;
        margin: 0.5rem 0 0 0;
        font-size: 0.9rem;
    }
    input { 
        width: 100%; 
        padding: 12px; 
        margin: 8px 0;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.2s;
    }
    input:focus {
        outline: none;
        border-color: #013369;
    }
    button { 
        width: 100%; 
        padding: 12px; 
        background: #013369; 
        color: #fff;
        border: none; 
        border-radius: 8px; 
        cursor: pointer;
        font-size: 1rem;
        transition: background 0.2s;
    }
    button:hover { 
        background: #0a4a92; 
    }
    .register-link {
        text-align: center;
        margin-top: 1rem;
    }
    .register-link a {
        color: #013369;
        text-decoration: none;
    }
    .register-link a:hover {
        text-decoration: underline;
    }
    .error {
        background: #f8d7da;
        color: #721c24;
        padding: 0.75rem;
        border-radius: 5px;
        margin-bottom: 1rem;
        text-align: center;
    }
  </style>
</head>
<body>
<div class="box">
  <div class="logo">
    <h1>🏈 Quiniela NFL</h1>
    <p>Temporada 2025</p>
  </div>
  
  <?php if ($error_message): ?>
    <div class="error"><?php echo $error_message; ?></div>
  <?php endif; ?>
  
  <form method="POST">
    <input type="text" name="nombre" placeholder="Nombre de usuario" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Iniciar Sesión</button>
  </form>
  
  <div class="register-link">
    <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
  </div>
</div>
</body>
</html>