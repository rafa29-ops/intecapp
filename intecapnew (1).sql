-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-09-2025 a las 21:11:18
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `intecapnew`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL,
  `fecha` date DEFAULT NULL,
  `id_usuario` varchar(50) DEFAULT NULL,
  `id_evento` varchar(50) DEFAULT NULL,
  `estatilla` varchar(100) DEFAULT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id`, `fecha`, `id_usuario`, `id_evento`, `estatilla`, `hora_entrada`, `hora_salida`, `estado`) VALUES
(1, '2025-07-10', '5', '6', NULL, '00:00:00', '00:00:00', 'Activo'),
(2, '2025-07-10', '5', '5', NULL, '18:37:00', '18:42:00', 'Activo'),
(3, '2025-07-10', '5', '5', NULL, '18:37:00', '18:42:00', 'Activo'),
(4, '2025-07-10', '5', '5', NULL, '18:37:00', '18:42:00', 'Activo'),
(5, '2025-07-10', '5', '5', NULL, '18:37:00', '18:42:00', 'Activo'),
(6, '2025-07-10', '5', '5', NULL, '18:37:00', '18:42:00', 'Activo'),
(7, '2025-07-10', '5', '5', NULL, '18:45:00', '21:48:00', 'Activo'),
(8, '2025-07-10', '5', '7', NULL, '19:46:00', '23:51:00', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL DEFAULT current_timestamp(),
  `Comentario` varchar(150) DEFAULT NULL,
  `id_mantenimiento` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id`, `fecha`, `Comentario`, `id_mantenimiento`) VALUES
(1, '2025-06-27', '', '1'),
(2, '2025-06-27', 'Prueba', '72'),
(7, '2025-06-27', 'comnetario id 72', '1'),
(9, '2025-06-27', 'asd 75', '75'),
(11, '2025-06-27', 'prueba 1', '1'),
(13, '2025-06-27', 'prueba 1 3', '1'),
(14, '2025-06-27', 'prueba 72 1', '72'),
(15, '2025-06-27', 'prueba 72 2', '72'),
(17, '2025-06-27', 'prueba 73 1', '73'),
(19, '2025-07-02', 'dfgdfg', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id_eventos` int(11) NOT NULL,
  `id_talleres` int(11) DEFAULT NULL,
  `programa` varchar(50) DEFAULT NULL,
  `nombre_evento` varchar(50) DEFAULT NULL,
  `f_inicio` date DEFAULT NULL,
  `f_fin` date DEFAULT NULL,
  `fecha_uso` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `id_instructor` int(11) DEFAULT NULL,
  `anio_evento` year(4) NOT NULL,
  `modalidad` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id_eventos`, `id_talleres`, `programa`, `nombre_evento`, `f_inicio`, `f_fin`, `fecha_uso`, `estado`, `id_instructor`, `anio_evento`, `modalidad`) VALUES
(5, 72, '001', 'Pasteles frios', '2025-04-01', '2025-04-01', NULL, 'Activo', 1, '2024', 'Presencial'),
(6, 71, '61651', 'Evento Mecánica', '2025-04-22', '2025-07-22', NULL, 'Activo', 3, '2024', 'Virtual'),
(7, 77, '159753', '2025', '2025-06-25', '2025-07-25', NULL, 'Activo', 7, '2025', 'Presencial'),
(8, 77, '61651', 'Evento', '2025-04-22', '2025-07-22', NULL, 'Inactivo', 3, '2025', 'Virtual');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructor`
--

CREATE TABLE `instructor` (
  `id` int(11) NOT NULL,
  `año` int(11) DEFAULT NULL,
  `nom_instructor` varchar(20) DEFAULT NULL,
  `id_talleres` varchar(50) DEFAULT NULL,
  `id_eventos` varchar(50) DEFAULT NULL,
  `fecha_uso` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instructor`
--

INSERT INTO `instructor` (`id`, `año`, `nom_instructor`, `id_talleres`, `id_eventos`, `fecha_uso`, `estado`, `usuario`) VALUES
(1, 2025, 'Kan HS', NULL, NULL, '2025-03-27', 'Activo', NULL),
(4, 2025, 'Kan Hernandez', NULL, NULL, NULL, 'Inactivo', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimiento`
--

CREATE TABLE `mantenimiento` (
  `id` int(11) NOT NULL,
  `id_taller` varchar(50) DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `f_reporte` date DEFAULT NULL,
  `f_realizado` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `id_encargado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mantenimiento`
--

INSERT INTO `mantenimiento` (`id`, `id_taller`, `hora_inicio`, `hora_fin`, `f_reporte`, `f_realizado`, `estado`, `descripcion`, `id_encargado`) VALUES
(1, '71', '00:00:00', '00:00:00', '2025-03-27', '2025-03-27', 'no realizado', 'fghfghfg', 5),
(72, '77', '14:14:00', '22:16:00', '2025-03-27', '2025-03-27', 'Realizada', 'Holaaa', 5),
(73, '77', '18:55:00', '19:56:00', '2025-06-25', '2025-08-26', 'Realizada', 'asdasfasd', 5),
(75, '77', '20:09:00', '22:11:00', '2025-06-25', '2025-07-25', 'no realizado', 'dfgdfg', 5),
(76, '72', '19:18:00', '20:19:00', '2025-07-10', '2025-08-10', 'no realizado', 'sdfsdfgsdg', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) DEFAULT NULL,
  `nombre` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `talleres`
--

CREATE TABLE `talleres` (
  `id` int(11) NOT NULL,
  `nombre_taller` varchar(100) NOT NULL,
  `participantes` int(11) DEFAULT NULL,
  `estatilla` varchar(100) DEFAULT NULL,
  `condicion` varchar(150) DEFAULT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `anio` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `talleres`
--

INSERT INTO `talleres` (`id`, `nombre_taller`, `participantes`, `estatilla`, `condicion`, `hora_entrada`, `hora_salida`, `estado`, `anio`) VALUES
(71, 'Cocina 2025', 50, NULL, '----', '07:00:00', '10:00:00', 'Inactivo', '2025'),
(72, 'Informática', 25, NULL, '------', '17:42:00', '22:47:00', 'Inactivo', '2025'),
(77, 'Informática...', 30, NULL, NULL, NULL, NULL, 'Activo', '2020');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) DEFAULT NULL,
  `direccion` varchar(70) DEFAULT NULL,
  `cargo` varchar(150) DEFAULT NULL,
  `nom_usuario` varchar(100) DEFAULT NULL,
  `contraseña` varchar(64) DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `direccion`, `cargo`, `nom_usuario`, `contraseña`, `id_rol`, `estado`) VALUES
(1, 'Kan', 'Santa Cruz del Quiche, Quiche', 'Admin', 'admin_Space', 'a1Bz20ydqelm8m1wql8c0d82130ba591e442e1ce7f99ad62a9', NULL, 'activo'),
(3, 'Kan', 'Santa Cruz del Quiche', 'Instructor', 'admin', 'a1Bz20ydqelm8m1wqle10adc3949ba59abbe56e057f20f883e', NULL, 'activo'),
(4, 'Kan', 'Quiche', 'Admin', 'kan', 'a1Bz20ydqelm8m1wqle10adc3949ba59abbe56e057f20f883e', NULL, 'activo'),
(5, 'Mantenimiento', 'Mantenimiento', 'Mantenimiento', 'Mantenimiento', 'a1Bz20ydqelm8m1wqle10adc3949ba59abbe56e057f20f883e', NULL, 'activo'),
(7, 'instructor', 'instructor', 'Instructor', 'instructor', 'a1Bz20ydqelm8m1wqle10adc3949ba59abbe56e057f20f883e', NULL, 'activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id_eventos`);

--
-- Indices de la tabla `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `talleres`
--
ALTER TABLE `talleres`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id_eventos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `instructor`
--
ALTER TABLE `instructor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `talleres`
--
ALTER TABLE `talleres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
