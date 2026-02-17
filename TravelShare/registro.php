<?php
$servidor = "localhost";
$usuario_bd = "root";
$password_bd = "";
$nombre_bd = "portal_viajes";

$mensaje_error = "";

try {
    $pdo = new PDO(
        "mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4",
        $usuario_bd,
        $password_bd,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $nombre = trim($_POST['nombre']);
        $apellido1 = trim($_POST['apellido1']);
        $nombre_usuario = trim($_POST['usuario']);
        $correo = trim($_POST['email']);
        $ubicacion = trim($_POST['ubicacion']);
        $contrasena = $_POST['contrasena'];
        $confirmar = $_POST['confirmar'];
        $rol = $_POST['rol'];

        if ($contrasena !== $confirmar) {
            $mensaje_error = "Las contraseñas no coinciden.";
        } else {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE nombre_usuario = ?");
            $stmt->execute([$nombre_usuario]);
            if ($stmt->fetch()) {
                $mensaje_error = "El nombre de usuario ya existe.";
            } else {
                $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
                $stmt->execute([$correo]);
                if ($stmt->fetch()) {
                    $mensaje_error = "El correo ya está registrado.";
                } else {
                    $ruta_foto = 'imagenes/NoUser.png';

                    if (!empty($_FILES['fotoperfil']['name'])) {
                        $permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                        
                        if (in_array($_FILES['fotoperfil']['type'], $permitidos)) {
                            $archivo = uniqid() . "_" . basename($_FILES['fotoperfil']['name']);
                            $destino = "imagenes/" . $archivo;
                            
                            if (move_uploaded_file($_FILES['fotoperfil']['tmp_name'], $destino)) {
                                $ruta_foto = $destino;
                            }
                        }
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO usuario (
                            nombre_usuario, 
                            nombre, 
                            apellido1, 
                            correo, 
                            contrasena, 
                            rol, 
                            foto_perfil, 
                            descripcion, 
                            ubicacion, 
                            paises_visitados, 
                            num_publicaciones
                        ) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, '', ?, 0, 0)
                    ");

                    $stmt->execute([
                        $nombre_usuario,
                        $nombre,
                        $apellido1,
                        $correo,
                        $contrasena,
                        $rol,
                        $ruta_foto,
                        $ubicacion
                    ]);

                    header('Location: inisesion.php');
                    exit;
                }
            }
        }
    }

} catch (PDOException $e) {
    $mensaje_error = "Error en la base de datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registrarse</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilo.css" />
</head>
<body class="fondo-login">
    <div class="contenedor-login">
        <div class="login-caja">
            <h1 class="titulo-login">Crear Cuenta</h1>
            <p class="subtitulo-login">Únete a la comunidad de viajeros</p>

            <?php if ($mensaje_error): ?>
                <p class="mensaje-error" style="color: red; margin-bottom: 1rem;"><?= htmlspecialchars($mensaje_error) ?></p>
            <?php endif; ?>
            
            <form class="formulario-login" method="POST" enctype="multipart/form-data">

                <label for="nombre" class="oculto-para-accesibilidad">Nombre</label>
                <input id="nombre" type="text" name="nombre" placeholder="Nombre" required />

                <label for="apellido1" class="oculto-para-accesibilidad">Apellido</label>
                <input id="apellido1" type="text" name="apellido1" placeholder="Primer Apellido" required />
                
                <label for="nombreusuario" class="oculto-para-accesibilidad">Nombre de usuario</label>
                <input id="nombreusuario" type="text" name="usuario" placeholder="Nombre de usuario" required />

                <label for="email" class="oculto-para-accesibilidad">Correo electrónico</label>
                <input id="email" type="email" name="email" placeholder="Correo electrónico" required />

                <label for="ubicacion" class="oculto-para-accesibilidad">Ubicación</label>
                <input id="ubicacion" type="text" name="ubicacion" placeholder="Ubicación" required />

                <label for="fotoperfil" class="oculto-para-accesibilidad">Foto de perfil</label>
                <input id="fotoperfil" type="file" name="fotoperfil" accept="image/*" />

                <label for="contrasena" class="oculto-para-accesibilidad">Contraseña</label>
                <input id="contrasena" type="password" name="contrasena" placeholder="Contraseña" required />

                <label for="confirmar" class="oculto-para-accesibilidad">Confirmar contraseña</label>
                <input id="confirmar" type="password" name="confirmar" placeholder="Confirmar contraseña" required />
                
                <label for="rol" class="seleccion-rol">Selecciona tu tipo de usuario:</label>
                <select id="rol" name="rol" class="selector-rol" required>
                    <option value="">-- Selecciona --</option>
                    <option value="normal">Usuario Normal</option>
                    <option value="explorador">Explorador</option>
                </select>

                <button type="submit" class="boton-login">Registrarse</button>
            </form>

            <p class="registro-texto">¿Ya tienes cuenta? <a href="inisesion.php">Inicia sesión</a></p>
        </div>
    </div>
</body>
</html>
