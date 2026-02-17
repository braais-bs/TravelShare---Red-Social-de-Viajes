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
    } else {
        header('Location: inisesion.php');
        exit;
    }

    $id_usuario = $usuario['id_usuario'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_guardado'])) {
        $id_cronica_eliminar = (int)$_POST['id_cronica'];
        $stmt_delete = $pdo->prepare("DELETE FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
        $stmt_delete->execute([$id_usuario, $id_cronica_eliminar]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $stmt_guardadas = $pdo->prepare("
        SELECT c.*, d.nombre AS destino
        FROM cronica_guardada cg
        JOIN cronica c ON cg.id_cronica = c.id_cronica
        JOIN destino d ON c.id_destino = d.id_destino
        WHERE cg.id_usuario = ? AND c.estado = 'Publicada'
        ORDER BY cg.fecha_guardado DESC
    ");
    $stmt_guardadas->execute([$id_usuario]);
    $cronicas_guardadas = $stmt_guardadas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Mis Destinos</title>
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
                    <h1 class="encabezado-principal seccion-misdestinos">Mis Destinos</h1>
                    <p class="subtitulo-principal">Organiza y revive tus aventuras guardadas</p>
                </header>
                <div class="caja-principal">
                    <div class="cronicas-alargadas">
                        <?php if (!empty($cronicas_guardadas)): ?>
                            <?php foreach ($cronicas_guardadas as $cronica): ?>
                                <?php
                                $link_cronica = ($usuario['rol'] === 'explorador') 
                                                ? "userexplorador-cronica.php?id=" . $cronica['id_cronica']
                                                : "usernormal-cronica.php?id=" . $cronica['id_cronica'];
                                ?>
                                <div class="contenedor-cronica">
                                    <form method="POST" class="form-eliminar-guardado">
                                        <input type="hidden" name="id_cronica" value="<?php echo $cronica['id_cronica']; ?>">
                                        <input type="hidden" name="eliminar_guardado" value="1">
                                        <button type="submit" class="boton-eliminar-guardado" title="Eliminar de guardados">−</button>
                                    </form>
                                    
                                    <a href="<?php echo $link_cronica; ?>" class="enlace-leer-cronica">
                                        <article class="cronica-alargada">
                                            <img src="<?php echo htmlspecialchars($cronica['imagen_principal']); ?>" 
                                                alt="Foto <?php echo htmlspecialchars($cronica['destino']); ?>" />
                                            <div class="detalles">
                                                <h2 class="titulo-cronica-alargada"><?php echo htmlspecialchars($cronica['titulo']); ?></h2>
                                                <div class="destino"><?php echo htmlspecialchars($cronica['destino']); ?></div>
                                                <div class="fecha">
                                                    Publicado el <?php echo date('d/m/Y', strtotime($cronica['fecha_publicacion'])); ?>
                                                </div>
                                            </div>
                                        </article>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="sin-resultados">Todavía no tienes ningún destino guardado.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
            
            <aside>
                <img class="foto-perfil"
                     src="<?php echo htmlspecialchars($usuario['foto_perfil'] ?: 'imagenes/NoUser.png'); ?>"
                     alt="Imagen de perfil" />
                <h3 class="nombre-usuario">
                    @<?php echo htmlspecialchars(trim($usuario['nombre_usuario'])); ?>
                </h3>

                <nav class="menu-principal">
                    <div class="grupo-menu">
                        <a href="user-explorar.php" class="enlace-menu explorar">Explorar</a>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="enlace-menu activo mis-destinos">Mis Destinos</a>
                        <a href="user-recomendaciones.php" class="enlace-menu recomendaciones">Recomendaciones</a>                        
                        <?php if ($usuario['rol'] === 'explorador'): ?>
                             <a href="userexplorador-publicarcronica.php" class="enlace-menu publicar-cronica">Publicar Crónica</a>
                             <a href="userexplorador-perfil.php" class="enlace-menu perfil">Perfil</a>
                        <?php else: ?>
                             <a href="usernormal-publicarcronica.php" class="enlace-menu publicar-cronica">Publicar Crónica</a>
                             <a href="usernormal-perfil.php" class="enlace-menu perfil">Perfil</a>
                        <?php endif; ?>
                    </div>
                </nav>
            </aside>
        </div>
    </body>
</html>
