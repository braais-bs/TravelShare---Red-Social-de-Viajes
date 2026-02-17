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
    
    $stmt_usuario = $pdo->prepare("SELECT * FROM usuario WHERE id_usuario = ? LIMIT 1");
    $stmt_usuario->execute([$id_usuario]);
    $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        header('Location: inisesion.php');
        exit;
    }
    
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header('Location: admin-pendiente.php');
        exit;
    }
    
    $id_cronica = $_GET['id'];
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['aprobar'])) {
            // Cambiar estado a 'Publicada'
            $stmt_aprobar = $pdo->prepare("UPDATE cronica SET estado = 'Publicada' WHERE id_cronica = ?");
            $stmt_aprobar->execute([$id_cronica]);
            header('Location: admin-aprobado.php');
            exit;
        } elseif (isset($_POST['rechazar'])) {
            // Cambiar estado a 'Rechazada'
            $stmt_rechazar = $pdo->prepare("UPDATE cronica SET estado = 'Rechazada' WHERE id_cronica = ?");
            $stmt_rechazar->execute([$id_cronica]);
            header('Location: admin-rechazado.php');
            exit;
        }
    }
    
    
    $stmt_cronica = $pdo->prepare("
        SELECT c.*, d.nombre AS destino
        FROM cronica c
        JOIN destino d ON c.id_destino = d.id_destino
        WHERE c.id_cronica = ?
    ");
    $stmt_cronica->execute([$id_cronica]);
    $cronica = $stmt_cronica->fetch(PDO::FETCH_ASSOC);
    
    if (!$cronica) {
        header('Location: admin-pendiente.php');
        exit;
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
        <title>Administrar Crónica</title>
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
                    <p class="subtitulo-principal">Gestión y control de crónicas</p>
                </header>

                <div class="caja-principal cronica-detalle">
                    <img src="<?php echo htmlspecialchars($cronica['imagen_principal']); ?>" 
                         alt="Foto del viaje" class="imagen-cronica-miniatura">
                    <h2 class="titulo-cronica"><?php echo htmlspecialchars($cronica['titulo']); ?></h2>

                    <h3 class="apartado-cronica">Destino:</h3>
                    <p class="detalle-destino"><?php echo htmlspecialchars($cronica['destino']); ?></p>

                    <h3 class="apartado-cronica">Ruta:</h3>
                    <p class="detalle-ruta"><?php echo nl2br(htmlspecialchars($cronica['ruta'])); ?></p>

                    <h3 class="apartado-cronica">Experiencia:</h3>
                    <p class="detalle-experiencia"><?php echo nl2br(htmlspecialchars($cronica['experiencia'])); ?></p>

                    <div class="acciones-cronica">
                        <form method="POST">
                            <input type="hidden" name="aprobar" value="1">
                            <button type="submit" class="boton-accion boton-aprobar enlace-boton">Aprobar</button>
                        </form>
                        
                        <form method="POST">
                            <input type="hidden" name="rechazar" value="1">
                            <button type="submit" class="boton-accion boton-eliminar enlace-boton">Rechazar</button>
                        </form>
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
                        <a href="admin-pendiente.php" class="enlace-menu administrador">Administrador</a>
                    </div>
                </nav>
            </aside>
        </div>
    </body>
</html>
