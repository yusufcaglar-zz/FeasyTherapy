-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 30 Mar 2022, 08:54:01
-- Sunucu sürümü: 10.4.8-MariaDB
-- PHP Sürümü: 7.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `feasytherapy`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `exercise`
--

CREATE TABLE `exercise` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `starting_date` timestamp NULL DEFAULT current_timestamp(),
  `ending_date` timestamp NULL DEFAULT current_timestamp(),
  `exercise_mode` varchar(50) NOT NULL,
  `motion_type` varchar(50) NOT NULL,
  `target_force` varchar(100) DEFAULT NULL,
  `target_position` varchar(10) DEFAULT NULL,
  `target_repeat` int(3) DEFAULT NULL,
  `hand` tinyint(1) NOT NULL COMMENT '0 for left, 1 for right',
  `patient_force` varchar(10000) DEFAULT NULL,
  `patient_position` varchar(10000) DEFAULT NULL,
  `patient_repeat` varchar(10000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Tablo döküm verisi `exercise`
--

INSERT INTO `exercise` (`id`, `session_id`, `starting_date`, `ending_date`, `exercise_mode`, `motion_type`, `target_force`, `target_position`, `target_repeat`, `hand`, `patient_force`, `patient_position`, `patient_repeat`) VALUES
(1, 1, '2022-01-06 16:30:00', '2022-01-06 17:00:00', 'Isometric', 'Flexion', '20', '[-30, 45]', 150, 1, '[18, 15, 20, 23, 24, 20, 20, 21, 22, 15, 17, 18, 25, 24, 23 ,22]', '[-20, 45, -25, 35, -26, 37, -34, 52]', '147');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ongoing`
--

CREATE TABLE `ongoing` (
  `id` int(11) UNSIGNED NOT NULL,
  `updated` timestamp NULL DEFAULT current_timestamp(),
  `country` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_info` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phpsessid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `patient`
--

CREATE TABLE `patient` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `gender` tinyint(1) NOT NULL COMMENT '0 for male, 1 for female',
  `photo` varchar(100) DEFAULT 'user.png',
  `birthday` date NOT NULL,
  `register_date` timestamp NULL DEFAULT current_timestamp(),
  `discharge_date` datetime DEFAULT NULL,
  `complaint` varchar(255) NOT NULL,
  `physiotherapist_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Patient List';

--
-- Tablo döküm verisi `patient`
--

INSERT INTO `patient` (`id`, `name`, `surname`, `gender`, `photo`, `birthday`, `register_date`, `discharge_date`, `complaint`, `physiotherapist_id`, `active`) VALUES
(1, 'Jane', 'Doe', 1, 'user.png', '1975-05-25', '2022-01-05 06:28:42', NULL, 'Joint range of motion is lower than expected. Also a surgery history presents.', 1, 1),
(2, 'John', 'Doe', 0, 'user.png', '1972-02-03', '2022-01-05 06:29:42', NULL, 'Pain on left wrist when lifting weights. Has a car crash accident story.', 1, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `physiotherapist`
--

CREATE TABLE `physiotherapist` (
  `id` int(11) NOT NULL,
  `id_number` bigint(11) UNSIGNED NOT NULL,
  `encrypted_password` varchar(150) NOT NULL,
  `salt` varchar(25) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `gender` tinyint(1) NOT NULL DEFAULT 0,
  `birthday` date NOT NULL,
  `register_date` date NOT NULL,
  `discharge_date` date DEFAULT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Physiotherapist List';

--
-- Tablo döküm verisi `physiotherapist`
--

INSERT INTO `physiotherapist` (`id`, `id_number`, `encrypted_password`, `salt`, `name`, `surname`, `gender`, `birthday`, `register_date`, `discharge_date`, `active`) VALUES
(1, 11111111111, '$2y$10$B6E6qiS4uw2FPExXLGkenOTr1VG/tBuh6sOWVN9xgap/k3i92ME0G', '55c27629a2', 'John', 'Smith', 0, '1990-01-04', '2021-01-15', NULL, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `session`
--

CREATE TABLE `session` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `physiotherapist_id` int(11) NOT NULL,
  `starting_date` timestamp NULL DEFAULT current_timestamp(),
  `ending_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Tablo döküm verisi `session`
--

INSERT INTO `session` (`id`, `patient_id`, `physiotherapist_id`, `starting_date`, `ending_date`) VALUES
(1, 2, 1, '2022-01-06 16:30:00', '2022-01-06 17:00:00');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `statistics`
--

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `exercise`
--
ALTER TABLE `exercise`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session-id` (`session_id`);

--
-- Tablo için indeksler `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`id`),
  ADD KEY `physiotherapist-id` (`physiotherapist_id`);

--
-- Tablo için indeksler `physiotherapist`
--
ALTER TABLE `physiotherapist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_number` (`id_number`);

--
-- Tablo için indeksler `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `physiotherapist-id` (`physiotherapist_id`),
  ADD KEY `patient-id` (`patient_id`);

--
-- Tablo için indeksler `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `exercise`
--
ALTER TABLE `exercise`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `patient`
--
ALTER TABLE `patient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `physiotherapist`
--
ALTER TABLE `physiotherapist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `session`
--
ALTER TABLE `session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `statistics`
--
ALTER TABLE `statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `patient_ibfk_1` FOREIGN KEY (`physiotherapist_id`) REFERENCES `physiotherapist` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
