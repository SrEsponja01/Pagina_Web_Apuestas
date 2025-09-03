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

// Consulta de partidos (ejemplo: semana 1)
$semana = 1;

// Consulta con manejo de errores - usando "id" como primary key
$sql = "SELECT * FROM partidos WHERE semana = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}

$stmt->bind_param("i", $semana);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error en la consulta: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Quiniela NFL - Semana <?php echo $semana; ?></title>
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
  </style>
</head>
<body>

<header>
  <a href="index.php" class="home-btn">🏠 Inicio</a>
  <div class="header-content">
    <h1>Quiniela NFL - Semana <?php echo $semana; ?></h1>
  </div>
  <div></div> <!-- Spacer for flexbox balance -->
</header>

<div class="container">
  <!-- Debug info -->
  <div class="debug">
    <strong>DEBUG:</strong> Total de partidos encontrados: <?php echo $result->num_rows; ?><br>
    Usuario logueado: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
  </div>

  <form action="guardar_apuesta.php" method="POST">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {  
            // Debug: mostrar todas las columnas disponibles
            echo "<!-- DEBUG ROW: " . print_r($row, true) . " -->\n";
            
            // Usar "id" como primary key (según tu estructura de BD)
            $partido_id = $row['id'];
            
            echo '
            <div class="match">
              <div class="team">
                <label>
                  <input type="radio" name="game'.$partido_id.'" value="'.$row["equipo_local"].'"> '.$row["equipo_local"].'
                </label>
              </div>
              <span>vs</span>
              <div class="team">
                <label>
                  <input type="radio" name="game'.$partido_id.'" value="'.$row["equipo_visitante"].'"> '.$row["equipo_visitante"].'
                </label>
              </div>
            </div>
            ';
        }
    } else {
        echo "<p>No hay partidos para esta semana.</p>";
        echo "<div class='debug'>Consulta ejecutada: " . htmlspecialchars($sql) . " con semana = " . $semana . "</div>";
    }
    ?>
    <button type="submit" class="submit-btn">Guardar Apuestas</button>
  </form>
</div>

</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>