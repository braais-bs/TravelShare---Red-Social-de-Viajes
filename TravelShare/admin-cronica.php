<?php
session_start();

$servidor   = "localhost";
$usuario_bd = "root";
$password_bd = "";
$nombre_bd  = "portal_viajes";

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4", $usuario_bd, $password_bd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt_usuario = $pdo->prepare("SELECT * FROM usuario WHERE id_usuario = ? LIMIT 1");
    $stmt_usuario->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cronica'])) {
        $id_usuario = $_SESSION['usuario_id'];
        $id_cronica = (int)$_POST['id_cronica'];
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
        } elseif ($accion === 'recomendar') {
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
        } elseif ($accion === 'eliminar') {
            $id_cronica_eliminar = (int)$_POST['id_cronica'];
            
            $stmt_update = $pdo->prepare("UPDATE cronica SET estado = 'Rechazada' WHERE id_cronica = ?");
            $stmt_update->execute([$id_cronica_eliminar]);
            $pdo->prepare("DELETE FROM comentario WHERE id_cronica = ?")->execute([$id_cronica_eliminar]);
            $pdo->prepare("DELETE FROM cronica_guardada WHERE id_cronica = ?")->execute([$id_cronica_eliminar]);
            $pdo->prepare("DELETE FROM cronica_recomendada WHERE id_cronica = ?")->execute([$id_cronica_eliminar]);

            $stmt_reset = $pdo->prepare("UPDATE cronica SET num_recomendados = 0 WHERE id_cronica = ?");
            $stmt_reset->execute([$id_cronica_eliminar]);
            
            header('Location: admin-explorar.php');
            exit;
        } elseif ($accion === 'comentar') {
            $comentario = trim($_POST['comentario']);
            if (!empty($comentario)) {
                $stmt_coment = $pdo->prepare("INSERT INTO comentario (id_cronica, id_usuario, contenido, fecha) VALUES (?, ?, ?, NOW())");
                $stmt_coment->execute([$id_cronica, $id_usuario, $comentario]);
            }
        }

        header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id_cronica);
        exit;
    }

    $id_cronica = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $stmt = $pdo->prepare("
        SELECT c.*, d.nombre AS nombre_destino, u.nombre AS autor_nombre, u.apellido1 AS autor_apellido
        FROM cronica c
        JOIN destino d ON c.id_destino = d.id_destino
        JOIN usuario u ON c.id_usuario = u.id_usuario
        WHERE c.id_cronica = ?
        LIMIT 1
    ");
    $stmt->execute([$id_cronica]);
    $cronica = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cronica) {
        die('Crónica no encontrada');
    }

    $stmt_check = $pdo->prepare("SELECT 1 FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
    $stmt_check->execute([$_SESSION['usuario_id'], $id_cronica]);
    $ya_guardada = $stmt_check->fetch() !== false;

    $stmt_check = $pdo->prepare("SELECT 1 FROM cronica_recomendada WHERE id_usuario = ? AND id_cronica = ?");
    $stmt_check->execute([$_SESSION['usuario_id'], $id_cronica]);
    $ya_recomendada = $stmt_check->fetch() !== false;

    $stmt_coment = $pdo->prepare("
        SELECT c.contenido, c.fecha, u.nombre_usuario, u.nombre, u.apellido1, u.foto_perfil
        FROM comentario c
        JOIN usuario u ON c.id_usuario = u.id_usuario
        WHERE c.id_cronica = ?
        ORDER BY c.fecha DESC
    ");
    $stmt_coment->execute([$id_cronica]);
    $comentarios = $stmt_coment->fetchAll(PDO::FETCH_ASSOC);

    $stmt_imagenes = $pdo->prepare("SELECT ruta_imagen FROM imagen_carrusel WHERE id_cronica = ? ORDER BY id_imagen ASC");
    $stmt_imagenes->execute([$id_cronica]);
    $imagenes = $stmt_imagenes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($cronica['titulo']); ?> - Admin</title>
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
                <h1 class="encabezado-principal seccion-cronica"><?php echo htmlspecialchars($cronica['titulo']); ?></h1>
                <p class="subtitulo-principal">
                    Experiencia de viaje de <?php echo htmlspecialchars($cronica['autor_nombre'].' '.$cronica['autor_apellido']); ?>
                    | <?php echo $cronica['num_recomendados']; ?> recomendaciones
                </p>
            </header>

            <div class="caja-principal cronica-detalle">
                <img src="<?php echo htmlspecialchars($cronica['imagen_principal']); ?>"
                     alt="Foto del viaje" class="imagen-cronica-miniatura">

                <h2 class="titulo-cronica"><?php echo htmlspecialchars($cronica['titulo']); ?></h2>

                <h3 class="apartado-cronica">Destino:</h3>
                <p class="detalle-destino"><?php echo htmlspecialchars($cronica['nombre_destino']); ?></p>

                <h3 class="apartado-cronica">Ruta:</h3>
                <p class="detalle-ruta"><?php echo nl2br(htmlspecialchars($cronica['ruta'])); ?></p>

                <h3 class="apartado-cronica">Experiencia:</h3>
                <p class="detalle-experiencia"><?php echo nl2br(htmlspecialchars($cronica['experiencia'])); ?></p>

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
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id_cronica" value="<?php echo $cronica['id_cronica']; ?>">
                        <input type="hidden" name="accion" value="guardar">
                        <button type="submit" class="boton-accion <?php echo $ya_guardada ? 'boton-accion' : ''; ?>">
                            <?php echo $ya_guardada ? 'Guardado' : 'Guardar'; ?>
                        </button>
                    </form>

                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id_cronica" value="<?php echo $cronica['id_cronica']; ?>">
                        <input type="hidden" name="accion" value="recomendar">
                        <button type="submit" class="boton-accion <?php echo $ya_recomendada ? 'boton-accion' : ''; ?>">
                            <?php echo $ya_recomendada ? 'Recomendada' : 'Recomendar'; ?>
                        </button>
                    </form>

                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id_cronica" value="<?php echo $cronica['id_cronica']; ?>">
                        <input type="hidden" name="accion" value="eliminar">
                        <button type="submit" class="boton-accion boton-eliminar">Eliminar</button>
                    </form>
                </div>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $id_cronica; ?>" method="POST">
                    <input type="hidden" name="id_cronica" value="<?php echo $cronica['id_cronica']; ?>">
                    <input type="hidden" name="accion" value="comentar">
                    <label for="comentario" class="oculto-para-accesibilidad">Comentario Admin</label>
                    <textarea id="comentario" name="comentario" required
                              placeholder="Añade comentario como administrador..."
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
            <img class="foto-perfil"
                 src="<?php echo htmlspecialchars($usuario['foto_perfil'] ?: 'imagenes/NoUser.png'); ?>"
                 alt="Admin" />
            <h3 class="nombre-usuario">@<?php echo htmlspecialchars(trim($usuario['nombre_usuario'])); ?></h3>

            <nav class="menu-principal">
                <div class="grupo-menu">
                    <a href="admin-explorar.php" class="enlace-menu explorar">Explorar</a>
                    <a href="admin-misdestinos.php" class="enlace-menu mis-destinos">Mis Destinos</a>
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
