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

    
    $mensaje = '';
    $error = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = trim($_POST['titulo']);
        $destino_nombre = trim($_POST['destino']);
        $ruta = trim($_POST['ruta']);
        $experiencia = trim($_POST['descripcion']);
        
        if (empty($titulo) || empty($destino_nombre) || empty($ruta) || empty($experiencia)) {
            $error = 'Todos los campos son obligatorios.';
        } else {
            $stmt_buscar_destino = $pdo->prepare("SELECT id_destino FROM destino WHERE nombre = ? LIMIT 1");
            $stmt_buscar_destino->execute([$destino_nombre]);
            $destino_existente = $stmt_buscar_destino->fetch(PDO::FETCH_ASSOC);
            
            if ($destino_existente) {
                $id_destino = $destino_existente['id_destino'];
            } else {
                $stmt_crear_destino = $pdo->prepare("INSERT INTO destino (nombre) VALUES (?)");
                $stmt_crear_destino->execute([$destino_nombre]);
                $id_destino = $pdo->lastInsertId();
            }
            
            $imagen_principal = NULL;
            if (isset($_FILES['miniatura']) && $_FILES['miniatura']['error'] === UPLOAD_ERR_OK) {
                $nombre_archivo = time() . '_' . basename($_FILES['miniatura']['name']);
                $ruta_destino = 'imagenes/' . $nombre_archivo;
                
                if (move_uploaded_file($_FILES['miniatura']['tmp_name'], $ruta_destino)) {
                    $imagen_principal = $ruta_destino;
                }
            }
            
            $stmt_cronica = $pdo->prepare("
                INSERT INTO cronica 
                (id_usuario, id_destino, titulo, experiencia, imagen_principal, ruta, fecha_publicacion, estado, num_recomendados) 
                VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 'Pendiente', 0)
            ");
            $stmt_cronica->execute([$id_usuario, $id_destino, $titulo, $experiencia, $imagen_principal, $ruta]);
            $id_cronica = $pdo->lastInsertId();
            
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
                foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                        $nombre_imagen = time() . '_' . $key . '_' . basename($_FILES['imagenes']['name'][$key]);
                        $ruta_imagen = 'imagenes/' . $nombre_imagen;
                        
                        if (move_uploaded_file($tmp_name, $ruta_imagen)) {
                            $stmt_imagen = $pdo->prepare("INSERT INTO imagen_carrusel (id_cronica, ruta_imagen) VALUES (?, ?)");
                            $stmt_imagen->execute([$id_cronica, $ruta_imagen]);
                        }
                    }
                }
            }
            
            $mensaje = '¡Crónica publicada correctamente! Será revisada por los administradores.';
            
            $_POST = array();
        }
    }
    
} catch (PDOException $e) {
    $error = "Error de conexión: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Publicar Crónica</title>
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
                <h1 class="encabezado-principal seccion-publicarcronica">Comparte tu experiencia</h1>
                <h2 class="oculto-para-accesibilidad">Comparte tu experiencia</h2>
                <p class="subtitulo-principal">Comparte tus momentos inolvidables para inspirar a otros viajeros</p>
            </header>

            <div class="caja-principal formulario-publicacion">
                <?php if ($mensaje): ?>
                    <div class="exito-publicacion">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-publicacion">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                    <label for="titulo">Título:</label>
                    <input type="text" id="titulo" name="titulo" value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>" required>

                    <label for="destino">Destino:</label>
                    <input type="text" id="destino" name="destino" value="<?php echo isset($_POST['destino']) ? htmlspecialchars($_POST['destino']) : ''; ?>" required>

                    <label for="ruta">Ruta:</label>
                    <textarea id="ruta" name="ruta" rows="8" placeholder="Roma:
- Coliseo Romano
- Basílica de San Pedro

Venecia:
- Plaza de San Marcos
- Gran Canal" required><?php echo isset($_POST['ruta']) ? htmlspecialchars($_POST['ruta']) : ''; ?></textarea>

                    <label for="descripcion">Comenta tu experiencia:</label>
                    <textarea id="descripcion" name="descripcion" rows="6" required><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>

                    <label for="miniatura">Miniatura de la publicación:</label>
                    <input type="file" id="miniatura" name="miniatura" accept="image/*">

                    <label for="imagenes">Álbum de fotos:</label>
                    <input type="file" id="imagenes" name="imagenes[]" multiple accept="image/*">

                    <button type="submit" class="boton-publicar">Publicar Crónica</button>
                </form>
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
                    <a href="user-explorar.php" class="enlace-menu explorar">Explorar</a>
                    <a href="user-misdestinos.php" class="enlace-menu mis-destinos">Mis Destinos</a>
                    <a href="user-recomendaciones.php" class="enlace-menu recomendaciones">Recomendaciones</a>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="enlace-menu activo publicar-cronica">Publicar Crónica</a>
                    <a href="userexplorador-perfil.php" class="enlace-menu perfil">Perfil</a>
                </div>
            </nav>
        </aside>
    </div>
</body>
</html>
