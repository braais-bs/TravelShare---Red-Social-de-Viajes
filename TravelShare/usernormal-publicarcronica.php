<?php
session_start();

$servidor   = "localhost";
$usuario_bd = "root";
$password_bd = "";
$nombre_bd  = "portal_viajes";

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4", $usuario_bd, $password_bd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $usuario = null;
    if (isset($_SESSION['usuario_id'])) {
        $stmt_usuario = $pdo->prepare("SELECT * FROM usuario WHERE id_usuario = ? LIMIT 1");
        $stmt_usuario->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
    } 

    if (!$usuario) {
        header('Location: inisesion.php');
        exit;
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Publicar Crónica</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css" />
</head>
<body>
    <input type="checkbox" id="estado-menu" class="estado-menu" />
    <label for="estado-menu" class="boton-menu">☰</label>
    <div class="overlay"></div>

    <div class="estructura">
        
        <main>
            <header class="caja-encabezado-principal">
                <h1 class="encabezado-principal seccion-publicarcronica">Comparte tu experiencia</h1>
                <h2 class="oculto-para-accesibilidad">Comparte tu experiencia</h2>
                <p class="subtitulo-principal">Comparte tus momentos inolvidables para inspirar a otros viajeros</p>
            </header>

            <div class="caja-principal formulario-publicacion">
                <p class="mensaje-aviso-usuario">
                No puedes publicar una crónica si no eres Explorador<br>¡Únete a nosotros desde tu perfil!
                </p>
            </div>
        </main>
        <aside>
            <img class="foto-perfil" src="<?php echo htmlspecialchars($usuario['foto_perfil'] ?: 'imagenes/NoUser.png'); ?>" alt="Imagen de perfil" />
            <h3 class="nombre-usuario">@<?php echo htmlspecialchars($usuario['nombre_usuario']); ?></h3>
            <nav class="menu-principal">
                <div class="grupo-menu">
                    <a href="user-explorar.php" class="enlace-menu explorar">Explorar</a>
                    <a href="user-misdestinos.php" class="enlace-menu mis-destinos">Mis Destinos</a>
                    <a href="user-recomendaciones.php" class="enlace-menu recomendaciones">Recomendaciones</a>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="enlace-menu activo publicar-cronica">Publicar Crónica</a>
                    <a href="usernormal-perfil.php" class="enlace-menu perfil">Perfil</a>
                </div>
            </nav>
        </aside>
    </div>
</body>
</html>
