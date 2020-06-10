-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-06-2020 a las 23:07:57
-- Versión del servidor: 10.4.11-MariaDB
-- Versión de PHP: 7.4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_sillondelentretenimiento`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentario`
--

CREATE TABLE `comentario` (
  `IdComentario` int(11) NOT NULL,
  `IdNoticia` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `Texto` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `comentario`
--

INSERT INTO `comentario` (`IdComentario`, `IdNoticia`, `IdUsuario`, `Fecha`, `Texto`) VALUES
(24, 26, 73, '2020-06-09 15:43:10', 'Ya quiero ver esta serie! No puedo esperar a verla .**'),
(26, 30, 12, '2020-06-09 16:19:26', 'Ya era hora de que saliera.*'),
(27, 24, 72, '2020-06-09 16:20:07', 'Vaya ya era hora.'),
(28, 24, 72, '2020-06-09 16:20:17', 'No puedo creerlo.'),
(32, 27, 12, '2020-06-09 19:55:45', 'Se ve bien, muy bien.*'),
(41, 25, 72, '2020-06-09 20:23:55', 'Comentar.'),
(42, 25, 73, '2020-06-09 20:24:19', 'Otro comentario.'),
(44, 34, 73, '2020-06-10 13:32:05', 'No lo puedo creer.'),
(45, 30, 73, '2020-06-10 13:32:48', 'No puedo esperar.'),
(46, 29, 73, '2020-06-10 13:33:04', 'Me gusta la imagen.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticia`
--

CREATE TABLE `noticia` (
  `IdNoticia` int(11) NOT NULL,
  `Seccion` enum('Videojuegos','Series','Peliculas') NOT NULL,
  `IdModerador` int(11) NOT NULL,
  `Titulo` varchar(50) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `Texto` varchar(1000) NOT NULL,
  `ImagePath` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `noticia`
--

INSERT INTO `noticia` (`IdNoticia`, `Seccion`, `IdModerador`, `Titulo`, `Fecha`, `Texto`, `ImagePath`) VALUES
(24, 'Peliculas', 12, 'Spiderman: Dentro del spiderverso 2', '2020-06-09 15:33:30', 'Hasta este punto, el universo Spidey ha visto algunos reveses desafortunados en su agenda. No solo se retrasaron los lanzamientos de Morbius y Venom: Let There Be Carnage, sino que también se retrasó Spider-Man 3 y Spider-Man: Into the Spider-Verse 2 de Tom Holland. Las actualizaciones sobre este último han sido principalmente escasas, pero hoy marca un gran hito, ya que la secuela ha dado un emocionante paso adelante.', 'http://localhost:80/SillonDelEntretenimiento/API/Imagenes/php3010.jpeg'),
(25, 'Videojuegos', 12, 'Cyberpunk anuncia nueva fecha de lanzamiento', '2020-06-09 15:35:30', 'La fecha oficial de lanzamiento de Cyberpunk 2077 es el 17 de septiembre de 2020. Eso significa que no queda mucho tiempo para esperar hasta que podamos explorar el sórdido vientre de Night City. Ya hemos pasado la fecha de lanzamiento original del 16 de abril, pero CD Projekt Red tiene la plena intención de cumplir con esta fecha de lanzamiento en septiembre.', 'http://localhost:80/SillonDelEntretenimiento/API/Imagenes/php4C1.jpeg'),
(26, 'Series', 12, 'La rueda del tiempo inicia filmación', '2020-06-09 15:36:56', 'Si bien el codirector de TV de Amazon, Vernon Sanders, no pudo dar una fecha exacta de cuándo finalmente veremos la adaptación de la serie de la serie de libros de fantasía de Robert Jordan, The Wheel of Time, confirmó hoy en TCA que “estamos en camino con producción y nos encanta lo que hemos visto hasta ahora \".', 'http://localhost:80/SillonDelEntretenimiento/API/Imagenes/php5445.jpeg'),
(27, 'Peliculas', 72, 'Studio Ghibli Reveal CG Movie Aya And The Witch', '2020-06-09 15:59:08', 'Está basado en el libro infantil Earwig And The Witch de Diana Wynne Jones (quien, fanática de las preguntas y respuestas, estaba detrás del tomo que Ghibli adaptó al Howl\'s Moving Castle). La historia sigue a Earwig, una niña huérfana que vive en St. Morwald\'s Home for Children. Es adoptada por una mujer llamada Bella Yaga, que resulta ser una bruja terrible y lleva a Earwig a vivir en su hogar de baratijas sobrenaturales. Con la ayuda de un gato que habla, la niña debe usar su ingenio para sobrevivir. Sí, parece material de Ghibli.', 'http://localhost:80/SillonDelEntretenimiento/API/Imagenes/phpA9C9.jpeg'),
(29, 'Series', 72, 'Nueva serie de TV para el Mundodisco', '2020-06-09 16:15:21', 'A medida que BBC Studios termina de trabajar en su nueva serie de televisión The Watch, inspirada en los cuentos de Terry Pratchett sobre Ankh-Morpork City Watch de sus libros de Discworld, la compañía de producción que el autor fundó unos años antes de que falleciera tristemente en 2015 ha obtenido un nuevo acuerdo con Motive Pictures y Endeavor Content para una lista propuesta de adaptaciones de Discworld TV.', 'http://localhost:80/SillonDelEntretenimiento/API/Imagenes/php1C54.jpeg'),
(30, 'Videojuegos', 12, 'EA implementa el crossplay en Need for Speed', '2020-06-09 16:19:12', 'Una nueva actualización en junio introducira el crossplay para PS4, XBOX ONE, y PC.', 'http://localhost:80/SillonDelEntretenimiento/API/Imagenes/php7BC.jpeg'),
(31, 'Peliculas', 12, 'Space JAM 2 ya tiene fecha de lanzamiento.', '2020-06-09 18:08:32', 'Noticia', 'http://localhost:80/SillonDelEntretenimiento/API/Imagenes/phpC2A9.jpeg'),
(34, 'Videojuegos', 12, 'AMD ha embarcado mas de 50 mil millones de GPUs', '2020-06-10 12:25:22', 'Desde el 2013, AMD ha embarcado mas GPUs que Intel o Nvidia.', 'http://localhost:80/SillonDelEntretenimiento/API/Imagenes/phpF8F4.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesion`
--

CREATE TABLE `sesion` (
  `IdUsuario` int(11) NOT NULL,
  `TokenAcceso` varchar(80) NOT NULL,
  `CaducidadTokenAcceso` datetime NOT NULL,
  `TokenActualizacion` varchar(80) NOT NULL,
  `CaducidadTokenActualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sesion`
--

INSERT INTO `sesion` (`IdUsuario`, `TokenAcceso`, `CaducidadTokenAcceso`, `TokenActualizacion`, `CaducidadTokenActualizacion`) VALUES
(73, 'ZjA5ZDljYTdjNmNjNjE1ZjZmYjJlOWY0ODE3MTBjZTdkNmQ1ZDRiMTNiMGYyYzJhMTU5MTgxNDA5Ng==', '2020-06-10 13:54:56', 'YzdhOGViMzRhOGQxZTI3YmY0NDhmNzhjNmI0ZTEwMjdlOWJiZTNiZDU3YmM2OTBkMTU5MTgxMzkxNQ==', '2020-06-25 13:31:55'),
(74, 'MTYzYjc0YWRjZTk3YTQwMTA4NmViNjIxNWM0ZjNhOGNhZjZhZGQxNWUxZjdjZmYwMTU5MTgxNDE2MA==', '2020-06-10 13:56:00', 'ZmIzZDAxNDM5NmMwY2E3ZDRiODEyMjM0NDM3NTJjM2M3Mzk2NjIxMDcxYTg1NWRiMTU5MTgxNDEzOA==', '2020-06-25 13:35:38'),
(72, 'Y2M3MDUxM2YyNjA1NDhmNmU2ZTM1MWIxNDFiZDA5ZjkxNjg1YzcyMGZhNjU1NzM3MTU5MTgyMTMyOQ==', '2020-06-10 15:55:29', 'YTQyMTg1OGYzZmJmYzdiMDMyZjFhZDJiMzYxMWFhODA2OGZhMDhjZWNlNzM4ZWJiMTU5MTgyMTI3Mg==', '2020-06-25 15:34:32'),
(12, 'NTEyZWExMTcwYTA1ZTQyMTM3YzgzMDEwNmEyNjg0Mjg1Y2Q0YTBkYjdlYjI1MjhmMTU5MTgyMzE4NA==', '2020-06-10 16:26:24', 'N2JlNjBjZGQwYTRmODQ0YjdhMmY5ZTgwMTMwNzI1ZDI2Nzk2ZjJjYWU5ZDNmOGJmMTU5MTgyMzE2NQ==', '2020-06-25 16:06:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `IdUsuario` int(11) NOT NULL,
  `NombreUsuario` varchar(50) NOT NULL,
  `Contrasenia` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `FechaRegistro` datetime NOT NULL DEFAULT current_timestamp(),
  `Rol` enum('Administrador','Moderador','Usuario') NOT NULL DEFAULT 'Usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`IdUsuario`, `NombreUsuario`, `Contrasenia`, `Email`, `FechaRegistro`, `Rol`) VALUES
(12, 'Rammas', '$2y$10$ouDJoLo6mksziwlJRI9M.e2jsnk6MfpfoQivNKbHSgVdh5iyryUcy', 'ADMIN@SILLONDELENTRETENIMIENTO.COM', '2020-06-02 18:33:38', 'Administrador'),
(72, 'Luis', '$2y$10$mFuVt5DLJPF4r9laCYQ5C..0VbJzSHzy6cXL4buMLAIcYFy9Danh2', 'LUIS@USUARIO.COM', '2020-06-09 15:40:05', 'Moderador'),
(73, 'usuario1', '$2y$10$4xNI0HQ2e9U2bC176G.fJe.iVa0TUroo8/FIo1OcNrfSJOOkRad1y', 'USUARIO1@HOTMAIL.COM', '2020-06-09 15:42:23', 'Usuario'),
(74, 'usuario2', '$2y$10$mIpWTotRuXiwn9uiIagCS.Hs5AM94F9EbZ4vMeWjiSsX7K0t6XCDS', 'USUARIO2@GMAIL.COM', '2020-06-10 13:35:38', 'Usuario');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comentario`
--
ALTER TABLE `comentario`
  ADD PRIMARY KEY (`IdComentario`),
  ADD KEY `FK_comentario_noticia` (`IdNoticia`),
  ADD KEY `FK_comentario_usuario` (`IdUsuario`);

--
-- Indices de la tabla `noticia`
--
ALTER TABLE `noticia`
  ADD PRIMARY KEY (`IdNoticia`),
  ADD KEY `FK_noticia_usuario` (`IdModerador`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`IdUsuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comentario`
--
ALTER TABLE `comentario`
  MODIFY `IdComentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `noticia`
--
ALTER TABLE `noticia`
  MODIFY `IdNoticia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comentario`
--
ALTER TABLE `comentario`
  ADD CONSTRAINT `FK_comentario_noticia` FOREIGN KEY (`IdNoticia`) REFERENCES `noticia` (`IdNoticia`),
  ADD CONSTRAINT `FK_comentario_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `noticia`
--
ALTER TABLE `noticia`
  ADD CONSTRAINT `FK_noticia_usuario` FOREIGN KEY (`IdModerador`) REFERENCES `usuario` (`IdUsuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
