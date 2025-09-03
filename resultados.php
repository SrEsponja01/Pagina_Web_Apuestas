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
$dbname = "equipos";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Obtener semana actual (puedes cambiar esto)
$semana = isset($_GET['semana']) ? (int)$_GET['semana'] : 1;

// Consulta para obtener resultados del usuario con los resultados reales
$sql = "SELECT 
    p.id,
    p.equipo_local,
    p.equipo_visitante,
    p.semana,
    a.equipo_elegido,
    r.equipo_ganador,
    CASE 
        WHEN r.equipo_ganador = a.equipo_elegido THEN 'ganado'
        WHEN r.equipo_ganador IS NOT NULL AND r.equipo_ganador != a.equipo_elegido THEN 'perdido'
        ELSE 'pendiente'
    END as resultado
FROM partidos p
LEFT JOIN apuestas a ON p.id = a.id_partido AND a.usuario = ?
LEFT JOIN resultados r ON p.id = r.id_partido
WHERE p.semana = ?
ORDER BY p.id";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}

$stmt->bind_param("si", $usuario_nombre, $semana);
$stmt->execute();
$result = $stmt->get_result();

// Contar estadísticas
$total_partidos = 0;
$partidos_con_apuesta = 0;
$aciertos = 0;
$fallos = 0;
$pendientes = 0;

$partidos = [];
while ($row = $result->fetch_assoc()) {
    $partidos[] = $row;
    $total_partidos++;
    
    if ($row['equipo_elegido']) {
        $partidos_con_apuesta++;
        
        if ($row['resultado'] == 'ganado') {
            $aciertos++;
        } elseif ($row['resultado'] == 'perdido') {
            $fallos++;
        } else {
            $pendientes++;
        }
    }
}

// Obtener semanas disponibles
$semanas_sql = "SELECT DISTINCT semana FROM partidos ORDER BY semana";
$semanas_result = $conn->query($semanas_sql);
$semanas_disponibles = [];
while ($sem = $semanas_result->fetch_assoc()) {
    $semanas_disponibles[] = $sem['semana'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados - Quiniela NFL</title>
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
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .aciertos { color: #28a745; }
        .fallos { color: #dc3545; }
        .pendientes { color: #ffc107; }
        .total { color: #013369; }
        
        .semana-selector {
            background: #fff;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        
        .partidos-container {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .partido {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1rem;
            align-items: center;
        }
        .partido:last-child {
            border-bottom: none;
        }
        .partido-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .vs {
            color: #666;
            font-weight: bold;
        }
        .mi-apuesta {
            text-align: center;
            font-weight: bold;
        }
        .sin-apuesta {
            color: #999;
            font-style: italic;
        }
        .resultado {
            text-align: center;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-weight: bold;
        }
        .ganado {
            background: #d4edda;
            color: #155724;
        }
        .perdido {
            background: #f8d7da;
            color: #721c24;
        }
        .pendiente {
            background: #fff3cd;
            color: #856404;
        }
        .no-apostado {
            background: #e9ecef;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .partido {
                grid-template-columns: 1fr;
                gap: 0.5rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="home-btn">🏠 Inicio</a>
    <div class="header-content">
        <h1>Resultados - Semana <?php echo $semana; ?></h1>
        <p>Usuario: <?php echo htmlspecialchars($usuario_nombre); ?></p>
    </div>
    <div></div>
</header>

<div class="container">
    <!-- Selector de semanas -->
    <div class="semana-selector">
        <strong>Seleccionar Semana:</strong><br><br>
        <?php foreach ($semanas_disponibles as $sem): ?>
            <a href="?semana=<?php echo $sem; ?>" 
               class="semana-btn <?php echo ($sem == $semana) ? 'active' : ''; ?>">
                Semana <?php echo $sem; ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number aciertos"><?php echo $aciertos; ?></div>
            <div>Aciertos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number fallos"><?php echo $fallos; ?></div>
            <div>Fallos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number pendientes"><?php echo $pendientes; ?></div>
            <div>Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number total"><?php echo $partidos_con_apuesta; ?>/<?php echo $total_partidos; ?></div>
            <div>Apostados</div>
        </div>
    </div>
    
    <!-- Lista de partidos -->
    <div class="partidos-container">
        <?php if (empty($partidos)): ?>
            <div class="partido">
                <div style="text-align: center; color: #666; grid-column: 1/-1;">
                    No hay partidos para esta semana
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($partidos as $partido): ?>
                <div class="partido">
                    <div class="partido-info">
                        <strong><?php echo htmlspecialchars($partido['equipo_local']); ?></strong>
                        <span class="vs">vs</span>
                        <strong><?php echo htmlspecialchars($partido['equipo_visitante']); ?></strong>
                    </div>
                    
                    <div class="mi-apuesta">
                        <?php if ($partido['equipo_elegido']): ?>
                            <?php echo htmlspecialchars($partido['equipo_elegido']); ?>
                        <?php else: ?>
                            <span class="sin-apuesta">Sin apuesta</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="resultado <?php echo $partido['equipo_elegido'] ? $partido['resultado'] : 'no-apostado'; ?>">
                        <?php 
                        if (!$partido['equipo_elegido']) {
                            echo "No apostado";
                        } elseif ($partido['resultado'] == 'ganado') {
                            echo "✅ GANADO";
                        } elseif ($partido['resultado'] == 'perdido') {
                            echo "❌ PERDIDO";
                        } else {
                            echo "⏳ PENDIENTE";
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php if ($partidos_con_apuesta > 0): ?>
        <div style="margin-top: 2rem; text-align: center; color: #666;">
            <p><strong>Porcentaje de aciertos:</strong> 
            <?php echo $partidos_con_apuesta > 0 ? round(($aciertos / $partidos_con_apuesta) * 100, 1) : 0; ?>%</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>