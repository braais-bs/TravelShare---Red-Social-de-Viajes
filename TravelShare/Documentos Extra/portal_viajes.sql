-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Xerado en: 11 de Xan de 2026 ás 21:40
-- Versión do servidor: 10.4.32-MariaDB
-- Versión do PHP: 8.2.12

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS `portal_viajes` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Seleccionar la base de datos
USE `portal_viajes`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `portal_viajes`
--

CREATE TABLE `comentario` (
  `id_comentario` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `id_cronica` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `comentario`
--

INSERT INTO `comentario` (`id_comentario`, `contenido`, `fecha`, `id_usuario`, `id_cronica`) VALUES
(1, '¡Qué aventura increíble por el Nilo!', '2025-12-27 05:17:32', 1, 1),
(2, 'Espectaculares fotos, gracias por compartir', '2025-12-25 06:38:27', 3, 1),
(14, 'Que ganas de ir!!', '2025-12-30 23:14:07', 1, 5),
(16, 'Unas vistas muy bonitas', '2026-01-07 00:08:28', 1, 3),
(19, 'A mi me toca en una semana, que ganas de ver Asuán', '2026-01-11 21:23:20', 5, 2),
(20, 'Me lo apunto como próximo destino ;)', '2026-01-11 21:28:05', 5, 1);

-- --------------------------------------------------------

--
-- Estrutura da táboa `cronica`
--

CREATE TABLE `cronica` (
  `id_cronica` int(11) NOT NULL,
  `titulo` varchar(300) NOT NULL,
  `ruta` text NOT NULL,
  `experiencia` longtext NOT NULL,
  `fecha_publicacion` datetime NOT NULL DEFAULT current_timestamp(),
  `imagen_principal` varchar(500) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `estado` enum('Publicada','Pendiente','Rechazada') NOT NULL DEFAULT 'Pendiente',
  `num_recomendados` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `cronica`
--

INSERT INTO `cronica` (`id_cronica`, `titulo`, `ruta`, `experiencia`, `fecha_publicacion`, `imagen_principal`, `id_usuario`, `id_destino`, `estado`, `num_recomendados`) VALUES
(1, 'Rio y Aventura', 'Luxor:\n- Fui a ver el templo de Karnak, una impresionante muestra de la antigua arquitectura egipcia.\n- Visité el valle de los Reyes, donde descansan muchos faraones, entre ellos Tutankamón.\n\nAsuán:\n- Paseo en barca por el río Nilo admirando el atardecer, con vistas a los templos de Philae y los jardines flotantes.', 'Navegar por el río fue un desafío emocionante. Los paisajes eran impresionantes, y cada curva traía una nueva vista espectacular. La conexión con el lugar y su historia fue total.', '2025-12-30 14:30:28', 'imagenes/viaje1.jpg', 1, 1, 'Publicada', 1),
(2, 'Templos Milenarios', 'Luxor:\n- Fui a ver el templo de Karnak, una impresionante muestra de la antigua arquitectura egipcia.\n- Visité el valle de los Reyes, donde descansan muchos faraones, entre ellos Tutankamón.\n\nAsuán:\n- Paseo en barca por el río Nilo admirando el atardecer, con vistas a los templos de Philae y los jardines flotantes.', 'Navegar por el río fue un desafío emocionante. Los paisajes eran impresionantes, y cada curva traía una nueva vista espectacular. La conexión con el lugar y su historia fue total.', '2025-12-31 21:34:17', 'imagenes/viaje2.jpg', 3, 2, 'Publicada', 2),
(3, 'Amanecer en Santorini', 'Oia:\n- Paseé por sus calles blancas y azules, famosas por sus casas cueva y vistas al mar Egeo.\n- Vi la puesta de sol desde el castillo veneciano, un momento mágico e inolvidable.\n\nFira:\n- Visité el Museo de Prehistoria de Thera para conocer la historia de la isla.\n- Caminé hasta el antiguo puerto disfrutando del paisaje volcánico y los barcos anclados.', 'Santorini es un lugar con una increíble belleza natural. La serenidad del mar junto con sus paisajes volcánicos hicieron que cada instante fuera único.', '2025-12-31 14:55:43', 'imagenes/viaje3.webp', 1, 2, 'Publicada', 1),
(4, 'Carretera Infinita', 'Ruta por carreteras secundarias con vistas espectaculares. Paradas en miradores naturales y pueblos con encanto.', 'La libertad de viajar sin prisas por carreteras infinitas es incomparable. Cada curva revela paisajes únicos.', '2025-12-29 07:20:47', 'imagenes/viaje4.jpg', 3, 4, 'Publicada', 0),
(5, 'Semana en NY', 'Manhattan, Central Park, Times Square, Brooklyn Bridge. Recorrido completo por la Gran Manzana.', 'La energía de Nueva York es adictiva. Nunca duerme y siempre sorprende.', '2026-01-01 16:19:27', 'imagenes/viaje5.jpg', 1, 3, 'Publicada', 3),
(21, 'Visita a la Costa Croata', 'Dubrovnik:\r\n - Murallas de la ciudad antigua\r\n - Fuerte Lovrijenac\r\n - Calle Stradun\r\n\r\nSplit:\r\n - Palacio de Diocleciano\r\n - Paseo marítimo Riva\r\n - Parque Nacional Krka\r\nHvar:\r\n - Fortaleza española\r\n - Catedral de San Esteban\r\n - Playas de Pakleni', 'Croacia nos recibió con un mar de un azul intenso y pueblos costeros llenos de encanto. Durante la semana recorrimos ciudades históricas, paseamos por calles empedradas, subimos a miradores con vistas al Adriático y disfrutamos de calas tranquilas donde el agua era tan clara que se veían las piedras del fondo. Entre paseos en barco, atardeceres naranjas y comida local deliciosa, el viaje se sintió como una mezcla perfecta de relax, cultura y naturaleza.', '2026-01-10 18:44:14', 'imagenes/1768067054_dubrovnik_croacia.jpg', 4, 12, 'Pendiente', 0),
(22, 'Castillos y Cerveza', 'Praga:\r\n - Puente de Carlos\r\n - Plaza de la Ciudad Vieja\r\n - Castillo de Praga\r\nExcursión:\r\n - Pueblo de Český Krumlov', 'La República Checa nos sorprendió con una mezcla muy agradable de historia y ambiente joven. Paseamos por Praga cruzando el Puente de Carlos al amanecer, cuando la ciudad todavía estaba medio dormida y solo se escuchaba el sonido del río. Por la tarde, las luces del casco antiguo y el reloj astronómico creaban una atmósfera casi de cuento. La excursión a Český Krumlov, con su castillo en lo alto y sus casas de colores alrededor del río, fue el complemento perfecto para desconectar y disfrutar de un ritmo más tranquilo.', '2026-01-08 18:53:43', 'imagenes/1768067623_que_ver_en_republica_checa_Brno.webp', 4, 13, 'Rechazada', 0),
(23, 'Fin de Semana Entre Gaudí y Tapas', 'Día 1:\r\n - La Sagrada Familia\r\n - Park Güell\r\n - Barrio Gótico\r\nDía 2:\r\n - Casa Batlló\r\n - Paseo de Gracia\r\n - Mercado de la Boquería\r\n - Las Ramblas\r\nDía 3:\r\n - Playa de la Barceloneta\r\n - Barrio del Raval', 'Barcelona nos sorprendió desde el primer momento. Al entrar en la Sagrada Familia, la luz que atravesaba los vitrales creaba un espectáculo de colores imposible de describir con palabras. Las columnas parecían árboles gigantes elevándose hacia el cielo, y cada rincón escondía detalles que Gaudí dejó plasmados con una imaginación desbordante.', '2026-01-11 21:26:41', 'imagenes/1768163201_shutterstock_580489630-1-1024x685.jpg', 5, 4, 'Pendiente', 0);

-- --------------------------------------------------------

--
-- Estrutura da táboa `cronica_guardada`
--

CREATE TABLE `cronica_guardada` (
  `id_guardada` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_cronica` int(11) NOT NULL,
  `fecha_guardado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `cronica_guardada`
--

INSERT INTO `cronica_guardada` (`id_guardada`, `id_usuario`, `id_cronica`, `fecha_guardado`) VALUES
(37, 1, 3, '2026-01-09 19:54:47'),
(44, 3, 1, '2026-01-11 19:09:50'),
(51, 3, 4, '2026-01-11 20:18:40'),
(54, 1, 4, '2026-01-11 20:26:45'),
(57, 2, 2, '2026-01-11 20:47:00'),
(58, 2, 3, '2026-01-11 21:15:12'),
(59, 1, 5, '2026-01-11 21:15:30'),
(60, 3, 5, '2026-01-11 21:16:03'),
(61, 5, 5, '2026-01-11 21:23:26'),
(62, 5, 1, '2026-01-11 21:23:27');

-- --------------------------------------------------------

--
-- Estrutura da táboa `cronica_recomendada`
--

CREATE TABLE `cronica_recomendada` (
  `id_recomendacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_cronica` int(11) NOT NULL,
  `fecha_recomendacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `cronica_recomendada`
--

INSERT INTO `cronica_recomendada` (`id_recomendacion`, `id_usuario`, `id_cronica`, `fecha_recomendacion`) VALUES
(12, 1, 2, '2026-01-02 13:17:07'),
(14, 1, 1, '2026-01-03 13:41:22'),
(15, 1, 5, '2026-01-06 22:56:12'),
(21, 3, 5, '2026-01-11 19:27:05'),
(22, 3, 2, '2026-01-11 19:27:16'),
(23, 5, 5, '2026-01-11 20:23:34'),
(24, 5, 3, '2026-01-11 20:23:44');

-- --------------------------------------------------------

--
-- Estrutura da táboa `destino`
--

CREATE TABLE `destino` (
  `id_destino` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `destino`
--

INSERT INTO `destino` (`id_destino`, `nombre`) VALUES
(12, 'Croacia'),
(1, 'Egipto'),
(4, 'España'),
(2, 'Grecia'),
(3, 'Nueva York, USA'),
(11, 'pp'),
(13, 'República Checa');

-- --------------------------------------------------------

--
-- Estrutura da táboa `imagen_carrusel`
--

CREATE TABLE `imagen_carrusel` (
  `id_imagen` int(11) NOT NULL,
  `id_cronica` int(11) NOT NULL,
  `ruta_imagen` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `imagen_carrusel`
--

INSERT INTO `imagen_carrusel` (`id_imagen`, `id_cronica`, `ruta_imagen`) VALUES
(7, 5, 'imagenes/newyork1.jpg'),
(8, 5, 'imagenes/newyork2.jpg'),
(9, 5, 'imagenes/newyork3.webp'),
(10, 5, 'imagenes/newyork4.webp'),
(11, 5, 'imagenes/newyork5.jpg'),
(12, 3, 'imagenes/santorini1.avif'),
(13, 3, 'imagenes/santorini2.jpg'),
(14, 3, 'imagenes/santorini3.jpg'),
(15, 3, 'imagenes/santorini4.jpg'),
(16, 2, 'imagenes/grecia1.jpg'),
(17, 2, 'imagenes/grecia2.jpg'),
(18, 2, 'imagenes/grecia3.jpg'),
(23, 21, 'imagenes/1768067054_0_zadar_croatia_sea_organ.jpg'),
(24, 21, 'imagenes/1768067054_1_Split-Croacia.jpg'),
(25, 21, 'imagenes/1768067054_2_peristyle_split_1.jpg'),
(26, 21, 'imagenes/1768067054_3_dubrovnik_croacia.jpg'),
(27, 21, 'imagenes/1768067054_4_18.-Dubrovnik-Croacia-320x480.jpg'),
(28, 22, 'imagenes/1768067623_0_RCheca-1.jpg'),
(29, 22, 'imagenes/1768067623_1_que_ver_en_republica_checa_Brno.webp'),
(30, 22, 'imagenes/1768067623_2_Portada-990x742.jpg'),
(31, 22, 'imagenes/1768067623_3_peristyle_split_1.jpg'),
(32, 22, 'imagenes/1768067623_4_karlovy-vary.jpg'),
(33, 22, 'imagenes/1768067623_5_Castillo-mas-bonitos-de-Republica-Checa.jpg'),
(34, 23, 'imagenes/1768163201_0_teleferico-barcelona_58.webp'),
(35, 23, 'imagenes/1768163201_1_shutterstock_580489630-1-1024x685.jpg'),
(36, 23, 'imagenes/1768163201_2_province-of-barcelona.jpg');

-- --------------------------------------------------------

--
-- Estrutura da táboa `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido1` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('administrador','explorador','normal') NOT NULL DEFAULT 'normal',
  `foto_perfil` varchar(500) DEFAULT 'imagenes/NoUser.png',
  `descripcion` text DEFAULT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `paises_visitados` int(11) DEFAULT 0,
  `num_publicaciones` int(11) DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A extraer os datos da táboa `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre_usuario`, `nombre`, `apellido1`, `correo`, `contrasena`, `rol`, `foto_perfil`, `descripcion`, `ubicacion`, `paises_visitados`, `num_publicaciones`, `fecha_registro`) VALUES
(1, 'braaisbs', 'Brais', 'Bertolo', 'admin@gmail.com', '1234', 'administrador', 'imagenes/fotoperfil-brais.jpeg', 'Disfrutón ;)', 'Tomiño', 6, 3, '2025-12-27 15:28:48'),
(2, 'alguien123', 'Usuario', 'Normal', 'normal@gmail.com', '1234', 'normal', 'imagenes/perfilnormal.jpg', 'Un usuario de lo mas normal...', 'Zamora', 1, 0, '2025-12-27 15:28:48'),
(3, 'omar.raz', 'Omar', 'Razzouki', 'explorador@gmail.com', '1234', 'explorador', 'imagenes/fotoperfilexplorador.jpg', '', 'A Coruña', 3, 2, '2025-12-27 15:28:48'),
(4, 'alberf991', 'Alberto', 'Fernandez', 'alberfndz@gmail.com', 'aabb', 'normal', 'imagenes/images.png', '', 'Valencia', 0, 0, '2026-01-08 18:46:41'),
(5, 'mArcos_14', 'Marcos', 'Martín', 'mmartin@gmail.com', 'aabb', 'explorador', 'imagenes/marcos.png', NULL, 'Valencia', 2, 0, '2026-01-11 20:22:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comentario`
--
ALTER TABLE `comentario`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `fk_comentario_usuario` (`id_usuario`),
  ADD KEY `fk_comentario_cronica` (`id_cronica`);

--
-- Indexes for table `cronica`
--
ALTER TABLE `cronica`
  ADD PRIMARY KEY (`id_cronica`),
  ADD KEY `fk_cronica_usuario` (`id_usuario`),
  ADD KEY `fk_cronica_destino` (`id_destino`);

--
-- Indexes for table `cronica_guardada`
--
ALTER TABLE `cronica_guardada`
  ADD PRIMARY KEY (`id_guardada`),
  ADD UNIQUE KEY `unica_guardada` (`id_usuario`,`id_cronica`),
  ADD KEY `id_cronica` (`id_cronica`);

--
-- Indexes for table `cronica_recomendada`
--
ALTER TABLE `cronica_recomendada`
  ADD PRIMARY KEY (`id_recomendacion`),
  ADD UNIQUE KEY `unica_recomendacion` (`id_usuario`,`id_cronica`),
  ADD KEY `id_cronica` (`id_cronica`);

--
-- Indexes for table `destino`
--
ALTER TABLE `destino`
  ADD PRIMARY KEY (`id_destino`),
  ADD UNIQUE KEY `uk_nombre` (`nombre`);

--
-- Indexes for table `imagen_carrusel`
--
ALTER TABLE `imagen_carrusel`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `fk_imagen_carrusel_cronica` (`id_cronica`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uk_correo` (`correo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comentario`
--
ALTER TABLE `comentario`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `cronica`
--
ALTER TABLE `cronica`
  MODIFY `id_cronica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `cronica_guardada`
--
ALTER TABLE `cronica_guardada`
  MODIFY `id_guardada` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `cronica_recomendada`
--
ALTER TABLE `cronica_recomendada`
  MODIFY `id_recomendacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `destino`
--
ALTER TABLE `destino`
  MODIFY `id_destino` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `imagen_carrusel`
--
ALTER TABLE `imagen_carrusel`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricións para os envorcados das táboas
--

--
-- Restricións para a táboa `comentario`
--
ALTER TABLE `comentario`
  ADD CONSTRAINT `fk_comentario_cronica` FOREIGN KEY (`id_cronica`) REFERENCES `cronica` (`id_cronica`),
  ADD CONSTRAINT `fk_comentario_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Restricións para a táboa `cronica`
--
ALTER TABLE `cronica`
  ADD CONSTRAINT `fk_cronica_destino` FOREIGN KEY (`id_destino`) REFERENCES `destino` (`id_destino`),
  ADD CONSTRAINT `fk_cronica_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Restricións para a táboa `cronica_guardada`
--
ALTER TABLE `cronica_guardada`
  ADD CONSTRAINT `cronica_guardada_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `cronica_guardada_ibfk_2` FOREIGN KEY (`id_cronica`) REFERENCES `cronica` (`id_cronica`) ON DELETE CASCADE;

--
-- Restricións para a táboa `cronica_recomendada`
--
ALTER TABLE `cronica_recomendada`
  ADD CONSTRAINT `cronica_recomendada_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `cronica_recomendada_ibfk_2` FOREIGN KEY (`id_cronica`) REFERENCES `cronica` (`id_cronica`);

--
-- Restricións para a táboa `imagen_carrusel`
--
ALTER TABLE `imagen_carrusel`
  ADD CONSTRAINT `fk_imagen_carrusel_cronica` FOREIGN KEY (`id_cronica`) REFERENCES `cronica` (`id_cronica`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
