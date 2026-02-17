<?php
session_start();

$servidor   = "localhost";
$usuario_bd = "root";
$password_bd = "";
$nombre_bd  = "portal_viajes";

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4", $usuario_bd, $password_bd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_SESSION['usuario_id'])) {
        $stmt_usuario = $pdo->prepare("SELECT * FROM usuario WHERE id_usuario = ? LIMIT 1");
        $stmt_usuario->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
    } else {
        header('Location: inisesion.php');
        exit;
    }

    if (isset($_GET['confirmar']) && $_GET['confirmar'] == 'si') {
        $stmt_update = $pdo->prepare("UPDATE usuario SET rol = 'explorador' WHERE id_usuario = ?");
        $stmt_update->execute([$usuario['id_usuario']]);
        

        $_SESSION['rol'] = 'explorador';
        
        header('Location: userexplorador-perfil.php');
        exit;
    }

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cambio de Rol</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="estilo.css" />
</head>
<body class="fondo-cambio-rol">
    <div class="contenedor-cambio-rol">
        <div class="caja-cambio-rol">
            <h1 class="titulo-cambio-rol">¿Quieres convertirte en Explorador?</h1>
            
            <form action="" method="GET">
                <input type="hidden" name="confirmar" value="si">
                <button type="submit" class="boton-cambio-rol boton-si">Sí</button>
            </form>
            
            <form action="usernormal-perfil.php" method="GET">
                <button type="submit" class="boton-cambio-rol boton-volver">Volver</button>
            </form>
        </div>
    </div>
</body>
</html>
