<?php
// Iniciar sesión y verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "equipos";

$conn = new mysqli($host, $user, $pass, $dbname);

// Checar si hay error
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset para evitar problemas de caracteres
$conn->set_charset('utf8mb4');

// Obtener semana de la URL o usar la primera disponible
$semana_seleccionada = isset($_GET['semana']) ? (int)$_GET['semana'] : null;

// Si no se especifica semana, obtener la primera disponible
if (!$semana_seleccionada) {
    $primera_semana_sql = "SELECT MIN(semana) as primera FROM partidos";
    $primera_result = $conn->query($primera_semana_sql);
    if ($primera_result && $primera_row = $primera_result->fetch_assoc()) {
        $semana_seleccionada = $primera_row['primera'] ?: 1;
    } else {
        $semana_seleccionada = 1;
    }
}

// Obtener todas las semanas disponibles para el selector
$semanas_sql = "SELECT DISTINCT semana FROM partidos ORDER BY semana";
$semanas_result = $conn->query($semanas_sql);
$semanas_disponibles = [];
while ($sem = $semanas_result->fetch_assoc()) {
    $semanas_disponibles[] = $sem['semana'];
}

// Consulta de partidos para la semana seleccionada
$sql = "SELECT * FROM partidos WHERE semana = ? ORDER BY id";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}

$stmt->bind_param("i", $semana_seleccionada);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

// Verificar si el usuario ya tiene apuestas para esta semana
$usuario_nombre = $_SESSION['usuario_nombre'];
$apuestas_sql = "SELECT a.id_partido, a.equipo_elegido 
                FROM apuestas a 
                JOIN partidos p ON a.id_partido = p.id 
                WHERE a.usuario = ? AND p.semana = ?";
$apuestas_stmt = $conn->prepare($apuestas_sql);
$apuestas_stmt->bind_param("si", $usuario_nombre, $semana_seleccionada);
$apuestas_stmt->execute();
$apuestas_result = $apuestas_stmt->get_result();

$apuestas_existentes = [];
while ($apuesta = $apuestas_result->fetch_assoc()) {
    $apuestas_existentes[$apuesta['id_partido']] = $apuesta['equipo_elegido'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Quiniela NFL - Semana <?php echo $semana_seleccionada; ?></title>
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
      text-align: center;
      padding: 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header-content {
      flex: 1;
    }
    .home-btn {
      background: #ffcc00;
      color: #013369;
      padding: 0.5rem 1rem;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      transition: background 0.2s;
    }
    .home-btn:hover {
      background: #e6b800;
    }
    .container {
      max-width: 900px;
      margin: 2rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .semana-selector {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 2rem;
      text-align: center;
    }
    .semana-btn {
      display: inline-block;
      margin: 0.2rem;
      padding: 0.5rem 1rem;
      background: #e9ecef;
      color: #495057;
      text-decoration: none;
      border-radius: 5px;
      transition: all 0.2s;
    }
    .semana-btn.active {
      background: #013369;
      color: #fff;
    }
    .semana-btn:hover {
      background: #0a4a92;
      color: #fff;
    }
    .match {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #ddd;
      padding: 1rem 0;
    }
    .team {
      flex: 1;
      text-align: center;
    }
    .team input {
      margin-top: .5rem;
    }
    .submit-btn {
      margin-top: 2rem;
      display: block;
      width: 100%;
      padding: 1rem;
      background: #013369;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
    }
    .submit-btn:hover {
      background: #0a4a92;
    }
    .debug {
      background: #f0f0f0;
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 5px;
      font-family: monospace;
      font-size: 0.9rem;
    }
    .info-mensaje {
      background: #d1ecf1;
      color: #0c5460;
      padding: 1rem;
      border-radius: 5px;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>

<header>
  <a href="index.php" class="home-btn">🏠 Inicio</a>
  <div class="header-content">
    <h1>Quiniela NFL - Semana <?php echo $semana_seleccionada; ?></h1>
    <p>Usuario: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
  </div>
  <div></div> <!-- Spacer for flexbox balance -->
</header>

<div class="container">
  <!-- Selector de semanas -->
  <?php if (count($semanas_disponibles) > 1): ?>
  <div class="semana-selector">
    <strong>Seleccionar Semana:</strong><br><br>
    <?php foreach ($semanas_disponibles as $sem): ?>
      <a href="?semana=<?php echo $sem; ?>" 
         class="semana-btn <?php echo ($sem == $semana_seleccionada) ? 'active' : ''; ?>">
        Semana <?php echo $sem; ?>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Debug info -->
  <div class="debug">
    <strong>DEBUG:</strong> Total de partidos encontrados: <?php echo $result->num_rows; ?><br>
    Semana seleccionada: <?php echo $semana_seleccionada; ?><br>
    Apuestas existentes: <?php echo count($apuestas_existentes); ?>
  </div>

  <?php if (!empty($apuestas_existentes)): ?>
    <div class="info-mensaje">
      <strong>ℹ️ Información:</strong> Ya tienes <?php echo count($apuestas_existentes); ?> apuesta(s) para esta semana. 
      Puedes modificarlas seleccionando nuevos equipos.
    </div>
  <?php endif; ?>

  <form action="guardar_apuesta.php" method="POST">
    <!-- Campo oculto para enviar la semana -->
    <input type="hidden" name="semana" value="<?php echo $semana_seleccionada; ?>">
    
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {  
            // Usar "id" como primary key (según tu estructura de BD)
            $partido_id = $row['id'];
            $apuesta_previa = isset($apuestas_existentes[$partido_id]) ? $apuestas_existentes[$partido_id] : null;
            
            echo '
            <div class="match">
              <div class="team">
                <label>
                  <input type="radio" name="game'.$partido_id.'" value="'.$row["equipo_local"].'"'
                  .($apuesta_previa == $row["equipo_local"] ? ' checked' : '').'> 
                  '.$row["equipo_local"].'
                </label>
              </div>
              <span>vs</span>
              <div class="team">
                <label>
                  <input type="radio" name="game'.$partido_id.'" value="'.$row["equipo_visitante"].'"'
                  .($apuesta_previa == $row["equipo_visitante"] ? ' checked' : '').'> 
                  '.$row["equipo_visitante"].'
                </label>
              </div>
            </div>
            ';
        }
    } else {
        echo "<p>No hay partidos para esta semana.</p>";
    }
    ?>
    <button type="submit" class="submit-btn">Guardar Apuestas</button>
  </form>
</div>

</body>
</html>
<?php 
$stmt->close();
$apuestas_stmt->close();
$conn->close(); 
?>