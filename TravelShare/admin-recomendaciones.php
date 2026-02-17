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
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
        $id_cronica_guardar = (int)$_POST['id_cronica'];
        $stmt_check = $pdo->prepare("SELECT 1 FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
        $stmt_check->execute([$id_usuario, $id_cronica_guardar]);
        $ya_guardada = $stmt_check->fetch() !== false;
        
        if ($ya_guardada) {
            $stmt_delete = $pdo->prepare("DELETE FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
            $stmt_delete->execute([$id_usuario, $id_cronica_guardar]);
        } else {
            $stmt_insert = $pdo->prepare("INSERT INTO cronica_guardada (id_usuario, id_cronica) VALUES (?, ?)");
            $stmt_insert->execute([$id_usuario, $id_cronica_guardar]);
        }
        
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    $stmt_usuario = $pdo->prepare("SELECT * FROM usuario WHERE id_usuario = ? LIMIT 1");
    $stmt_usuario->execute([$id_usuario]);
    $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
    
    $stmt_cronicas = $pdo->prepare("
        SELECT c.*, d.nombre AS destino
        FROM cronica c
        JOIN destino d ON c.id_destino = d.id_destino
        WHERE c.estado = 'Publicada'
        ORDER BY c.num_recomendados DESC, c.fecha_publicacion DESC
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
        <title>Recomendaciones</title>
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
                    <h1 class="encabezado-principal seccion-recomendaciones">Recomendaciones</h1>
                    <p class="subtitulo-principal">Inspiración exclusiva de la mano de otros exploradores</p>
                </header>
                <div class="caja-principal">
                    <div class="cronicas">
                        <?php if (!empty($cronicas)): ?>
                            <?php foreach ($cronicas as $cronica): ?>
                                <?php
                                $stmt_check = $pdo->prepare("SELECT 1 FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
                                $stmt_check->execute([$id_usuario, $cronica['id_cronica']]);
                                $ya_guardada = $stmt_check->fetch() !== false;
                                ?>
                                <article class="cronica">
                                    <a href="admin-cronica.php?id=<?php echo $cronica['id_cronica']; ?>" class="enlace-leer-cronica">
                                        <img src="<?php echo htmlspecialchars($cronica['imagen_principal']); ?>" 
                                             alt="Foto de <?php echo htmlspecialchars($cronica['titulo']); ?>" />
                                        <h2 class="titulo-cronica">
                                            <?php echo htmlspecialchars($cronica['titulo']); ?>
                                            <br>
                                            <span style="font-size: 0.8rem; color: #888;"> <?php echo $cronica['num_recomendados']; ?> &#x2B50;</span>
                                        </h2>
                                    </a>
                                    <a href="admin-cronica.php?id=<?php echo $cronica['id_cronica']; ?>#comentarios-seccion" class="boton-accion" style="text-decoration:none; text-align:center;">Comentar</a>
                                    
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_cronica" value="<?php echo $cronica['id_cronica']; ?>">
                                        <input type="hidden" name="accion" value="guardar">
                                        <button type="submit" class="boton-accion">
                                            <?php echo $ya_guardada ? 'Guardado' : 'Guardar'; ?>
                                        </button>
                                    </form>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="sin-resultados">No hay crónicas publicadas todavía.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
            
            <aside>
                <?php if (!empty($usuario)): ?>
                    <img class="foto-perfil"
                         src="<?php echo htmlspecialchars($usuario['foto_perfil'] ?: 'imagenes/NoUser.png'); ?>"
                         alt="Imagen de perfil" />
                    <h3 class="nombre-usuario">@<?php echo htmlspecialchars(trim($usuario['nombre_usuario'])); ?></h3>
                <?php else: ?>
                    <img class="foto-perfil" src="imagenes/NoUser.png" alt="Imagen de perfil" />
                    <h3 class="nombre-usuario">@Usuario</h3>
                <?php endif; ?>

                <nav class="menu-principal">
                    <div class="grupo-menu">
                        <a href="admin-explorar.php" class="enlace-menu explorar">Explorar</a>
                        <a href="admin-misdestinos.php" class="enlace-menu mis-destinos">Mis Destinos</a>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="enlace-menu activo recomendaciones">Recomendaciones</a>
                        <a href="admin-publicarcronica.php" class="enlace-menu publicar-cronica">Publicar Crónica</a>
                        <a href="admin-perfil.php" class="enlace-menu perfil">Perfil</a>
                        <a href="admin-pendiente.php" class="enlace-menu administrador">Administrador</a>
                    </div>
                </nav>
            </aside>
        </div>
    </body>
</html>
