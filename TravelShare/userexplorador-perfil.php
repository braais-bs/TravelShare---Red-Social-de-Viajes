<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: inisesion.php');
    exit;
}

$servidor = "localhost";
$usuario_bd = "root";
$password_bd = "";
$nombre_bd = "portal_viajes";

try {
    $pdo = new PDO(
        "mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4",
        $usuario_bd,
        $password_bd,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $id_usuario = $_SESSION['usuario_id'];

    $stmt = $pdo->prepare("
        SELECT nombre, apellido1, nombre_usuario, ubicacion, descripcion, 
               paises_visitados, foto_perfil, rol
        FROM usuario
        WHERE id_usuario = ?
    ");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: inisesion.php');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as num_experiencias
        FROM cronica
        WHERE id_usuario = ? AND estado = 'Publicada'
    ");
    $stmt->execute([$id_usuario]);
    $num_experiencias = $stmt->fetch(PDO::FETCH_ASSOC)['num_experiencias'];

    $stmt = $pdo->prepare("
        SELECT c.id_cronica, c.titulo, c.imagen_principal, c.fecha_publicacion, d.nombre AS nombre_destino
        FROM cronica c
        JOIN destino d ON c.id_destino = d.id_destino
        WHERE c.id_usuario = ? AND c.estado = 'Publicada'
        ORDER BY c.fecha_publicacion DESC
    ");
    $stmt->execute([$id_usuario]);
    $cronicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Perfil</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="estilo.css" />
    </head>
    <body>
        <input type="checkbox" id="estado-menu" class="estado-menu" />
        <label for="estado-menu" class="boton-menu">☰</label>
        <div class="overlay"></div>

        <div class="estructura">
            
            <main>
                <h1 class="oculto-para-accesibilidad">Perfil</h1>
                <div class="caja-principal">
                    <div class="banner-perfil">
                        <img class="imagen-banner" src="imagenes/banner.jpg" alt="Banner de perfil">
                    </div>
                    
                    <div class="info-usuario">
                        <a href="modificar-perfil.php" class="boton-editar-perfil">Editar perfil</a>
                        <div class="foto-usuario">
                            <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
                        </div>
                        <div class="datos-usuario">
                            <div class="nombre-usuario">
                                <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido1']) ?>
                            </div>
                            <div class="datos-adicionales">
                                <span class="ubicacion-usuario"><?= htmlspecialchars($usuario['ubicacion']) ?></span>
                                <span class="etiqueta-rol"><?= ucfirst($usuario['rol']) ?></span>                        
                            </div>
                            <?php if (!empty($usuario['descripcion'])): ?>
                                <div class="descripcion-usuario">
                                    <?= htmlspecialchars($usuario['descripcion']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="estadisticas-usuario">
                                <div>
                                    <span class="dato-numero"><?= $num_experiencias ?></span>
                                    <div class="texto-estadistica">Experiencias</div>
                                </div>
                                <div>
                                    <span class="dato-numero"><?= $usuario['paises_visitados'] ?></span>
                                    <div class="texto-estadistica">Países visitados</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="caja-principal">
                    <div class="cronicas-alargadas">
                        <?php if (!empty($cronicas)): ?>
                            <?php foreach ($cronicas as $cronica): ?>
                                <div class="contenedor-cronica">
                                    <a href="userexplorador-cronica.php?id=<?php echo $cronica['id_cronica']; ?>" class="enlace-leer-cronica">
                                        <article class="cronica-alargada">
                                            <img src="<?php echo htmlspecialchars($cronica['imagen_principal']); ?>" 
                                                alt="Foto <?php echo htmlspecialchars($cronica['nombre_destino']); ?>" />
                                            <div class="detalles">
                                                <h2 class="titulo-cronica-alargada"><?php echo htmlspecialchars($cronica['titulo']); ?></h2>
                                                <div class="destino"><?php echo htmlspecialchars($cronica['nombre_destino']); ?></div>
                                                <div class="fecha">
                                                    Publicado el <?php echo date('d/m/Y', strtotime($cronica['fecha_publicacion'])); ?>
                                                </div>
                                            </div>
                                        </article>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="sin-experiencias">Aún no has publicado ninguna experiencia</p>
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
                        <a href="user-misdestinos.php" class="enlace-menu mis-destinos">Mis Destinos</a>
                        <a href="user-recomendaciones.php" class="enlace-menu recomendaciones">Recomendaciones</a>
                        <a href="userexplorador-publicarcronica.php" class="enlace-menu publicar-cronica">Publicar Crónica</a>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="enlace-menu activo perfil">Perfil</a>
                    </div>
                    <a href="cerrar-sesion.php" class="boton-cerrar-sesion">Cerrar Sesión</a>
                </nav>
            </aside>
        </div>
    </body>
</html>
