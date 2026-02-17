<?php
$servidor   = "localhost";
$usuario_bd = "root";
$password_bd = "";
$nombre_bd  = "portal_viajes";

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4", $usuario_bd, $password_bd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt_cronicas = $pdo->prepare("
        SELECT c.*, d.nombre AS destino
        FROM cronica c
        JOIN destino d ON c.id_destino = d.id_destino
        WHERE c.estado = 'Publicada'
        ORDER BY c.fecha_publicacion DESC
    ");
    $stmt_cronicas->execute();
    $cronicas = $stmt_cronicas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Explorar</title>
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
                    <h1 class="encabezado-principal seccion-explorar">Descubre el Mundo</h1>
                    <p class="subtitulo-principal">Lo último que comparten nuestros exploradores</p>
                </header>

                <div class="caja-principal">

                    <div class="cronicas">
                        <?php if (!empty($cronicas)): ?>
                            <?php foreach ($cronicas as $cronica): ?>
                                <article class="cronica">
                                    <a href="inisesion.php" class="enlace-leer-cronica">
                                        <img src="<?php echo htmlspecialchars($cronica['imagen_principal']); ?>"
                                             alt="Foto de <?php echo htmlspecialchars($cronica['titulo']); ?>" />
                                        <h2 class="titulo-cronica"><?php echo htmlspecialchars($cronica['titulo']); ?></h2>
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay crónicas publicadas todavía.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>

            <aside>
                <img class="foto-perfil" src="imagenes/NoUser.png" alt="Imagen de perfil" />
                <h3 class="nombre-usuario">@Usuario</h3>

                <nav class="menu-principal">
                    <div class="grupo-menu">
                        <a href="index.php" class="enlace-menu activo explorar">Explorar</a>
                        <a href="inisesion.php" class="enlace-menu inicio-sesion">Inicia Sesión</a>
                    </div>
                </nav>
            </aside>
        </div>
    </body>
</html>
