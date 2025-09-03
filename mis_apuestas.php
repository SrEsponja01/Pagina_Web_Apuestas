<?php
session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$usuario_nombre = $_SESSION['usuario_nombre'];

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "equipos"; // Usar la misma base de datos

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Consulta usando la estructura real de tu BD
// La tabla partidos usa "id" como primary key, no "id_partido"
$sql = "SELECT a.*, p.equipo_local, p.equipo_visitante, p.semana 
        FROM apuestas a 
        JOIN partidos p ON a.id_partido = p.id 
        WHERE a.usuario = ? 
        ORDER BY p.semana DESC, a.id_apuesta DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}

$stmt->bind_param("s", $usuario_nombre);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Apuestas - Quiniela NFL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        header {
            background: #013369;
            color: #fff;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-content {
            flex: 1;
            text-align: center;
        }
        .home-btn {
            background: #ffcc00;
            color: #013369;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .home-btn:hover {
            background: #e6b800;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .apuesta {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .apuesta:last-child {
            border-bottom: none;
        }
        .partido-info {
            flex: 2;
        }
        .equipo-elegido {
            flex: 1;
            text-align: center;
            font-weight: bold;
            color: #013369;
        }
        .semana {
            background: #013369;
            color: #fff;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        .no-apuestas {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 2rem;
        }
        .stats {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="home-btn">🏠 Inicio</a>
    <div class="header-content">
        <h1>Mis Apuestas</h1>
        <p>Usuario: <?php echo htmlspecialchars($usuario_nombre); ?></p>
    </div>
    <div></div>
</header>

<div class="container">
    <?php if ($result->num_rows > 0): ?>
        <div class="stats">
            <strong>Total de apuestas:</strong> <?php echo $result->num_rows; ?>
        </div>
        
        <?php while ($fila = $result->fetch_assoc()): ?>
            <div class="apuesta">
                <div class="partido-info">
                    <span class="semana">Semana <?php echo $fila['semana']; ?></span>
                    <br>
                    <strong><?php echo htmlspecialchars($fila['equipo_local']); ?></strong> 
                    vs 
                    <strong><?php echo htmlspecialchars($fila['equipo_visitante']); ?></strong>
                    <br><small>ID Partido: <?php echo $fila['id_partido']; ?> | ID Apuesta: <?php echo $fila['id_apuesta']; ?></small>
                </div>
                <div class="equipo-elegido">
                    ➤ <?php echo htmlspecialchars($fila['equipo_elegido']); ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-apuestas">
            <h3>No tienes apuestas registradas aún</h3>
            <p>¡Ve a la quiniela y haz tus primeras apuestas!</p>
            <br>
            <a href="quiniela.php" class="home-btn">Ir a Quiniela</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>