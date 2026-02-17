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

$id_usuario = $_SESSION['usuario_id'];
$mensaje = "";

try {
    $pdo = new PDO(
        "mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4",
        $usuario_bd,
        $password_bd,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare("
        SELECT nombre, apellido1, nombre_usuario, descripcion, ubicacion, paises_visitados, foto_perfil, rol
        FROM usuario
        WHERE id_usuario = ?
    ");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: inisesion.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {

        $nombre = trim($_POST['nombre-completo']);
        $apellido1 = trim($_POST['apellido1']);
        $nombre_usuario = trim($_POST['nombre-usuario']);
        $descripcion = trim($_POST['descripcion']);
        $ubicacion = trim($_POST['ubicacion']);
        $paises_visitados = (int)$_POST['paises-visitados'];

        if (empty($nombre) || empty($apellido1) || empty($nombre_usuario)) {
            $mensaje = "Nombre, apellido y nombre de usuario son obligatorios.";
        } else {

            $ruta_foto = $usuario['foto_perfil'];

            if (!empty($_FILES['fotoperfil']['name'])) {

                $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($_FILES['fotoperfil']['type'], $permitidos)) {
                    $mensaje = "Formato de imagen no permitido.";
                } else {

                    $archivo = uniqid() . "_" . basename($_FILES['fotoperfil']['name']);
                    $destino = "imagenes/" . $archivo;

                    if (move_uploaded_file($_FILES['fotoperfil']['tmp_name'], $destino)) {

                        if (
                            $usuario['foto_perfil'] && 
                            $usuario['foto_perfil'] !== 'imagenes/NoUser.png' && 
                            file_exists($usuario['foto_perfil'])
                        ) {
                            unlink($usuario['foto_perfil']);
                        }

                        $ruta_foto = $destino;
                    }
                }
            }

            if ($mensaje === "") {
                $stmt = $pdo->prepare("
                    UPDATE usuario
                    SET nombre = ?, apellido1 = ?, nombre_usuario = ?, 
                        descripcion = ?, ubicacion = ?, paises_visitados = ?, foto_perfil = ?
                    WHERE id_usuario = ?
                ");
                $stmt->execute([
                    $nombre,
                    $apellido1,
                    $nombre_usuario,
                    $descripcion,
                    $ubicacion,
                    $paises_visitados,
                    $ruta_foto,
                    $id_usuario
                ]);

                switch ($usuario['rol']) {
                    case 'administrador':
                        header('Location: admin-perfil.php');
                        break;
                    case 'explorador':
                        header('Location: userexplorador-perfil.php');
                        break;
                    default:
                        header('Location: usernormal-perfil.php');
                }
                exit;
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("SELECT id_cronica FROM cronica_recomendada WHERE id_usuario = ?");
            $stmt->execute([$id_usuario]);
            $cronicas_recomendadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($cronicas_recomendadas as $id_cronica) {
                $stmt = $pdo->prepare("
                    UPDATE cronica 
                    SET num_recomendados = GREATEST(num_recomendados - 1, 0)
                    WHERE id_cronica = ?
                ");
                $stmt->execute([$id_cronica]);
            }

            $pdo->prepare("DELETE FROM comentario WHERE id_usuario = ?")->execute([$id_usuario]);
            $pdo->prepare("DELETE FROM cronica_recomendada WHERE id_usuario = ?")->execute([$id_usuario]);
            $pdo->prepare("DELETE FROM cronica_guardada WHERE id_usuario = ?")->execute([$id_usuario]);
            
            $pdo->prepare("DELETE FROM cronica WHERE id_usuario = ?")->execute([$id_usuario]);

            if (
                $usuario['foto_perfil'] && 
                $usuario['foto_perfil'] !== 'imagenes/NoUser.png' && 
                file_exists($usuario['foto_perfil'])
            ) {
                unlink($usuario['foto_perfil']);
            }

            $pdo->prepare("DELETE FROM usuario WHERE id_usuario = ?")->execute([$id_usuario]);

            $pdo->commit();

            session_destroy();
            header('Location: inisesion.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error al eliminar la cuenta: " . $e->getMessage());
        }
    }

} catch (Exception $e) {
    die("Error en la operación: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Modificar Perfil</title>
<link rel="stylesheet" href="estilo.css" />
</head>
<body>

<div class="fondo-modperfil">
    <div class="contenedor-modperfil">
        <div class="modperfil-caja">
            <h1 class="titulo-modperfil">Modifica tu Perfil</h1>

            <?php if ($mensaje): ?>
                <p class="mensaje-error"><?= htmlspecialchars($mensaje) ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="formulario-modperfil">
                
                <label for="nombre-completo">Nombre:</label>
                <input type="text" id="nombre-completo" name="nombre-completo" 
                       value="<?= htmlspecialchars($usuario['nombre']) ?>" 
                       placeholder="Brais" required />

                <label for="apellido1">Apellido:</label>
                <input type="text" id="apellido1" name="apellido1" 
                       value="<?= htmlspecialchars($usuario['apellido1']) ?>" 
                       placeholder="Bértolo" required />

                <label for="nombre-usuario">Nombre de Usuario:</label>
                <input type="text" id="nombre-usuario" name="nombre-usuario" 
                       value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" 
                       placeholder="braais.bs" required />

                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" 
                          placeholder="Disfrutón ;)"><?= htmlspecialchars($usuario['descripcion']) ?></textarea>

                <label for="ubicacion">Ubicación:</label>
                <input type="text" id="ubicacion" name="ubicacion" 
                       value="<?= htmlspecialchars($usuario['ubicacion']) ?>" 
                       placeholder="Vigo" />

                <label for="paises-visitados">Países Visitados:</label>
                <input type="number" id="paises-visitados" name="paises-visitados" 
                       value="<?= htmlspecialchars($usuario['paises_visitados']) ?>" 
                       min="0" placeholder="4" />

                <label for="fotoperfil">Foto de Perfil</label>
                <input type="file" id="fotoperfil" name="fotoperfil" accept="image/*">

                <button type="submit" name="accion" value="guardar" 
                        class="botonmodificar botonmod-guardar">Guardar Cambios</button>
                
                <button type="submit" name="accion" value="eliminar" 
                        class="botonmodificar botonmod-eliminar"
                        onclick="return confirm('¿Seguro que deseas eliminar tu cuenta?');">Eliminar Cuenta</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
