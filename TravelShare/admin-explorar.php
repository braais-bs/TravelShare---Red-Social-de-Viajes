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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_id']) && isset($_POST['id_cronica'])) {
        $id_usuario = $_SESSION['usuario_id'];
        $id_cronica = (int)$_POST['id_cronica'];
        
        $stmt_check = $pdo->prepare("SELECT id_guardada FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
        $stmt_check->execute([$id_usuario, $id_cronica]);
        $ya_guardada = $stmt_check->fetch();
        
        if ($ya_guardada) {
            $stmt_delete = $pdo->prepare("DELETE FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
            $stmt_delete->execute([$id_usuario, $id_cronica]);
        } else {
            $stmt_insert = $pdo->prepare("INSERT INTO cronica_guardada (id_usuario, id_cronica) VALUES (?, ?)");
            $stmt_insert->execute([$id_usuario, $id_cronica]);
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $cronicas = [];
    $busqueda_destino = '';
    if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
        $busqueda_destino = trim($_GET['buscar']);
        $stmt_busqueda = $pdo->prepare("
            SELECT c.*, d.nombre AS destino
            FROM cronica c
            JOIN destino d ON c.id_destino = d.id_destino
            WHERE c.estado = 'Publicada' 
            AND d.nombre LIKE :busqueda
            ORDER BY c.fecha_publicacion DESC
        ");
        $stmt_busqueda->execute([':busqueda' => "%$busqueda_destino%"]);
        $cronicas = $stmt_busqueda->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt_cronicas = $pdo->prepare("
            SELECT c.*, d.nombre AS destino
            FROM cronica c
            JOIN destino d ON c.id_destino = d.id_destino
            WHERE c.estado = 'Publicada'
            ORDER BY c.fecha_publicacion DESC
        ");
        $stmt_cronicas->execute();
        $cronicas = $stmt_cronicas->fetchAll(PDO::FETCH_ASSOC);
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
        <title><?php echo $busqueda_destino ? "Resultados para '$busqueda_destino'" : 'Explorar'; ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="estilo.css" />
        <style>
            .sin-resultados {
                text-align: center;
                color: var(--color-letra-gris);
                font-size: 1.1rem;
                margin: 2rem 0;
            }
        </style>
    </head>
    <body>
        <input type="checkbox" id="estado-menu" class="estado-menu" />
        <label for="estado-menu" class="boton-menu">☰</label>
        <div class="overlay"></div>

        <div class="estructura">
            <main>
                <header class="caja-encabezado-principal">
                    <h1 class="encabezado-principal seccion-explorar">
                        <?php echo $busqueda_destino ? "Resultados para '$busqueda_destino'" : 'Descubre el Mundo'; ?>
                    </h1>
                    <p class="subtitulo-principal">
                        <?php echo $busqueda_destino ? 'Crónicas encontradas en este destino' : 'Lo último que comparten nuestros exploradores'; ?>
                    </p>
                </header>

                <div class="caja-principal">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" class="contenedor-buscador">
                        <label for="buscador" class="oculto-para-accesibilidad">Buscar por destino:</label>
                        <input type="text" id="buscador" name="buscar" 
                               placeholder="Buscar por destino..." 
                               class="texto-buscador" 
                               value="<?php echo htmlspecialchars($busqueda_destino); ?>" />
                        <button type="submit" class="boton-buscador">Buscar</button>
                    </form>

                    <div class="cronicas">
                        <?php if (!empty($cronicas)): ?>
                            <?php foreach ($cronicas as $cronica): ?>
                                <?php                           
                                $ya_guardada = false;
                                if ($usuario) {
                                    $stmt_check = $pdo->prepare("SELECT 1 FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
                                    $stmt_check->execute([$usuario['id_usuario'], $cronica['id_cronica']]);
                                    $ya_guardada = $stmt_check->fetch() !== false;
                                }
                                ?>
                                <article class="cronica">
                                    <a href="admin-cronica.php?id=<?php echo $cronica['id_cronica']; ?>" class="enlace-leer-cronica">
                                        <img src="<?php echo htmlspecialchars($cronica['imagen_principal']); ?>"
                                             alt="Foto de <?php echo htmlspecialchars($cronica['titulo']); ?>" />
                                        <h2 class="titulo-cronica"><?php echo htmlspecialchars($cronica['titulo']); ?></h2>
                                    </a>
                                    <a href="admin-cronica.php?id=<?php echo $cronica['id_cronica']; ?>#comentario" class="boton-accion">Comentar</a>
                                    
                                    <?php if ($usuario): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id_cronica" value="<?php echo $cronica['id_cronica']; ?>">
                                            <button type="submit" class="boton-accion <?php echo $ya_guardada ? 'guardado-activo' : ''; ?>">
                                                <?php echo $ya_guardada ? 'Guardado' : 'Guardar'; ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="boton-accion" disabled>Inicia Sesión</button>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        <?php elseif ($busqueda_destino): ?>
                            <p class="sin-resultados">No se encontraron coincidencias.</p>
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
                    <h3 class="nombre-usuario">
                        @<?php echo htmlspecialchars(trim($usuario['nombre_usuario'])); ?>
                    </h3>
                <?php else: ?>
                    <img class="foto-perfil" src="imagenes/NoUser.png" alt="Imagen de perfil" />
                    <h3 class="nombre-usuario">@Usuario</h3>
                <?php endif; ?>

                <nav class="menu-principal">
                    <div class="grupo-menu">
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="enlace-menu activo explorar">Explorar</a>
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
