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

    $id_cronica = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id_cronica <= 0) {
        die("Crónica no especificada.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_usuario = $usuario['id_usuario'];
        $accion = $_POST['accion'] ?? '';
        
        if ($accion === 'guardar') {
            $stmt_check = $pdo->prepare("SELECT 1 FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
            $stmt_check->execute([$id_usuario, $id_cronica]);
            $ya_guardada = $stmt_check->fetch() !== false;

            if ($ya_guardada) {
                $stmt_delete = $pdo->prepare("DELETE FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
                $stmt_delete->execute([$id_usuario, $id_cronica]);
            } else {
                $stmt_insert = $pdo->prepare("INSERT INTO cronica_guardada (id_usuario, id_cronica) VALUES (?, ?)");
                $stmt_insert->execute([$id_usuario, $id_cronica]);
            }
        }
        
        if ($accion === 'recomendar') {
            $stmt_check = $pdo->prepare("SELECT 1 FROM cronica_recomendada WHERE id_usuario = ? AND id_cronica = ?");
            $stmt_check->execute([$id_usuario, $id_cronica]);
            $ya_recomendada = $stmt_check->fetch() !== false;

            if ($ya_recomendada) {
                $stmt_delete = $pdo->prepare("DELETE FROM cronica_recomendada WHERE id_usuario = ? AND id_cronica = ?");
                $stmt_delete->execute([$id_usuario, $id_cronica]);
                $stmt_update = $pdo->prepare("UPDATE cronica SET num_recomendados = num_recomendados - 1 WHERE id_cronica = ?");
                $stmt_update->execute([$id_cronica]);
            } else {
                $stmt_insert = $pdo->prepare("INSERT INTO cronica_recomendada (id_usuario, id_cronica) VALUES (?, ?)");
                $stmt_insert->execute([$id_usuario, $id_cronica]);
                $stmt_update = $pdo->prepare("UPDATE cronica SET num_recomendados = num_recomendados + 1 WHERE id_cronica = ?");
                $stmt_update->execute([$id_cronica]);
            }
        }
        
        if ($accion === 'comentar' && isset($_POST['comentario']) && !empty(trim($_POST['comentario']))) {
            $contenido = trim($_POST['comentario']);
            $stmt_com = $pdo->prepare("INSERT INTO comentario (contenido, id_usuario, id_cronica) VALUES (?, ?, ?)");
            $stmt_com->execute([$contenido, $id_usuario, $id_cronica]);
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id_cronica . "#comentarios-seccion");
        exit;
    }

    $stmt_cronica = $pdo->prepare("
        SELECT c.*, d.nombre AS nombre_destino 
        FROM cronica c
        JOIN destino d ON c.id_destino = d.id_destino
        WHERE c.id_cronica = ? AND c.estado = 'Publicada'
    ");
    $stmt_cronica->execute([$id_cronica]);
    $cronica = $stmt_cronica->fetch(PDO::FETCH_ASSOC);

    if (!$cronica) {
        die("Crónica no encontrada o no disponible.");
    }

    $stmt_comentarios = $pdo->prepare("
        SELECT co.*, u.nombre_usuario, u.foto_perfil
        FROM comentario co
        JOIN usuario u ON co.id_usuario = u.id_usuario
        WHERE co.id_cronica = ?
        ORDER BY co.fecha DESC
    ");
    $stmt_comentarios->execute([$id_cronica]);
    $comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);

    $stmt_g = $pdo->prepare("SELECT 1 FROM cronica_guardada WHERE id_usuario=? AND id_cronica=?");
    $stmt_g->execute([$usuario['id_usuario'], $id_cronica]);
    $esta_guardada = $stmt_g->fetch() !== false;

    $stmt_r = $pdo->prepare("SELECT 1 FROM cronica_recomendada WHERE id_usuario=? AND id_cronica=?");
    $stmt_r->execute([$usuario['id_usuario'], $id_cronica]);
    $esta_recomendada = $stmt_r->fetch() !== false;

    $stmt_imagenes = $pdo->prepare("SELECT ruta_imagen FROM imagen_carrusel WHERE id_cronica = ? ORDER BY id_imagen ASC");
    $stmt_imagenes->execute([$id_cronica]);
    $imagenes = $stmt_imagenes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo htmlspecialchars($cronica['titulo']); ?></title>
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
                    <h1 class="encabezado-principal seccion-cronica">Crónica de Viaje</h1>
                    <p class="subtitulo-principal">Lee la experiencia de viaje de otro usuario e interactúa con ella</p>
                </header>

                <div class="caja-principal cronica-detalle">
                    <img src="<?php echo htmlspecialchars($cronica['imagen_principal']); ?>" alt="Foto del viaje" class="imagen-cronica-miniatura">

                    <h2 class="titulo-cronica"><?php echo htmlspecialchars($cronica['titulo']); ?></h2>

                    <h3 class="apartado-cronica">Destino:</h3>
                    <p class="detalle-destino"><?php echo htmlspecialchars($cronica['nombre_destino']); ?></p>

                    <h3 class="apartado-cronica">Ruta:</h3>
                    <p class="detalle-ruta">
                        <?php echo nl2br(htmlspecialchars($cronica['ruta'])); ?>
                    </p>

                    <h3 class="apartado-cronica">Experiencia:</h3>
                    <p class="detalle-experiencia">
                        <?php echo nl2br(htmlspecialchars($cronica['experiencia'])); ?>
                    </p>

                    <?php if (!empty($imagenes)): ?>
                        <h3 class="apartado-cronica">Imágenes:</h3>
                        <div class="scroll-imagenes">
                            <?php foreach ($imagenes as $index => $img): ?>
                                <img src="<?php echo htmlspecialchars($img['ruta_imagen']); ?>" 
                                     alt="Foto del viaje <?php echo $index + 1; ?>">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="acciones-cronica">
                        <form method="POST" style="display:inline;">
                             <input type="hidden" name="accion" value="guardar">
                             <button type="submit" class="boton-accion">
                                <?php echo $esta_guardada ? 'Guardado' : 'Guardar'; ?>
                             </button>
                        </form>
                        
                        <form method="POST" style="display:inline;">
                             <input type="hidden" name="accion" value="recomendar">
                             <button type="submit" class="boton-accion">
                                <?php echo $esta_recomendada ? 'Recomendado' : 'Recomendar'; ?>
                             </button>
                        </form>
                    </div>

                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $id_cronica; ?>" method="POST" id="comentarios-seccion">
                        <input type="hidden" name="accion" value="comentar">
                        <label for="comentario" class="oculto-para-accesibilidad">Comentario</label>
                        <textarea id="comentario" name="comentario" required
                                  placeholder="Comenta esta experiencia..."
                                  class="comentario"></textarea>
                        <button type="submit" class="boton-enviar">Enviar comentario</button>
                    </form>
                </div>

                <div class="caja-principal comentarios">
                    <h3 class="apartado-comentarios">Comentarios (<?php echo count($comentarios); ?>)</h3>
                    <?php if (!empty($comentarios)): ?>
                        <?php foreach ($comentarios as $coment): ?>
                            <div class="comentario-item">
                                <p class="comentario-autor">
                                    <img src="<?php echo htmlspecialchars($coment['foto_perfil'] ?: 'imagenes/NoUser.png'); ?>" 
                                         alt="Perfil" class="comentario-foto-perfil">
                                    <?php echo htmlspecialchars(trim($coment['nombre_usuario'])); ?>
                                    <span class="comentario-fecha">
                                        (<?php echo date('d/m/Y H:i', strtotime($coment['fecha'])); ?>)
                                    </span>
                                </p>
                                <p class="comentario-texto">
                                    <?php echo nl2br(htmlspecialchars($coment['contenido'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="comentario-vacio">No hay comentarios</p>
                    <?php endif; ?>
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
                        <a href="userexplorador-publicarcronica.php" class="enlace-menu publicar-cronica">Publicar Crónica</a>
                        <a href="userexplorador-perfil.php" class="enlace-menu perfil">Perfil</a>
                    </div>
                </nav>
            </aside>
        </div>
    </body>
</html>
