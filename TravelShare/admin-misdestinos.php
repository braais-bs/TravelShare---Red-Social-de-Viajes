<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: inisesion.php');
    exit;
}

$servidor   = "localhost";
$usuario_bd = "root";
$password_bd = "";
$nombre_bd  = "portal_viajes";

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4", $usuario_bd, $password_bd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $id_usuario = $_SESSION['usuario_id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_guardado'])) {
        $id_cronica_eliminar = (int)$_POST['id_cronica'];
        $stmt_delete = $pdo->prepare("DELETE FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
        $stmt_delete->execute([$id_usuario, $id_cronica_eliminar]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    $stmt_usuario = $pdo->prepare("SELECT * FROM usuario WHERE id_usuario = ? LIMIT 1");
    $stmt_usuario->execute([$id_usuario]);
    $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
    
    $stmt_destinos = $pdo->prepare("
        SELECT DISTINCT d.nombre AS destino, d.id_destino, c.titulo, c.imagen_principal, 
               c.fecha_publicacion, c.id_cronica
        FROM destino d
        JOIN cronica c ON d.id_destino = c.id_destino
        WHERE c.id_cronica IN (                
            SELECT id_cronica FROM cronica_guardada WHERE id_usuario = ?
        )
        AND c.estado = 'Publicada'
        ORDER BY c.fecha_publicacion DESC
    ");
    $stmt_destinos->execute([$id_usuario]);
    $destinos_usuario = $stmt_destinos->fetchAll(PDO::FETCH_ASSOC);
    
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
                        <?php if (!empty($destinos_usuario)): ?>
                            <?php foreach ($destinos_usuario as $cronica): ?>
                                <div class="contenedor-cronica">
                                    <form method="POST" class="form-eliminar-guardado">
                                        <input type="hidden" name="id_cronica" value="<?php echo $cronica['id_cronica']; ?>">
                                        <input type="hidden" name="eliminar_guardado" value="1">
                                        <button type="submit" class="boton-eliminar-guardado" title="Eliminar de guardados">−</button>
                                    </form>
                                    
                                    <a href="admin-cronica.php?id=<?php echo $cronica['id_cronica']; ?>" class="enlace-leer-cronica">
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
                <?php if (!empty($usuario)): ?>
                    <img class="foto-perfil"
                         src="<?php echo htmlspecialchars($usuario['foto_perfil'] ?: 'imagenes/NoUser.png'); ?>"
                         alt="Imagen de perfil" />
                    <h3 class="nombre-usuario">
                        @<?php echo htmlspecialchars(trim($usuario['nombre_usuario'])); ?>
                    </h3>
                <?php else: ?>
                    <img class="foto-perfil" src="imagenes/NoUser.png" alt="Imagen de perfil" />
                    <h3 class="nombre-usuario">@Usuario</h3>
                <?php endif; ?>

                <nav class="menu-principal">
                    <div class="grupo-menu">
                        <a href="admin-explorar.php" class="enlace-menu explorar">Explorar</a>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="enlace-menu activo mis-destinos">Mis Destinos</a>
                        <a href="admin-recomendaciones.php" class="enlace-menu recomendaciones">Recomendaciones</a>
                        <a href="admin-publicarcronica.php" class="enlace-menu publicar-cronica">Publicar Crónica</a>
                        <a href="admin-perfil.php" class="enlace-menu perfil">Perfil</a>                    
                        <a href="admin-pendiente.php" class="enlace-menu administrador">Administrador</a>
                    </div>
                </nav>
            </aside>
        </div>
    </body>
</html>
