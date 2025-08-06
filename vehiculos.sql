-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-04-2025 a las 01:13:24
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
-- Base de datos: `vehiculos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `anio` year(4) NOT NULL,
  `color` varchar(30) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `tipo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id`, `marca`, `modelo`, `anio`, `color`, `precio`, `tipo`) VALUES
(1, 'Toyota', 'Corolla', '2020', 'blanco', 25000.00, 'Sedan'),
(2, 'Ford', 'Ranger', '2019', 'negro', 28000.00, 'pickup'),
(3, 'Chevrolet ', 'Onix', '2022', 'rojo', 21000.00, 'hatchback'),
(4, 'Renault', 'Duster', '2021', 'negro', 25000.00, 'SUV'),
(5, 'Volkswagen', 'Golf', '2018', 'azul', 20000.00, 'hatchback'),
(6, 'Peugeot', '208', '2020', 'blanco', 22000.00, 'hatchback'),
(7, 'Honda', 'Civic', '2022', 'gris plata', 27000.00, 'sedan'),
(8, 'Fiat', 'Strada', '2019', 'rojo', 18500.00, 'pickup'),
(9, 'Nissan', 'Versa', '2021', 'azul marino', 24000.00, 'sedan'),
(10, 'Jeep', 'Renegade', '2023', 'verde', 32000.00, 'SUV');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
