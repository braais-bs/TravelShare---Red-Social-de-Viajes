<?php
session_start();

$servidor   = "localhost";
$usuario_bd = "root";
$password_bd = "";
$nombre_bd  = "portal_viajes";

$mensaje_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4", $usuario_bd, $password_bd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $nombre_usuario = trim($_POST['usuario']);
        $contraseña = $_POST['contraseña'];
        
        $stmt = $pdo->prepare("SELECT id_usuario, nombre_usuario, contrasena, rol FROM usuario WHERE nombre_usuario = :usuario LIMIT 1");
        $stmt->execute([':usuario' => $nombre_usuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && $contraseña === $usuario['contrasena']) {
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
            $_SESSION['rol'] = $usuario['rol'];
            
            if ($usuario['rol'] === 'administrador') {
                header('Location: admin-explorar.php');
            } else {
                header('Location: user-explorar.php');
            }
            exit;
        } else {
            $mensaje_error = "Usuario o contraseña incorrectos.";
        }
        
    } catch (PDOException $e) {
        $mensaje_error = "Error de conexión.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Inicio de Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css" />
    
    <style>
    .mensaje-error {
        color: rgb(201, 31, 31) !important;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        text-align: center;
    }
    </style>

</head>
<body class="fondo-login">
    <div class="contenedor-login">
        <div class="login-caja">
            <h1 class="titulo-login">Bienvenido a TravelShare</h1>
            <p class="subtitulo-login">Comparte, explora y comenta experiencias de viaje</p>
            
            <?php if ($mensaje_error): ?>
                <div class="mensaje-error"><?php echo $mensaje_error; ?></div>
            <?php endif; ?>
            
            <form class="formulario-login" action="inisesion.php" method="POST">
                <label for="nombreusuario" class="oculto-para-accesibilidad">Nombre de usuario</label>
                <input id="nombreusuario" type="text" name="usuario" placeholder="Nombre de usuario" required autocomplete="off" />

                <label for="contraseña" class="oculto-para-accesibilidad">Contraseña</label>
                <input id="contraseña" type="password" name="contraseña" placeholder="Contraseña" required autocomplete="off" />

                <button type="submit" class="boton-login">Iniciar Sesión</button>
            </form>
            
            <p class="registro-texto">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
        </div>
    </div>
</body>
</html>
