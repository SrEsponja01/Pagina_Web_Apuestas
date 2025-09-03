<?php
session_start();

// Si no está logueado, mandarlo al login
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario_nombre']; // Nombre del usuario logueado
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Quiniela NFL</title>
  <style>
    * { margin:0; padding:0; box-sizing: border-box; }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f5f5;
      color: #333;
    }

    header {
      background: #013369;
      color: #fff;
      padding: 2rem;
      text-align: center;
      box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    }

    header h1 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }

    header p {
      font-size: 1.1rem;
      color: #f0f0f0;
    }

    .bienvenida {
      margin-top: 1rem;
      font-size: 1rem;
      color: #ffcc00;
    }

    .container {
      max-width: 1000px;
      margin: 2rem auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      padding: 0 1rem;
    }

    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      padding: 1.5rem;
      text-align: center;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 14px rgba(0,0,0,0.15);
    }

    .card h2 {
      color: #d50a0a;
      margin-bottom: 1rem;
    }

    .card p {
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
      line-height: 1.4;
    }

    .card a {
      text-decoration: none;
      display: inline-block;
      padding: 0.6rem 1.2rem;
      background: #013369;
      color: #fff;
      border-radius: 8px;
      transition: background 0.2s;
    }

    .card a:hover {
      background: #0a4a92;
    }

    .logout {
      display: inline-block;
      margin-top: 1rem;
      padding: 0.5rem 1rem;
      background: #d50a0a;
      color: #fff;
      border-radius: 8px;
      text-decoration: none;
    }

    .logout:hover {
      background: #a00;
    }

    footer {
      text-align: center;
      padding: 1.5rem;
      margin-top: 3rem;
      color: #555;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

<header>
  <h1>Quiniela NFL 2025</h1>
  <p>Elige tus ganadores de la semana y compite con tus amigos</p>
  <div class="bienvenida">
    Bienvenido, <?php echo htmlspecialchars($usuario); ?> 👋
    <br>
    <a class="logout" href="logout.php">Cerrar sesión</a>
  </div>
</header>

<div class="container">
  <div class="card">
    <h2>Quiniela</h2>
    <p>Revisa los partidos disponibles y marca tus apuestas por semana.</p>
    <a href="quiniela.php">Ir a Quiniela</a>
  </div>

  <div class="card">
    <h2>Resultados</h2>
    <p>Mira qué equipos ganaron y quién va liderando la quiniela.</p>
    <a href="resultados.php">Ver Resultados</a>
  </div>

  <div class="card">
    <h2>Mis Apuestas</h2>
    <p>Consulta tus apuestas anteriores y sigue tu progreso semana a semana.</p>
    <a href="mis_apuestas.php">Ver Mis Apuestas</a>
  </div>
</div>

<footer>
  &copy; 2025 Quiniela NFL | Hecho por Angelon
</footer>

</body>
</html>