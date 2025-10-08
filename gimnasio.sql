-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 09-10-2025 a las 01:08:35
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gimnasio`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` bigint(20) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `momento` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id`, `dni`, `momento`) VALUES
(7, '12312312', '2025-10-08 19:57:11'),
(2, '12345678', '2025-10-05 18:28:19'),
(1, '12345678', '2025-10-06 18:28:19'),
(4, '12345678', '2025-10-08 15:57:30'),
(6, '12345678', '2025-10-08 19:54:36'),
(3, '20123456', '2025-10-04 18:28:19'),
(5, '20123456', '2025-10-08 19:40:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `dni` varchar(10) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `telefono` varchar(40) DEFAULT NULL,
  `membresia_vence` date NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`dni`, `nombre`, `email`, `telefono`, `membresia_vence`, `creado_en`) VALUES
('12312312', 'Cosme', 'cosmefulano@gmail.com', '111111111', '2025-10-16', '2025-10-08 22:56:47'),
('12345678', 'Ana Pérez', 'ana@example.com', '223-555-1000', '2025-11-07', '2025-10-06 21:27:37'),
('20123456', 'Luis Gómez', 'luis@example.com', '223-555-2000', '2025-10-05', '2025-10-06 21:27:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicios`
--

CREATE TABLE `ejercicios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `musculo` varchar(80) DEFAULT NULL,
  `media_url` varchar(500) DEFAULT NULL,
  `media_tipo` enum('img','video') NOT NULL DEFAULT 'img',
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ejercicios`
--

INSERT INTO `ejercicios` (`id`, `nombre`, `musculo`, `media_url`, `media_tipo`, `descripcion`, `creado_en`) VALUES
(1, 'Press banca', 'pecho', NULL, 'img', 'Pecho con barra en banco plano', '2025-10-06 21:28:19'),
(2, 'Dominadas', 'espalda', NULL, 'img', 'Tracción vertical con peso corporal', '2025-10-06 21:28:19'),
(3, 'Sentadillas', 'piernas', NULL, 'img', 'Sentadilla con barra', '2025-10-06 21:28:19'),
(4, 'Press militar', 'hombros', NULL, 'img', 'Press de hombros de pie', '2025-10-06 21:28:19'),
(5, 'Press banca', 'pecho', NULL, 'img', NULL, '2025-10-08 18:40:38'),
(6, 'Dominadas', 'espalda', NULL, 'img', NULL, '2025-10-08 18:40:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

CREATE TABLE `profesores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`id`, `nombre`, `email`, `creado_en`) VALUES
(1, 'Juan Martínez', 'juan@ejemplo.com', '2025-10-06 21:28:19'),
(2, 'Carla Ruiz', 'carla@ejemplo.com', '2025-10-06 21:28:19'),
(3, 'Profe Juan', 'juan@ejemplo.com', '2025-10-08 18:40:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progresos`
--

CREATE TABLE `progresos` (
  `id` bigint(20) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `rutina_id` int(11) DEFAULT NULL,
  `ejercicio_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `series` int(11) NOT NULL,
  `repeticiones` int(11) NOT NULL,
  `peso` decimal(6,2) DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `progresos`
--

INSERT INTO `progresos` (`id`, `dni`, `rutina_id`, `ejercicio_id`, `fecha`, `series`, `repeticiones`, `peso`, `notas`) VALUES
(1, '12345678', 1, 1, '2025-10-06', 3, 10, 30.00, 'Primer día'),
(2, '12345678', 1, 2, '2025-10-06', 3, 8, NULL, 'Ajustar técnica');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutinas`
--

CREATE TABLE `rutinas` (
  `id` int(11) NOT NULL,
  `alumno_dni` varchar(10) NOT NULL,
  `profesor_id` int(11) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `notas` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rutinas`
--

INSERT INTO `rutinas` (`id`, `alumno_dni`, `profesor_id`, `nombre`, `notas`, `creado_en`) VALUES
(1, '12345678', 1, 'Full Body A', NULL, '2025-10-06 21:28:19'),
(2, '12312312', 1, 'Tren superior', NULL, '2025-10-08 23:06:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutina_detalle`
--

CREATE TABLE `rutina_detalle` (
  `id` int(11) NOT NULL,
  `rutina_id` int(11) NOT NULL,
  `ejercicio_id` int(11) NOT NULL,
  `series` int(11) NOT NULL DEFAULT 3,
  `repeticiones` int(11) NOT NULL DEFAULT 10,
  `peso_objetivo` decimal(6,2) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rutina_detalle`
--

INSERT INTO `rutina_detalle` (`id`, `rutina_id`, `ejercicio_id`, `series`, `repeticiones`, `peso_objetivo`, `orden`) VALUES
(4, 1, 2, 4, 8, NULL, 1),
(5, 2, 2, 4, 8, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_asistencias_ultimas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_asistencias_ultimas` (
`id` bigint(20)
,`dni` varchar(10)
,`momento` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_clientes_estado`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_clientes_estado` (
`dni` varchar(10)
,`nombre` varchar(120)
,`membresia_vence` date
,`dias_restantes` int(7)
,`activa` int(1)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_asistencias_ultimas`
--
DROP TABLE IF EXISTS `vw_asistencias_ultimas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_asistencias_ultimas`  AS SELECT `a`.`id` AS `id`, `a`.`dni` AS `dni`, `a`.`momento` AS `momento` FROM `asistencias` AS `a` WHERE `a`.`momento` = (select max(`a2`.`momento`) from `asistencias` `a2` where `a2`.`dni` = `a`.`dni`) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_clientes_estado`
--
DROP TABLE IF EXISTS `vw_clientes_estado`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_clientes_estado`  AS SELECT `c`.`dni` AS `dni`, `c`.`nombre` AS `nombre`, `c`.`membresia_vence` AS `membresia_vence`, to_days(`c`.`membresia_vence`) - to_days(curdate()) AS `dias_restantes`, to_days(`c`.`membresia_vence`) - to_days(curdate()) >= 0 AS `activa` FROM `clientes` AS `c` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asist_dni_momento` (`dni`,`momento`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`dni`);

--
-- Indices de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `progresos`
--
ALTER TABLE `progresos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prog_rutina` (`rutina_id`),
  ADD KEY `idx_prog_dni_fecha` (`dni`,`fecha`),
  ADD KEY `idx_prog_ejercicio_fecha` (`ejercicio_id`,`fecha`);

--
-- Indices de la tabla `rutinas`
--
ALTER TABLE `rutinas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rutina_cliente` (`alumno_dni`),
  ADD KEY `fk_rutina_profesor` (`profesor_id`);

--
-- Indices de la tabla `rutina_detalle`
--
ALTER TABLE `rutina_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detalle_ejercicio` (`ejercicio_id`),
  ADD KEY `idx_detalle_rutina_orden` (`rutina_id`,`orden`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `profesores`
--
ALTER TABLE `profesores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `progresos`
--
ALTER TABLE `progresos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `rutinas`
--
ALTER TABLE `rutinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `rutina_detalle`
--
ALTER TABLE `rutina_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `fk_asistencia_cliente` FOREIGN KEY (`dni`) REFERENCES `clientes` (`dni`) ON DELETE CASCADE;

--
-- Filtros para la tabla `progresos`
--
ALTER TABLE `progresos`
  ADD CONSTRAINT `fk_prog_cliente` FOREIGN KEY (`dni`) REFERENCES `clientes` (`dni`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prog_ejercicio` FOREIGN KEY (`ejercicio_id`) REFERENCES `ejercicios` (`id`),
  ADD CONSTRAINT `fk_prog_rutina` FOREIGN KEY (`rutina_id`) REFERENCES `rutinas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `rutinas`
--
ALTER TABLE `rutinas`
  ADD CONSTRAINT `fk_rutina_cliente` FOREIGN KEY (`alumno_dni`) REFERENCES `clientes` (`dni`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rutina_profesor` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `rutina_detalle`
--
ALTER TABLE `rutina_detalle`
  ADD CONSTRAINT `fk_detalle_ejercicio` FOREIGN KEY (`ejercicio_id`) REFERENCES `ejercicios` (`id`),
  ADD CONSTRAINT `fk_detalle_rutina` FOREIGN KEY (`rutina_id`) REFERENCES `rutinas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
