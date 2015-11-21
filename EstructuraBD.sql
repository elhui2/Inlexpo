-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 20-11-2015 a las 08:41:21
-- Versión del servidor: 5.5.46
-- Versión de PHP: 5.3.10-1ubuntu3.21

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `inlexpo15`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `content`
--

CREATE TABLE IF NOT EXISTS `content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `lang` char(2) NOT NULL,
  `content_type` int(11) NOT NULL,
  `source` varchar(150) DEFAULT NULL,
  `content_text` varchar(9000) DEFAULT NULL,
  `content_int` int(11) DEFAULT NULL,
  `content_attachment` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=865 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `content_choice_options`
--

CREATE TABLE IF NOT EXISTS `content_choice_options` (
  `lang` char(2) NOT NULL,
  `content_type_id` int(11) NOT NULL,
  `content_choice_id` int(11) NOT NULL,
  `content_value` varchar(100) DEFAULT NULL,
  `content_value_abbr` varchar(50) DEFAULT NULL,
  `content_parent_value` int(11) NOT NULL DEFAULT '-1',
  `content_choice_visible` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `content_types`
--

CREATE TABLE IF NOT EXISTS `content_types` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` char(2) NOT NULL,
  `label` varchar(40) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `required` bit(1) DEFAULT NULL,
  `control` int(11) DEFAULT NULL,
  `max` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `crossref`
--

CREATE TABLE IF NOT EXISTS `crossref` (
  `src` int(11) NOT NULL,
  `tgt` int(11) NOT NULL,
  `cross_ref_type` int(11) NOT NULL,
  `icon` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `crossref_type`
--

CREATE TABLE IF NOT EXISTS `crossref_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dictionary`
--

CREATE TABLE IF NOT EXISTS `dictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `author` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entry`
--

CREATE TABLE IF NOT EXISTS `entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `d_id` int(11) NOT NULL,
  `lang` char(2) NOT NULL,
  `type` int(11) NOT NULL,
  `head` varchar(50) DEFAULT NULL,
  `parent` int(11) NOT NULL,
  `number` int(11) NOT NULL DEFAULT '-1',
  `owner` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8940 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entry_type`
--

CREATE TABLE IF NOT EXISTS `entry_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(20) DEFAULT NULL,
  `permitted_children` varchar(60) DEFAULT NULL,
  `permitted_content` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `import_temp`
--

CREATE TABLE IF NOT EXISTS `import_temp` (
  `id` int(11) NOT NULL,
  `text` varchar(250) NOT NULL,
  `size` int(11) NOT NULL,
  `style` varchar(4) NOT NULL,
  `type` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `id` char(2) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `login` varchar(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `level` int(11) NOT NULL,
  `password` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
