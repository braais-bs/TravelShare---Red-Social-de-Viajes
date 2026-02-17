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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
        $id_cronica_guardar = (int)$_POST['id_cronica'];
        $stmt_check = $pdo->prepare("SELECT 1 FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
        $stmt_check->execute([$usuario['id_usuario'], $id_cronica_guardar]);
        $ya_guardada = $stmt_check->fetch() !== false;
        
        if ($ya_guardada) {
            $stmt_delete = $pdo->prepare("DELETE FROM cronica_guardada WHERE id_usuario = ? AND id_cronica = ?");
            $stmt_delete->execute([$usuario['id_usuario'], $id_cronica_guardar]);
        } else {
            $stmt_insert = $pdo->prepare("INSERT INTO cronica_guardada (id_usuario, id_cronica) VALUES (?, ?)");
            $stmt_insert->execute([$usuario['id_usuario'], $id_cronica_guardar]);
        }
        
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    $cronicas = [];
    $busqueda = '';
    if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
        $busqueda = trim($_GET['buscar']);
        $stmt_busqueda = $pdo->prepare("
            SELECT c.*, d.nombre AS destino
            FROM cronica c
            JOIN destino d ON c.id_destino = d.id_destino
            WHERE c.estado = 'Publicada' 
            AND (c.titulo LIKE :busqueda OR d.nombre LIKE :busqueda)
            ORDER BY c.fecha_publicacion DESC
        ");
        $stmt_busqueda->execute([':busqueda' => "%$busqueda%"]);
        $cronicas = $stmt_busqueda->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt_cronicas = $pdo->prepare("
            SELECT c.*, d.nombre AS destino
            FROM cronica c
            JOIN destino d ON c.id_destino = d.id_destino
            WHERE c.estado = 'Publicada'
            ORDER BY c.fecha_publicacion DESC
            LIMIT 20
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
        <title><?php echo $busqueda ? "Resultados para '$busqueda'" : 'Explorar'; ?></title>
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
                    <p class="subtitulo-principal"><?php echo $busqueda ? 'Resultados de búsqueda' : 'Lo último que comparten nuestros exploradores'; ?></p>
                </header>
                <div class="caja-principal">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" class="contenedor-buscador">
                        <label for="buscador" class="oculto-para-accesibilidad">Buscar crónicas:</label>
                        <input type="text" id="buscador" name="buscar" 
                               value="<?php echo htmlspecialchars($busqueda); ?>"
                               placeholder="Buscar por título o destino..." class="texto-buscador" />
                        <button type="submit" class="boton-buscador">Buscar</button>
                    </form>

                    <div class="cronicas">
                        <?php if(!empty($cronicas)): ?>
                            <?php foreach($cronicas as $cronica): ?>
                                <?php
                                     $link_cronica = ($usuario['rol'] === 'administrador' || $usuario['rol'] === 'explorador') 
                                                     ? "userexplorador-cronica.php" 
                                                     : "usernormal-cronica.php";
                                     $link_cronica .= "?id=" . $cronica['id_cronica'];
                                    
                                     $stmt_g = $pdo->prepare("SELECT 1 FROM cronica_guardada WHERE id_usuario=? AND id_cronica=?");
                                     $stmt_g->execute([$usuario['id_usuario'], $cronica['id_cronica']]);
                                     $is_guardada = $stmt_g->fetchColumn() !== false;
                                ?>
                                <article class="cronica">
                                    <a href="<?php echo $link_cronica; ?>" class="enlace-leer-cronica">
                                        <img src="<?php echo htmlspecialchars($cronica['imagen_principal']); ?>" alt="Foto de <?php echo htmlspecialchars($cronica['titulo']); ?>" />
                                        <h2 class="titulo-cronica"><?php echo htmlspecialchars($cronica['titulo']); ?></h2>
                                    </a>
                                    <a href="<?php echo $link_cronica; ?>#comentarios-seccion" class="boton-accion" style="text-decoration:none; text-align:center;">Comentar</a>
                                    
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_cronica" value="<?php echo $cronica['id_cronica']; ?>">
                                        <input type="hidden" name="accion" value="guardar">
                                        <button type="submit" class="boton-accion">
                                            <?php echo $is_guardada ? 'Guardado' : 'Guardar'; ?>
                                        </button>
                                    </form>
                                </article>     
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="text-align: center; color: #666; margin-top: 2rem;">No se encontraron crónicas.</p>
                        <?php endif; ?>                   
                    </div>
                </div>
            </main>
            <aside>
                <img class="foto-perfil" src="<?php echo htmlspecialchars($usuario['foto_perfil'] ?: 'imagenes/NoUser.png'); ?>" alt="Imagen de perfil" />
                <h3 class="nombre-usuario">@<?php echo htmlspecialchars($usuario['nombre_usuario']); ?></h3>
                <nav class="menu-principal">
                    <div class="grupo-menu">
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="enlace-menu activo explorar">Explorar</a>
                        <a href="user-misdestinos.php" class="enlace-menu mis-destinos">Mis Destinos</a>
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
