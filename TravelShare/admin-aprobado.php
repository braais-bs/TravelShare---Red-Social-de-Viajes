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
    
    // Cargar datos del usuario
    $stmt_usuario = $pdo->prepare("SELECT * FROM usuario WHERE id_usuario = ? LIMIT 1");
    $stmt_usuario->execute([$id_usuario]);
    $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        header('Location: inisesion.php');
        exit;
    }
    
    $stmt_pendientes = $pdo->query("SELECT COUNT(*) as total FROM cronica WHERE estado = 'Pendiente'");
    $num_pendientes = $stmt_pendientes->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt_aprobadas = $pdo->query("
        SELECT COUNT(*) as total 
        FROM cronica 
        WHERE estado = 'Publicada' 
        AND fecha_publicacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $num_aprobadas = $stmt_aprobadas->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt_rechazadas = $pdo->query("
        SELECT COUNT(*) as total 
        FROM cronica 
        WHERE estado = 'Rechazada' 
        AND fecha_publicacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $num_rechazadas = $stmt_rechazadas->fetch(PDO::FETCH_ASSOC)['total'];
        
    $stmt_cronicas = $pdo->query("
        SELECT c.id_cronica, c.titulo, c.imagen_principal, c.fecha_publicacion, d.nombre AS destino
        FROM cronica c
        JOIN destino d ON c.id_destino = d.id_destino
        WHERE c.estado = 'Publicada'
        AND c.fecha_publicacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY c.fecha_publicacion DESC
    ");
    $cronicas_aprobadas = $stmt_cronicas->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Administrador Aprobados</title>
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
                    <h1 class="encabezado-principal seccion-administrador">Panel de Administración</h1>
                    <p class="subtitulo-principal">Gestiona y modera las experiencias de la comunidad</p>
                </header>

                <div class="panel-resumen">
                    <a href="admin-pendiente.php" class="resumen-caja pendiente">
                        <div class="resumen-titulo">
                            <span>Pendientes</span>
                            <span class="resumen-icono"></span>
                        </div>
                        <div class="resumen-numero"><?php echo $num_pendientes; ?></div>
                    </a>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="resumen-caja aprobada">
                        <div class="resumen-titulo">
                            <span>Aprobadas</span>
                            <span class="resumen-icono"></span>
                        </div>
                        <div class="resumen-numero"><?php echo $num_aprobadas; ?></div>
                    </a>
                    <a href="admin-rechazado.php" class="resumen-caja rechazada">
                        <div class="resumen-titulo">
                            <span>Rechazadas</span>
                            <span class="resumen-icono"></span>
                        </div>
                        <div class="resumen-numero"><?php echo $num_rechazadas; ?></div>
                    </a>
                </div>

                <div class="texto-admin-cronicas">
                    <h2>Crónicas Aprobadas</h2>
                </div>

                <div class="caja-principal">
                    <div class="cronicas-alargadas">
                        <?php if (!empty($cronicas_aprobadas)): ?>
                            <?php foreach ($cronicas_aprobadas as $cronica): ?>
                                <article class="cronica-alargada">
                                    <img src="<?php echo htmlspecialchars($cronica['imagen_principal']); ?>" 
                                         alt="Foto <?php echo htmlspecialchars($cronica['destino']); ?>" />
                                    <div class="detalles">
                                        <h2 class="titulo-cronica-alargada"><?php echo htmlspecialchars($cronica['titulo']); ?></h2>
                                        <div class="destino"><?php echo htmlspecialchars($cronica['destino']); ?></div>
                                        <div class="fecha">Publicado el <?php echo date('d/m/Y', strtotime($cronica['fecha_publicacion'])); ?></div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="sin-experiencias">No hay crónicas aprobadas en los últimos 30 días</p>
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
                        <a href="admin-misdestinos.php" class="enlace-menu mis-destinos">Mis Destinos</a>
                        <a href="admin-recomendaciones.php" class="enlace-menu recomendaciones">Recomendaciones</a>
                        <a href="admin-publicarcronica.php" class="enlace-menu publicar-cronica">Publicar Crónica</a>
                        <a href="admin-perfil.php" class="enlace-menu perfil">Perfil</a>
                        <a href="admin-pendiente.php" class="enlace-menu activo administrador">Administrador</a>
                    </div>
                </nav>
            </aside>
        </div>
    </body>
</html>
