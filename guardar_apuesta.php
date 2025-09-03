<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "equipos";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// (opcional) lanza excepciones útiles si hay errores mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// Obtener el nombre de usuario desde la sesión (según tu estructura de BD)
$usuario = $_SESSION['usuario_nombre'];

// Verifica que sí haya picks
if (empty($_POST)) {
    exit("No recibí apuestas. ¿Marcaste algún equipo? <a href='quiniela.php'>Volver</a> | <a href='index.php'>Inicio</a>");
}

// Prepara UNA vez, fuera del loop - usando "id_partido" y "usuario" según tu BD
$stmt = $conn->prepare(
    "INSERT INTO apuestas (id_partido, usuario, equipo_elegido)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE equipo_elegido = VALUES(equipo_elegido)"
);

if (!$stmt) {
    // Si falla, muestra el motivo
    die("Prepare falló: " . $conn->error);
}

$apuestas_guardadas = 0;

// Recorre los picks del formulario
foreach ($_POST as $name => $equipo) {
    // Espera names tipo "game<ID>"
    if (strpos($name, 'game') !== 0) continue;

    $id_partido = (int) substr($name, 4); // más seguro que str_replace
    if ($id_partido <= 0) continue;

    try {
        $stmt->bind_param("iss", $id_partido, $usuario, $equipo);
        $stmt->execute();
        $apuestas_guardadas++;
    } catch (Exception $e) {
        echo "Error al guardar apuesta para partido $id_partido: " . $e->getMessage() . "<br>";
    }
}

// Cierra solo si $stmt existe
$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Apuestas Guardadas</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f5f5f5; 
            margin: 0; 
            padding: 2rem; 
            text-align: center; 
        }
        .message {
            max-width: 500px;
            margin: 2rem auto;
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        .btn {
            display: inline-block;
            margin: 0.5rem;
            padding: 0.6rem 1.2rem;
            background: #013369;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #0a4a92;
        }
        .btn-home {
            background: #28a745;
        }
        .btn-home:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="message">
        <div class="success">
            ✅ ¡Tus apuestas han sido guardadas!
        </div>
        <p>Se guardaron <?php echo $apuestas_guardadas; ?> apuesta(s) correctamente.</p>
        <p>Usuario: <strong><?php echo htmlspecialchars($usuario); ?></strong></p>
        <div>
            <a href="index.php" class="btn btn-home">🏠 Inicio</a>
            <a href="quiniela.php" class="btn">🔄 Volver a Quiniela</a>
            <a href="mis_apuestas.php" class="btn">📊 Ver Mis Apuestas</a>
        </div>
    </div>
</body>
</html>