-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 06 août 2025 à 12:31
-- Version du serveur : 8.0.42-0ubuntu0.24.04.2
-- Version de PHP : 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `elh`
--

-- --------------------------------------------------------

--
-- Structure de la table `carte`
--

CREATE TABLE `carte` (
  `id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `afiliation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `death_date` datetime DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `onmyname` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_prefix` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salat_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `carte`
--

INSERT INTO `carte` (`id`, `created_by_id`, `afiliation`, `firstname`, `lastname`, `death_date`, `content`, `type`, `location_name`, `onmyname`, `phone`, `phone_prefix`, `salat_id`) VALUES
(3, 2, 'bom', 'Ffxfxf', 'Death', '2024-02-27 00:00:00', 'Gggfff', 'death', NULL, 'myname', NULL, NULL, NULL),
(5, 1, 'bro', 'dfff', 'ssdf', '2024-04-21 11:16:49', 'Dffff', 'remercie', NULL, 'myname', NULL, NULL, NULL),
(6, 1, 'bro', 'uih', 'tre', '2024-05-09 11:20:10', 'Dffffgg', 'death', 'gezboo', 'myname', NULL, NULL, NULL),
(7, 1, 'bro', '', '', '2024-05-09 18:55:00', '', 'pardon', '', 'myname', NULL, NULL, NULL),
(8, 1, 'bro', 'capli', 'Gérard ', '2024-05-12 15:17:15', '', 'pardon', '', 'toother', NULL, NULL, NULL),
(9, 1, 'sis', '', '', '2024-05-12 15:33:57', '', 'remercie', '', 'myname', NULL, NULL, NULL),
(10, 1, 'bom', 'bertizr', 'geraldine', '2024-05-12 15:46:59', '', 'remercie', '', 'toother', NULL, NULL, NULL),
(11, 1, 'bsis', '', '', '2024-05-12 15:51:50', '', 'invocation', '', 'myname', NULL, NULL, NULL),
(12, 1, 'sister', 'Retyuffff', 'Ffs', '2024-05-12 00:00:00', '', 'invocation', '', 'toother', '', '+33', NULL),
(13, 1, 'bro', 'Smmmmo', 'Fsfg', '2024-05-12 00:00:00', '', 'searchdette', '', 'toother', '577335533', '+1684', NULL),
(16, 1, 'bro', 'sffr', 'tzww', '2024-05-13 18:38:04', '', 'salat', '', 'toother', NULL, NULL, 8),
(17, 1, 'bro', 'sdfff', 'fresh htrt', '2024-05-13 18:40:11', '', 'salat', '', 'toother', NULL, NULL, 9),
(18, 1, 'bro', 'ggg', 'hhh', '2024-06-03 11:35:00', '', 'salat', '', 'toother', NULL, NULL, 10),
(19, 9, 'bro', 'Nic', 'Dff', '2024-05-21 14:50:00', '', 'salat', '', 'toother', NULL, NULL, 4),
(20, 1, 'bro', 'dff', 'nic', '2024-05-21 14:52:00', '', 'salat', '', 'toother', NULL, NULL, 11),
(22, 1, 'sister', 'eedr', 'sde', '2024-05-23 10:14:44', '', 'salat', '', 'toother', NULL, NULL, 12),
(23, 1, 'bro', 'eeeee', 'ddwd', '2025-01-03 15:15:00', '', 'salat', '', 'toother', NULL, NULL, 13),
(25, 1, 'bof', 'man', 'cool', '2025-02-17 19:00:40', '', 'death', 'jhfg', 'myname', '', '+33', NULL),
(26, 1, 'father', '', '', '2025-02-17 19:06:26', '', 'invocation', '', 'myname', '', '+33', NULL),
(27, 1, 'father', '', '', '2025-02-17 00:00:00', '', 'invocation', '', 'myname', '', '+33', NULL),
(31, 1, 'father', 'tyyjv', 'trsh', '2025-03-02 15:13:00', '', 'salat', '', 'toother', NULL, NULL, 15),
(32, 1, 'grandm', 'ghv', 'ggv', '2025-02-26 08:24:15', '', 'salat', '', 'toother', NULL, NULL, 16),
(33, 1, 'dot', 'dsz', 'dzz', '2025-03-10 07:31:46', '', 'salat', '', 'toother', NULL, NULL, 17),
(34, 1, 'grandp', '', '', '2025-03-10 07:33:39', '', 'remercie', '', 'myname', '', '+33', NULL),
(35, 1, 'father', 'Manual', 'M9s', '2025-03-11 00:00:00', '', 'salat', '', 'toother', NULL, NULL, 18),
(36, 1, 'father', 'hhhhh', 'suur', '2025-03-11 09:19:46', '', 'salat', '', 'toother', NULL, NULL, 19),
(37, 15, 'father', 'gth', 'yhg', '2025-03-11 11:01:22', '', 'searchdette', '', 'toother', '66888696', '+33', NULL),
(38, 1, 'father', 'hjjjfr', 'yyy', '2025-03-18 15:47:24', '', 'pardon', '', 'toother', '', '+33', NULL),
(39, 1, 'father', 'dez', '1zzzdee', '2025-03-18 15:57:49', '', 'pardon', '', 'toother', '', '+33', NULL),
(40, 1, 'father', 'hjj', 'jjj', '2025-03-18 15:59:46', '', 'pardon', '', 'toother', '', '+33', NULL),
(41, 1, 'brother', 'jjuii', 'mooo', '2025-03-18 14:00:00', '', 'death', 'hhhhhhh', 'myname', '', '+33', NULL),
(42, 1, 'father', 'recc', 'test', '2025-03-19 13:21:49', '', 'searchdette', '', 'toother', '989666665', '+33', NULL),
(43, 1, 'father', 'Hyy', 'Ttt', '2025-03-19 00:00:00', '', 'salat', '', 'toother', NULL, NULL, 20),
(44, 1, 'father', 'Xde', 'Newwh', '2025-03-19 00:00:00', '', 'salat', '', 'toother', NULL, NULL, 21),
(45, 1, 'father', 'ooooo', 'juu', '2025-03-19 13:57:17', '', 'salat', '', 'toother', NULL, NULL, 22),
(46, 1, 'father', 'Ggg', 'Yyfgg', '2025-03-19 00:00:00', '', 'salat', '', 'toother', NULL, NULL, 23),
(47, 1, 'father', 'yygtyy', 'tttttt', '2025-03-19 14:00:17', '', 'salat', '', 'toother', NULL, NULL, 24),
(48, 1, 'brother', 'hhhhh', 'hjj', '2025-03-19 14:02:14', '', 'salat', '', 'toother', NULL, NULL, 25),
(49, 1, 'grandm', 'hhh', 'tes', '2025-03-20 16:51:33', '', 'remercie', '', 'toother', '', '+33', NULL),
(50, 5, 'father', 'gghgh', 'hhbhh', '2025-03-20 16:52:16', '', 'invocation', '', 'toother', '', '+33', NULL),
(53, 1, 'father', 'test', 'test', '2025-03-21 17:22:00', '', 'salat', '', 'toother', NULL, NULL, 28),
(55, 15, 'dot', 'nnn', 'nnn', '2025-03-21 23:25:00', '', 'salat', '', 'toother', NULL, NULL, 29),
(56, 1, 'father', 'tgggg', 'mosque 2', '2025-03-24 15:31:31', '', 'salat', '', 'toother', NULL, NULL, 30),
(57, 15, 'father', '', '', '2025-03-24 15:48:47', '', 'remercie', '', 'myname', '', '+33', NULL),
(58, 16, 'father', 'hhh', 'hhh', '2025-03-28 17:51:44', '', 'death', 'cvbj', 'myname', '', '+33', NULL),
(59, 16, 'father', '', '', '2025-03-28 17:52:19', '', 'pardon', '', 'myname', '', '+33', NULL),
(60, 16, 'father', '', '', '2025-04-03 11:04:35', '', 'pardon', '', 'myname', '', '+33', NULL),
(61, 16, 'father', 'gghhh', 'tfc', '2025-05-12 11:11:00', '', 'salat', '', 'toother', NULL, NULL, 31);

-- --------------------------------------------------------

--
-- Structure de la table `carte_share`
--

CREATE TABLE `carte_share` (
  `id` int NOT NULL,
  `carte_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `carte_share`
--

INSERT INTO `carte_share` (`id`, `carte_id`, `user_id`) VALUES
(3, 3, 1),
(9, 13, 3),
(10, 20, 2),
(11, 20, 3),
(12, 34, 1),
(14, 50, 15),
(16, 57, 1),
(17, 49, 15);

-- --------------------------------------------------------

--
-- Structure de la table `carte_text`
--

CREATE TABLE `carte_text` (
  `id` int NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `for_other` tinyint(1) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `carte_text`
--

INSERT INTO `carte_text` (`id`, `type`, `for_other`, `content`) VALUES
(1, 'pardon', 0, 'A Salem Alaykoum\n\nMoi {user_fullname}, je souhaite sincèrement vous demander pardon pour toutes paroles, gestes, regard ou tort qui auraient pu vous blesser\n\n“Que celui qui a fait du tort à son frère, lui demande pardon, car, (le jour du jugement) il n’y aura ni dinar, ni dirham. Sinon, il sera pris de ses bonnes actions pour les donner à son frère et s’il n’en a pas, alors, il sera pris des mauvaises actions de son frère pour les mettre sur son compte.”\n'),
(2, 'pardon', 1, 'A Salem Alaykoum\n\n\nAu nom de {other_fullname}, je souhaite sincèrement vous demander pardon pour toutes paroles, gestes, regard ou tort qui auraient pu vous blesser.\n\n“Que celui qui a fait du tort à son frère, lui demande pardon, car, (le jour du jugement) il n’y aura ni dinar, ni dirham. Sinon, il sera pris de ses bonnes actions pour les donner à son frère et s’il n’en a pas, alors, il sera pris des mauvaises actions de son frère pour les mettre sur son compte.”'),
(3, 'remercie', 0, 'A salem alaykoum\n\nJ’ai été profondément touché(e) par votre soutien, vos douas, votre bienveillance lors du décès de {genre_affiliation} {affiliation} {allyramou_genre} et je tenais sincèrement à vous remercier.\n\nQu’Allah vous accorde la meilleure des récompenses Amine'),
(4, 'remercie', 1, 'A salem alaykoum\n\nJe tenais à vous adresser ce message  au nom de {other_fullname} qui a été profondément touché(e) par votre  soutien, vos douas, votre bienveillance lors du décès de : {genre_affiliation} {affiliation}\n\nQu’Allah vous accorde la meilleure des récompenses Amine\n'),
(5, 'invocation', 0, 'A salem alaykoum\n\nMoi {user_fullname}, je vous demande des douas dans vos prières  pour {genre_affiliation} {affiliation} {allyramou_genre}  qui vient de retourner auprès de  son créateur\n\n\n“Ô Allah, pardonne-lui et accorde-lui Ta miséricorde. Accorde-lui le salut et le pardon. Assure-lui une noble demeure. Élargis-lui sa tombe et lave-le avec l’eau, la neige et la grêle. Nettoie-le de ses péchés comme on nettoie le vêtement blanc de la saleté. Donne-lui en échange une demeure meilleure que la sienne et une épouse meilleure que la sienne. Fais-le entrer au Paradis et préserve-le du châtiment de la tombe (et du châtiment de l’Enfer).”'),
(6, 'invocation', 1, 'A salem alaykoum\r\n\r\nAu nom de  {other_fullname}, je vous demande des douas dans vos prières  pour {genre_affiliation} {affiliation} {allyramou_genre} qui vient de retourner auprès de son créateur\r\n\r\n“Ô Allah, pardonne-lui et accorde-lui Ta miséricorde. Accorde-lui le salut et le pardon. Assure-lui une noble demeure. Elargis-lui sa tombe et lave-le avec l’eau, la neige et la grêle. Nettoie-le de ses péchés comme on nettoie le vêtement blanc de la saleté. Donne-lui en échange une demeure meilleure que la sienne et une épouse meilleure que la sienne. Fais-le entrer au Paradis et préserve-le du châtiment de la tombe (et du châtiment de l’Enfer).”'),
(7, 'searchdette', 1, 'Assalem alaykoum\n\nSuite au décès de {other_fullname}, qu’Allah lui fasse miséricorde, nous recherchons toute personne ayant une dette ou un emprunt envers le défunt.\nContactez-nous au : {other_phone}\n\nBarakallah ou fikoum, Le Prophète SAW a dit : « L\'âme du croyant reste suspendue entre la damnation et le salut jusqu\'à ce qu\'il acquitte sa dette. » (Sunan At-Tirmidhî ,hadith Hasan)');

-- --------------------------------------------------------

--
-- Structure de la table `chat_message`
--

CREATE TABLE `chat_message` (
  `id` int NOT NULL,
  `created_by_id` int DEFAULT NULL,
  `chat_thread_id` int NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `file_id` int DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_message`
--

INSERT INTO `chat_message` (`id`, `created_by_id`, `chat_thread_id`, `content`, `created_at`, `file_id`, `deleted_at`, `updated_at`) VALUES
(1, 1, 1, 'Ttg', '2023-12-14 11:08:27', NULL, NULL, NULL),
(2, 1, 1, 'Dfgg', '2023-12-14 11:10:50', NULL, NULL, NULL),
(3, 1, 2, 'Tesdgg', '2023-12-14 11:11:24', NULL, NULL, '2025-02-10 11:00:03'),
(4, 1, 2, '', '2023-12-14 11:31:31', NULL, '2025-02-10 10:57:22', NULL),
(5, 1, 2, 'Zfgh', '2024-02-12 12:13:29', NULL, NULL, NULL),
(6, 1, 1, 'Sfgfg', '2024-02-14 11:48:21', NULL, NULL, NULL),
(7, 3, 1, 'Greatgcfg', '2024-02-14 12:01:46', NULL, NULL, NULL),
(8, 1, 1, 'Dfg', '2024-02-16 08:53:31', NULL, NULL, NULL),
(9, 1, 1, 'D', '2024-02-16 08:53:35', NULL, NULL, NULL),
(10, 1, 1, 'F', '2024-02-16 08:53:37', NULL, NULL, NULL),
(11, 1, 1, 'Ddg', '2024-02-16 08:53:43', NULL, NULL, NULL),
(12, 1, 1, 'Dfg', '2024-02-16 08:53:46', NULL, NULL, NULL),
(13, 1, 1, 'Fft', '2024-02-16 08:53:48', NULL, NULL, NULL),
(14, 1, 1, 'Ee', '2024-02-16 08:54:00', NULL, NULL, NULL),
(15, 1, 1, 'la pompe funèbre pompe 2 gre souhaite vous accompagner', '2024-02-19 17:25:05', NULL, NULL, NULL),
(16, 6, 5, 'Test@test.fr ddd', '2024-03-23 11:59:05', NULL, NULL, NULL),
(17, 1, 6, 'La pompe funèbre : Pompe Gap souhaite vous accompagner', '2024-04-11 11:21:49', NULL, NULL, NULL),
(18, 1, 7, '', '2024-04-24 14:23:34', NULL, '2025-02-10 09:00:34', NULL),
(19, 1, 7, 'Dxcf', '2024-04-24 14:29:52', NULL, NULL, NULL),
(20, NULL, 7, '', '2025-02-10 09:21:48', 8, NULL, NULL),
(21, 1, 7, '', '2025-02-10 09:23:11', NULL, '2025-02-10 10:49:45', NULL),
(22, 1, 7, '', '2025-02-10 10:50:37', 10, NULL, NULL),
(23, 1, 8, 'Hjfg', '2025-02-10 11:20:51', NULL, NULL, NULL),
(24, 1, 10, 'Ghh', '2025-02-12 12:54:55', NULL, NULL, NULL),
(25, 1, 12, 'Hdhhhdh', '2025-03-10 04:01:15', NULL, NULL, NULL),
(26, 1, 11, 'Hhhh', '2025-03-10 04:04:05', NULL, NULL, NULL),
(27, 1, 11, 'Ddff', '2025-03-10 04:04:10', NULL, NULL, NULL),
(28, 1, 11, '', '2025-03-10 04:04:41', NULL, '2025-03-13 14:09:57', NULL),
(29, 1, 11, 'Hshhd hdhvvd', '2025-03-10 04:04:49', NULL, NULL, NULL),
(30, 1, 11, 'Ddgg', '2025-03-10 04:04:59', NULL, NULL, NULL),
(44, 1, 14, 'Ddddf', '2025-03-14 06:06:42', NULL, NULL, NULL),
(45, 1, 14, 'Group', '2025-03-14 06:12:05', NULL, NULL, NULL),
(46, 15, 14, 'from tne', '2025-03-14 06:12:05', NULL, NULL, NULL),
(47, 8, 14, 'from 8', '2025-03-14 06:13:05', NULL, NULL, NULL),
(49, 15, 13, 'Thh', '2025-03-14 14:39:10', NULL, NULL, NULL),
(50, 16, 22, '', '2025-04-11 13:05:08', NULL, '2025-05-14 16:19:38', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `chat_notification`
--

CREATE TABLE `chat_notification` (
  `id` int NOT NULL,
  `thread_id` int NOT NULL,
  `user_id` int NOT NULL,
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_notification`
--

INSERT INTO `chat_notification` (`id`, `thread_id`, `user_id`, `updated_at`) VALUES
(5, 5, 4, '2024-03-23 11:59:05'),
(6, 6, 6, '2024-04-11 11:21:49'),
(7, 7, 5, '2025-02-10 10:50:40'),
(8, 8, 2, '2025-02-10 11:20:51'),
(9, 8, 5, '2025-02-10 11:20:51'),
(11, 12, 8, '2025-03-10 04:01:15'),
(12, 12, 10, '2025-03-10 04:01:15'),
(13, 11, 8, '2025-03-10 04:04:59'),
(21, 14, 2, '2025-03-14 06:12:05'),
(22, 14, 4, '2025-03-14 06:12:05'),
(29, 14, 15, '2025-03-14 06:12:05');

-- --------------------------------------------------------

--
-- Structure de la table `chat_participant`
--

CREATE TABLE `chat_participant` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `thread_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_participant`
--

INSERT INTO `chat_participant` (`id`, `user_id`, `thread_id`) VALUES
(1, 1, 1),
(3, 1, 2),
(10, 1, 6),
(12, 1, 7),
(14, 1, 8),
(17, 1, 9),
(19, 1, 10),
(21, 1, 11),
(23, 1, 12),
(27, 1, 13),
(28, 1, 14),
(35, 1, 15),
(49, 1, 22),
(15, 2, 8),
(29, 2, 14),
(2, 3, 1),
(9, 4, 5),
(18, 4, 9),
(30, 4, 14),
(13, 5, 7),
(16, 5, 8),
(8, 6, 5),
(11, 6, 6),
(22, 8, 11),
(24, 8, 12),
(31, 8, 14),
(20, 10, 10),
(25, 10, 12),
(32, 10, 14),
(26, 15, 13),
(34, 15, 14),
(50, 16, 22);

-- --------------------------------------------------------

--
-- Structure de la table `chat_thread`
--

CREATE TABLE `chat_thread` (
  `id` int NOT NULL,
  `last_message_id` int DEFAULT NULL,
  `created_by_id` int DEFAULT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_update` datetime NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_id` int DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_thread`
--

INSERT INTO `chat_thread` (`id`, `last_message_id`, `created_by_id`, `type`, `last_update`, `name`, `reference`, `image_id`, `deleted_at`) VALUES
(1, 15, 1, 'simple', '2024-02-19 17:25:05', NULL, NULL, NULL, NULL),
(2, 5, 1, 'group', '2024-02-12 12:13:29', 'groupbb', NULL, 11, '2025-02-10 11:20:08'),
(5, 16, 6, 'simple', '2024-03-23 11:59:05', NULL, NULL, NULL, NULL),
(6, 17, 1, 'simple', '2024-04-11 11:21:49', NULL, NULL, NULL, NULL),
(7, 22, 1, 'simple', '2025-02-10 10:50:37', NULL, NULL, NULL, NULL),
(8, 23, 1, 'group', '2025-02-10 11:20:51', 'supet', NULL, NULL, NULL),
(9, NULL, 1, 'simple', '2025-02-12 12:44:41', NULL, NULL, NULL, NULL),
(10, 24, 1, 'simple', '2025-02-12 12:54:55', NULL, NULL, NULL, NULL),
(11, 30, 1, 'simple', '2025-03-10 04:04:59', NULL, NULL, NULL, NULL),
(12, 25, 1, 'group', '2025-03-10 04:01:15', NULL, NULL, NULL, NULL),
(13, 49, 15, 'simple', '2025-03-14 14:39:10', NULL, NULL, NULL, NULL),
(14, 45, 1, 'group', '2025-03-14 06:12:16', NULL, NULL, NULL, NULL),
(15, NULL, 1, 'simple', '2025-04-03 08:36:00', NULL, NULL, NULL, NULL),
(22, 50, 1, 'simple', '2025-04-11 13:05:08', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `dece`
--

CREATE TABLE `dece` (
  `id` int NOT NULL,
  `location_id` int DEFAULT NULL,
  `created_by_id` int NOT NULL,
  `afiliation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lieu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `notif_pf` tinyint(1) DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dece`
--

INSERT INTO `dece` (`id`, `location_id`, `created_by_id`, `afiliation`, `lieu`, `firstname`, `lastname`, `date`, `created_at`, `notif_pf`, `phone`) VALUES
(1, 19, 1, 'bop', 'maison', 'britany', 'beui', '2022-01-17 00:00:00', '2023-12-19 16:21:33', NULL, NULL),
(2, 20, 1, 'father', 'maison', 'zgggz', 'hsc', '2023-12-10 18:08:32', '2023-12-19 18:13:06', NULL, NULL),
(3, 21, 1, 'tante', 'maison', 'ruli', 'francis', '2023-10-19 00:00:00', '2023-12-19 18:17:40', NULL, NULL),
(4, 22, 1, 'bop', 'maison', 'ferbu', 'Émilie ', '2022-10-19 00:00:00', '2023-12-19 18:28:54', NULL, NULL),
(5, 28, 1, 'father', 'maison', 'tesgr', 'pfd', '2024-02-16 11:16:04', '2024-02-16 11:16:31', 1, NULL),
(6, 30, 1, 'father', 'maison', 'wxff', 'fddzs', '2024-02-16 13:55:09', '2024-02-16 13:55:28', 1, NULL),
(7, 31, 1, 'father', 'maison', 'wxff', 'fddzs', '2024-02-16 13:55:09', '2024-02-16 13:59:14', 1, NULL),
(8, 32, 1, 'father', 'maison', 'wxff', 'fddzs', '2024-02-16 13:55:09', '2024-02-16 14:00:10', 1, NULL),
(9, 33, 1, 'father', 'maison', 'hhhdh', 'yeuj', '2024-02-19 17:47:07', '2024-02-19 17:51:46', 0, NULL),
(10, 34, 1, 'father', 'maison', 'hhhdh', 'yeuj', '2024-02-19 17:47:07', '2024-02-19 17:53:10', 0, NULL),
(11, 35, 1, 'father', 'maison', 'xfxfg', 'dff', '2024-02-19 17:53:22', '2024-02-19 17:53:44', 0, NULL),
(12, 36, 1, 'father', 'maison', 'xfxfg', 'dff', '2024-02-19 17:53:22', '2024-02-19 17:54:19', 0, NULL),
(13, 37, 1, 'father', 'maison', 'xfxfg', 'dff', '2024-02-19 17:53:22', '2024-02-19 17:55:12', 0, NULL),
(15, 39, 1, 'father', 'maison', 'fddrgg', 'gfffggfd', '2024-02-27 00:00:00', '2024-02-27 09:07:35', 1, ''),
(16, 41, 6, 'son', 'maison', 'fgbb', 'res', '2024-04-11 10:57:53', '2024-04-11 11:01:01', 1, NULL),
(17, 44, 1, 'bro', 'maison', 'e44r4', 'zdf', '2024-04-24 18:46:12', '2024-04-24 18:46:24', 1, ''),
(18, 45, 1, 'bro', 'maison', 'ffffff', 'deesdde', '2024-05-08 09:27:39', '2024-05-08 09:32:23', 1, '');

-- --------------------------------------------------------

--
-- Structure de la table `deuil`
--

CREATE TABLE `deuil` (
  `id` int NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `deuil`
--

INSERT INTO `deuil` (`id`, `content`, `type`) VALUES
(1, '&amp;lt;p&amp;gt;Nous somme d&eacute;sol&eacute; &hellip; le deuil durera&amp;lt;strong&amp;gt; jusqu&amp;#039;au {date_plus_trois_jour}&amp;lt;/strong&amp;gt;.&amp;lt;br&amp;gt;Si vous &ecirc;tes enceint, la date de d&eacute;but de deuil correspond &agrave; la date de votre accouchement.&amp;amp;nbsp;&amp;lt;/p&amp;gt;', 'family'),
(2, '&amp;lt;p&amp;gt;Epouse deuil date&amp;lt;strong&amp;gt; jusqu&amp;#039;au {datefin}&amp;lt;/strong&amp;gt;.&amp;lt;br&amp;gt;Si vous &ecirc;tes enceint, la date de d&eacute;but de deuil correspond &agrave; la date de votre accouchement.&amp;amp;nbsp;&amp;lt;/p&amp;gt;', 'epouse'),
(3, '&amp;lt;p&amp;gt;Epouse enceinte &hellip; le deuil durera&amp;lt;strong&amp;gt; jusqu&amp;#039;au {datefin}&amp;lt;/strong&amp;gt;.&amp;lt;br&amp;gt;Si vous &ecirc;tes enceint, la date de d&eacute;but de deuil correspond &agrave; la date de votre accouchement.&amp;amp;nbsp;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;amp;nbsp;&amp;lt;/p&amp;gt;', 'enceinte');

-- --------------------------------------------------------

--
-- Structure de la table `deuil_date`
--

CREATE TABLE `deuil_date` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `end_date` datetime NOT NULL,
  `ref` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `deuil_date`
--

INSERT INTO `deuil_date` (`id`, `user_id`, `end_date`, `ref`) VALUES
(20, 1, '2023-12-13 00:00:00', '1713956981'),
(35, 1, '2025-08-07 23:59:59', '1740741276'),
(36, 15, '2025-08-03 23:59:59', '1742820481');

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20240320134534', '2024-03-20 14:45:36', 53),
('DoctrineMigrations\\Version20240323093950', '2024-03-23 10:39:51', 43),
('DoctrineMigrations\\Version20240324105121', '2024-03-24 11:51:22', 74),
('DoctrineMigrations\\Version20240324111326', '2024-03-24 12:13:27', 31),
('DoctrineMigrations\\Version20240325074246', '2024-03-25 08:42:47', 20),
('DoctrineMigrations\\Version20240413091224', '2024-04-13 11:12:25', 130),
('DoctrineMigrations\\Version20240424104726', '2024-04-24 12:47:27', 48),
('DoctrineMigrations\\Version20240424110301', '2024-04-24 13:03:03', 24),
('DoctrineMigrations\\Version20240424154352', '2024-04-24 17:43:54', 30),
('DoctrineMigrations\\Version20240424155925', '2024-04-24 17:59:26', 39),
('DoctrineMigrations\\Version20240508083610', '2024-05-08 10:36:11', 126),
('DoctrineMigrations\\Version20240508152818', '2024-05-08 17:28:19', 13),
('DoctrineMigrations\\Version20240508163208', '2024-05-08 18:32:09', 24),
('DoctrineMigrations\\Version20240509063000', '2024-05-09 08:31:17', 232),
('DoctrineMigrations\\Version20240509092311', '2024-05-09 11:23:12', 33),
('DoctrineMigrations\\Version20240509160212', '2024-05-09 18:30:53', 41),
('DoctrineMigrations\\Version20240509163025', '2024-05-09 18:30:53', 13),
('DoctrineMigrations\\Version20240509163052', '2024-05-09 18:32:14', 1),
('DoctrineMigrations\\Version20240512143453', '2024-05-12 16:34:53', 20),
('DoctrineMigrations\\Version20240513142815', '2024-05-13 16:28:16', 86),
('DoctrineMigrations\\Version20240516130835', '2024-05-16 15:08:37', 85),
('DoctrineMigrations\\Version20240523084718', '2024-05-23 10:47:19', 20),
('DoctrineMigrations\\Version20250210072831', '2025-02-10 07:28:32', 81),
('DoctrineMigrations\\Version20250210081423', '2025-02-10 08:14:24', 15),
('DoctrineMigrations\\Version20250210102000', '2025-02-10 10:20:01', 20),
('DoctrineMigrations\\Version20250212122656', '2025-02-12 12:26:57', 11),
('DoctrineMigrations\\Version20250221122804', '2025-02-21 12:28:05', 21),
('DoctrineMigrations\\Version20250311050957', '2025-03-11 05:09:59', 22),
('DoctrineMigrations\\Version20250311051118', '2025-03-11 05:11:19', 46),
('DoctrineMigrations\\Version20250403055808', '2025-04-03 05:58:10', 26),
('DoctrineMigrations\\Version20250428140046', '2025-04-28 14:00:47', 84),
('DoctrineMigrations\\Version20250429133851', '2025-04-29 13:38:52', 33);

-- --------------------------------------------------------

--
-- Structure de la table `don`
--

CREATE TABLE `don` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `don`
--

INSERT INTO `don` (`id`, `name`, `description`, `link`, `image_id`) VALUES
(1, 'Secours Islamique France', '&amp;lt;p&amp;gt;Le Secours Islamique France (SIF) est une ONG nationale et internationale qui d&eacute;veloppe des actions humanitaires et sociales.&amp;lt;/p&amp;gt;', 'https://www.secours-islamique.org/', 4),
(2, 'Ummah Charity', '&amp;lt;p&amp;gt;Ummah Charity est une ONG de solidarit&eacute; internationale qui vise &agrave; all&eacute;ger les souffrances des populations les plus pauvres du monde.&amp;lt;/p&amp;gt;', 'https://ummahcharity.org/don', 3),
(4, 'association', '&amp;lt;p&amp;gt;pas de logo&amp;lt;/p&amp;gt;', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `faq`
--

CREATE TABLE `faq` (
  `id` int NOT NULL,
  `question` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reponse` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `online` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `faq`
--

INSERT INTO `faq` (`id`, `question`, `reponse`, `online`) VALUES
(1, 'dsqd ', '&amp;lt;p&amp;gt;ds DS dds&amp;lt;strong&amp;gt;q dqs d&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;', 1),
(2, 'Que dire et faire quand on assite au dernières heures d’un proche ?', '&amp;lt;p&amp;gt;DSQ DSQ D&amp;lt;a target=&amp;quot;_blank&amp;quot; rel=&amp;quot;noopener noreferrer&amp;quot; href=&amp;quot;https://google.fr&amp;quot;&amp;gt;sq DSQdf sf ds&amp;lt;/a&amp;gt;&amp;lt;br&amp;gt;Fdsfdsfdsf fdcxwcwcxv&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;dsdsqdsq&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;Un lein dans le text dd&amp;amp;nbsp;&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;dsqdsqdsq&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;', 1);

-- --------------------------------------------------------

--
-- Structure de la table `fcm_token`
--

CREATE TABLE `fcm_token` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `fcmToken` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `fcm_token`
--

INSERT INTO `fcm_token` (`id`, `user_id`, `fcmToken`, `device_id`) VALUES
(104, 1, 'ff0kRwjkSbGlLBBJZip2lL:APA91bH6L5p43jBsC5JWuR40jAUNkWEX66fcU6BnO7bZ8cuI3Y65zCbpTZuSCEqgWqzAU-nIgOqrn9xBE7wq3XlP4Uyk-o5hhDCXN7XTG_3K9lqZf3xIHcU', 'BP22.250325.006'),
(109, 13, 'evi48fGXQMS5K_pOHD8t2d:APA91bHqktW0fALNXdWLjGTFt7ljZ_SHSR-PGl81GcofQutAfDDJNP7dktk0ima39qp63DJXh07YZ6-8G37Fk0y6O7QD8LwVHORpsa5EbDx2ifaYU1wwEO8', 'HUAWEISNE-L21'),
(110, 1, 'evi48fGXQMS5K_pOHD8t2d:APA91bH5ucP2od9KW3_bcjBboRfM3QJs7OyFY19q6Usm5WG4JMLyU_Kp4yeWeJZrNWEdesACXUk4G8BLSGSkcJoorhik_fLVLVhAHMtveZHEcumJBLsqMTQ', 'HUAWEISNE-L21');

-- --------------------------------------------------------

--
-- Structure de la table `imam`
--

CREATE TABLE `imam` (
  `id` int NOT NULL,
  `location_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `online` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `imam`
--

INSERT INTO `imam` (`id`, `location_id`, `name`, `description`, `online`) VALUES
(1, 26, 'Imam Ju1', '&amp;lt;p&amp;gt;dsq DSQD&amp;lt;br&amp;gt;021563489&amp;lt;/p&amp;gt;', 1),
(2, 27, 'Imam2', '&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;fdsq&amp;lt;/strong&amp;gt; f&amp;lt;/p&amp;gt;', 1);

-- --------------------------------------------------------

--
-- Structure de la table `intro`
--

CREATE TABLE `intro` (
  `id` int NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `page` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `intro`
--

INSERT INTO `intro` (`id`, `content`, `page`) VALUES
(1, '&amp;lt;p&amp;gt;A SALAM WARLIKOUM&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;Nous sommes ravis de vous accueillir parmi nous et de vous offrir nos services. Nous sommes convaincus que notre collaboration sera fructueuse et nous esp&eacute;rons avoir l&amp;#039;occasion de vous montrer tout ce que nous avons &agrave; offrir&amp;lt;strong&amp;gt;. Si vous avez des questions&amp;lt;/strong&amp;gt; ou des pr&eacute;occupations, n&amp;#039;h&eacute;sitez pas &agrave; nous contacter. Nous sommes &agrave; votre disposition pour vous aider &agrave; tirer le meilleur parti de nos services. *** text modifiable&amp;lt;/p&amp;gt;', NULL),
(2, '&amp;lt;p&amp;gt;Que diriez-vous de&amp;amp;nbsp;contribuer &agrave; des actions humanitaires&amp;amp;nbsp;dont les&amp;amp;nbsp;bienfaits perdurent &agrave; tr&egrave;s long terme&amp;amp;nbsp;&amp;lt;br&amp;gt;C&rsquo;est la vocation de la&amp;amp;nbsp;Sadaqa Jariya. Aum&ocirc;ne continue, elle symbolise&amp;amp;nbsp;le don par excellence&amp;amp;nbsp;pour d&eacute;multiplier l&rsquo;impact de votre g&eacute;n&eacute;rosit&eacute;.&amp;lt;br&amp;gt;Protection de l&amp;#039;enfance, acc&egrave;s &agrave; l&amp;#039;eau potable... Votre Sadaqa Jariya change des vies&amp;lt;/p&amp;gt;', 'don');

-- --------------------------------------------------------

--
-- Structure de la table `invitation`
--

CREATE TABLE `invitation` (
  `id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accpeted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `invitation`
--

INSERT INTO `invitation` (`id`, `created_by_id`, `email`, `accpeted`) VALUES
(1, 4, 'invitemslconnect2@gmail.fr', 1),
(2, 1, 'yhhhh6@hggs.fr', 0);

-- --------------------------------------------------------

--
-- Structure de la table `jeun`
--

CREATE TABLE `jeun` (
  `id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `nb_days` smallint NOT NULL,
  `text` longtext COLLATE utf8mb4_unicode_ci,
  `selected_year` smallint NOT NULL,
  `jeun_nb_days_r` smallint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `jeun`
--

INSERT INTO `jeun` (`id`, `created_by_id`, `nb_days`, `text`, `selected_year`, `jeun_nb_days_r`) VALUES
(2, 16, 5, 'Yyyy', 2028, 3),
(3, 3, 6, 'Yyyy', 2028, 3);

-- --------------------------------------------------------

--
-- Structure de la table `location`
--

CREATE TABLE `location` (
  `id` int NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `adress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `location`
--

INSERT INTO `location` (`id`, `label`, `lat`, `lng`, `city`, `post_code`, `region`, `adress`) VALUES
(1, 'Grenoble', 45.1875602, 5.7357819, 'Grenoble', '', 'France métropolitaine', 'Grenoble'),
(2, 'Lyon', 45.758, 4.835, 'Lyon', '69001', '69, Rhône, Auvergne-Rhône-Alpes', 'Lyon'),
(3, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(4, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(5, 'Gap', 44.544606, 6.077989, 'Gap', '05000', '05, Hautes-Alpes, Provence-Alpes-Côte d\'Azur', 'Gap'),
(6, 'Paris', 48.859, 2.347, 'Paris', '75001', '75, Paris, Île-de-France', 'Paris'),
(7, 'Versailles', 48.803019, 2.131319, 'Versailles', '78000', '78, Yvelines, Île-de-France', 'Versailles'),
(8, 'Paris 20e Arrondissement', 48.863367, 2.397152, 'Paris 20e Arrondissement', '75020', '75, Paris, Île-de-France', 'Paris 20e Arrondissement'),
(9, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(10, 'Lyon', 45.758, 4.835, 'Lyon', '69001', '69, Rhône, Auvergne-Rhône-Alpes', 'Lyon'),
(11, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(12, 'Gap', 44.544606, 6.077989, 'Gap', '05000', '05, Hautes-Alpes, Provence-Alpes-Côte d\'Azur', 'Gap'),
(13, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(14, '34 Rue Lavoisier 38100 Grenoble', 45.177514, 5.733723, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', ''),
(18, 'Rue du Lot Mazeres II 83500 La Seyne-sur-Mer', 43.082807, 5.86091, 'La Seyne-sur-Mer', '83500', '83, Var, Provence-Alpes-Côte d\'Azur', 'Rue du Lot Mazeres II'),
(19, 'Square Lucien Pointet 05230 Chorges', 44.5456191, 6.2765868, 'Chorges', '05230', '05, Hautes-Alpes, Provence-Alpes-Côte d\'Azur', ''),
(20, 'Rue de Greux 37270 Montlouis-sur-Loire', 47.3789, 0.800336, 'Montlouis-sur-Loire', '37270', '37, Indre-et-Loire, Centre-Val de Loire', 'Rue de Greux'),
(21, 'Impasse Eglory Fontaine 97430 Le Tampon', -21.242434, 55.490453, 'Le Tampon', '97430', '974, La Réunion', ''),
(22, '39 Grande Rue 05230 Chorges', 44.5456558, 6.2765651, 'Chorges', '05230', '05, Hautes-Alpes, Provence-Alpes-Côte d\'Azur', ''),
(24, 'Marseille', 43.282, 5.405, 'Marseille', '13001', '13, Bouches-du-Rhône, Provence-Alpes-Côte d\'Azur', 'Marseille'),
(25, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(26, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(27, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(28, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(29, 'Rue de l\'Arc en Ciel 05000 Gap', 44.561365, 6.104013, 'Gap', '05000', '05, Hautes-Alpes, Provence-Alpes-Côte d\'Azur', 'Rue de l\'Arc en Ciel'),
(30, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(31, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(32, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(33, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(34, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(35, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(36, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(37, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', 'Grenoble'),
(39, 'Grenoble', 45.182081, 5.7243, 'Grenoble', '38100', '38, Isère, Auvergne-Rhône-Alpes', ''),
(40, 'Grenoble', 45.1875602, 5.7357819, 'Grenoble', '', 'France métropolitaine', 'Grenoble'),
(41, '05000 Gap', 44.5612032, 6.0820639, 'Gap', '05000', 'France métropolitaine', '05000 Gap'),
(43, '9 Rue des Eyguières, 05230 Chorges', 44.545662, 6.2765391, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(44, '9 Rue des Eyguières, 05230 Chorges', 44.5456614, 6.2765361, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(45, '9 Rue des Eyguières, 05230 Chorges', 44.5456613, 6.2765399, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(46, 'Rue du Plan de Gap, 05000 Gap', 44.5728226, 6.0963419, 'Gap', '05000', 'France métropolitaine', 'Rue du Plan de Gap, 05000 Gap'),
(47, '9 Rue des Eyguières, 05230 Chorges', 44.5456661, 6.2765291, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(48, '9 Rue des Eyguières, 05230 Chorges', 44.5456645, 6.2765295, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(49, '9 Rue des Eyguières, 05230 Chorges', 44.5456645, 6.2765295, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(50, '05000 Gap', 44.5612032, 6.0820639, 'Gap', '05000', 'France métropolitaine', '05000 Gap'),
(51, '9 Rue des Eyguières, 05230 Chorges', 44.5456653, 6.2765311, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(52, '9 Rue des Eyguières, 05230 Chorges', 44.5456653, 6.2765311, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(53, '9 Rue des Eyguières, 05230 Chorges', 44.5456653, 6.2765311, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(54, '9 Rue des Eyguières, 05230 Chorges', 44.5456653, 6.2765311, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(55, '9 Rue des Eyguières, 05230 Chorges', 44.5456653, 6.2765311, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(56, '9 Rue des Eyguières, 05230 Chorges', 44.5456653, 6.2765311, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(57, '9 Rue des Eyguières, 05230 Chorges', 44.5456653, 6.2765311, 'Chorges', '05230', 'France métropolitaine', '9 Rue des Eyguières, 05230 Chorges'),
(58, 'Ambérieu-en-Bugey', 45.9722369, 5.3475684249629, 'Ambérieu-en-Bugey', '01500', '01', '34 Av. Maréchal de Lattre de Tassigny'),
(59, 'Ambérieu-en-Bugey', 45.9722369, 5.3475684249629, 'Ambérieu-en-Bugey', '01500', '01', '34 Av. Maréchal de Lattre de Tassigny'),
(60, 'Grenoble', 45.1875602, 5.7357819, 'Grenoble', '', 'France métropolitaine', 'Grenoble'),
(61, 'Lyon', 45.7578137, 4.8320114, 'Lyon', '', 'France métropolitaine', 'Lyon');

-- --------------------------------------------------------

--
-- Structure de la table `mail`
--

CREATE TABLE `mail` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mailkey` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mail`
--

INSERT INTO `mail` (`id`, `name`, `mailkey`, `content`, `subject`, `variables`) VALUES
(2, 'mail test envoi lors test', 'mail-test', '&amp;lt;p&amp;gt;Bonjour Franck,&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;dsqf sdfqs&amp;lt;strong&amp;gt; qfdsq fds&amp;lt;/strong&amp;gt;&amp;lt;br&amp;gt;&amp;lt;strong&amp;gt;Fdsfdsf &amp;lt;/strong&amp;gt;fdsfs&amp;lt;/p&amp;gt;', 'Mon objet ...', NULL),
(4, 'Réinitialiser mon mot de passe !', 'reset-password', '&amp;lt;p&amp;gt;Vous avez fait une demande de r&eacute;initialisation de mot de passe, voici votre de code de validation :&amp;lt;strong&amp;gt; {code}&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;', 'Réinitialiser mon mot de passe sur Muslim Connect!', '[\"{nom}\",\"{prenom}\",\"{code}\"]'),
(5, 'Notification pompe funèbre suite à une décès', 'pompe_notification', '&amp;lt;p&amp;gt;Bonjour &amp;amp;nbsp;{prenom} {nom},&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;Vous &ecirc;tes contact&eacute; suite au d&eacute;c&egrave;s de &amp;amp;nbsp;{prenom_dece} {nom_dece} &agrave; {lieu_dece} .&amp;amp;nbsp;&amp;lt;br&amp;gt;Consultez d&egrave;s &agrave; pr&eacute;sent l&amp;#039;annonce du d&eacute;c&egrave;s dans Muslim Connect et contactez la famille si vous souhaitez proposer vos services.&amp;amp;nbsp;&amp;lt;/p&amp;gt;', 'Demande de service à votre pompe funèbre', '[\"{nom}\",\"{prenom}\",\"{nom_dece}\",\"{prenom_dece}\",\"{lieu_dece}\" ]'),
(6, 'Notification mosquée suite à une décès', 'mosque_notification', '&amp;lt;p&amp;gt;Bonjour &amp;amp;nbsp;{prenom} {nom},&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;Vous &ecirc;tes contact&eacute; suite au d&eacute;c&egrave;s de &amp;amp;nbsp;{prenom_dece} {nom_dece} &agrave; {lieu_dece} .&amp;amp;nbsp;&amp;lt;br&amp;gt;Consultez d&egrave;s &agrave; pr&eacute;sent l&amp;#039;annonce du d&eacute;c&egrave;s dans Muslim Connect et contactez la famille si vous souhaitez proposer vos services.&amp;amp;nbsp;&amp;lt;/p&amp;gt;', 'Demande de service à votre pompe funèbre', '[\"{nom}\",\"{prenom}\",\"{nom_dece}\",\"{prenom_dece}\",\"{lieu_dece}\" ]'),
(7, 'Invitation à rejoindre Muslim Connect', 'invitation_mail', '&amp;lt;p&amp;gt;Bonjour,&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;{fromFullname} t&amp;#039;invite &agrave; rejoindre Muslim Connect. T&eacute;l&eacute;charge l&amp;#039;application et cr&eacute;&eacute; ton compte avec cette adresse email {toEmail} pour faire direcement partie de ses contacts.&amp;lt;/p&amp;gt;', 'Invitation à rejoindre Muslim Connect', '[\"{fromFullname}\",\"{toEmail}\" ]'),
(8, 'Notification Admin : pompe funèbre inscription', 'pompe_registration_admin', '&amp;lt;p&amp;gt;Bonjour,&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;Un nouveau compte Pompe fun&egrave;bre {nom_pompe} est &agrave; valider en administration.&amp;lt;/p&amp;gt;', 'Demande de validation compte pompe funèbre', '[\"{nom}\",\"{prenom}\",\"{nom_pompe}\"]'),
(9, 'Notification : validation compte pompe funèbre', 'pompe_registration_validation', '&amp;lt;p&amp;gt;Bonjour &amp;amp;nbsp;{prenom} {nom},&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;Votre compte pompe fun&egrave;bre sur Muslim Connect est valid&eacute;. Vous pouvez d&egrave;s &agrave; pr&eacute;sents recevoir des demandes. ..&amp;lt;/p&amp;gt;', 'Demande de validation de votre compte pompe funèbre accepté', '[\"{nom}\",\"{prenom}\",\"{nom_pompe}\"]');

-- --------------------------------------------------------

--
-- Structure de la table `maraude`
--

CREATE TABLE `maraude` (
  `id` int NOT NULL,
  `location_id` int NOT NULL,
  `date` datetime NOT NULL,
  `online` tinyint(1) NOT NULL,
  `validated` tinyint(1) NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `managed_by_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `maraude`
--

INSERT INTO `maraude` (`id`, `location_id`, `date`, `online`, `validated`, `description`, `managed_by_id`) VALUES
(1, 24, '2023-12-26 16:50:00', 1, 1, 'Heh dhhhd', 1),
(2, 25, '2023-12-27 12:46:00', 1, 1, '&amp;lt;p&amp;gt;dsqd qfdqs&amp;lt;/p&amp;gt;', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `media`
--

CREATE TABLE `media` (
  `id` int NOT NULL,
  `bucket` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `onS3` tinyint(1) NOT NULL,
  `folder` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordered` int DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_prefixes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `media`
--

INSERT INTO `media` (`id`, `bucket`, `filename`, `type`, `onS3`, `folder`, `version`, `ordered`, `label`, `size_prefixes`, `file_size`) VALUES
(3, 'muslimconect-images', 'logo_don_f40af7bc93eb68d4a531a4da8089c2a4.jpeg', 'image/jpeg', 1, 'association', '1707398198', NULL, NULL, NULL, NULL),
(4, 'muslimconect-images', 'logo_don_4f8b9a2f8e13ed4a4fc98ca71b7b2b93.jpeg', 'image/jpeg', 1, 'association', '1707398160', NULL, NULL, NULL, NULL),
(7, 'muslimconect-images', '1711016449bafhsc707s.jpeg', 'image_navpage', 1, 'navimage', '1711016450', NULL, 'door.jpeg', 'null', NULL),
(8, 'muslimconnect-private', '1739175708dssmrknzyq.jpeg', 'chat-image', 1, 'chat', '1739175710', NULL, 'Screenshot_20250204_165231_com.huawei.android.launcher_edit_1781324176720893.jpg', NULL, 0.129),
(10, 'muslimconnect-private', '17391810375pu9vsu4ju.pdf', 'chat-file', 1, 'chat', '1739181040', NULL, '1-itineraire-du-monastere-de-sant-pere-de-roda-a-la-vall-de-santa-creu.pdf', NULL, 1.333),
(11, 'muslimconect-images', '17391820541jrwfpqavw.jpeg', 'thread-profile', 1, 'thread', '1739182055', NULL, NULL, NULL, NULL),
(14, 'muslimconect-images', 'photo-u1_174054319380zsenr8vw.jpeg', 'image/jpeg', 1, 'user', '1740543194', NULL, NULL, NULL, NULL),
(16, 'muslimconnect-private', '17419588415v9cvgse6f.jpeg', 'chat-image', 1, 'chat', '1741958842', NULL, 'scaled_Screenshot_20250307_155342_com.idosportcoaching.idosportapp.jpg', NULL, 0.025);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `mosque`
--

CREATE TABLE `mosque` (
  `id` int NOT NULL,
  `location_id` int NOT NULL,
  `managed_by_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `online` tinyint(1) NOT NULL,
  `tel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mosque`
--

INSERT INTO `mosque` (`id`, `location_id`, `managed_by_id`, `name`, `description`, `online`, `tel`) VALUES
(1, 2, NULL, 'Mosque l', '&amp;lt;p&amp;gt;fdsq&amp;lt;/p&amp;gt;', 1, NULL),
(2, 3, NULL, 'Mosque 2', '&amp;lt;p&amp;gt;fds fsf ds&amp;lt;/p&amp;gt;', 1, NULL),
(3, 4, 1, 'Moque 3', '&amp;lt;p&amp;gt;fdsq fdsq&amp;amp;nbsp;sfg &amp;lt;strong&amp;gt;dfgh&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;a href=&amp;quot;gdddff&amp;quot; rel=&amp;quot;noopener noreferrer&amp;quot; target=&amp;quot;_blank&amp;quot;&amp;gt;Gggg&amp;lt;/a&amp;gt;&amp;lt;br&amp;gt;&amp;lt;/p&amp;gt;', 1, NULL),
(4, 5, NULL, 'Mosque 4', '&amp;lt;p&amp;gt;fdsf dsf ds&amp;lt;/p&amp;gt;', 1, NULL),
(5, 6, NULL, 'Paris', '&amp;lt;p&amp;gt;sqdf&amp;amp;nbsp;&amp;lt;/p&amp;gt;', 1, NULL),
(6, 7, NULL, 'Versailles', '&amp;lt;p&amp;gt;sss&amp;lt;/p&amp;gt;', 1, NULL),
(7, 8, NULL, 'paris 2', '&amp;lt;p&amp;gt;fsdf&amp;amp;nbsp;&amp;lt;/p&amp;gt;', 1, NULL),
(8, 9, NULL, 'grenoble 2', '&amp;lt;p&amp;gt;fs d&amp;lt;/p&amp;gt;', 1, NULL),
(9, 10, 3, 'lyon 2', '&amp;lt;p&amp;gt;fdsf&amp;amp;nbsp;&amp;lt;/p&amp;gt;', 1, NULL),
(10, 11, 2, 'Gre 3', '&amp;lt;p&amp;gt;fdsf ds&amp;amp;nbsp;&amp;lt;/p&amp;gt;', 1, NULL),
(11, 12, 1, 'Gap 2', '&amp;lt;p&amp;gt;fdsq fsq&amp;lt;/p&amp;gt;', 1, NULL),
(12, 58, NULL, 'Grande Mosquée d\'Ambérieu-en-Bugey', NULL, 1, '33971226624'),
(13, 59, NULL, 'Grande Mosquée d\'Ambérieu-en-Bugey', NULL, 1, '33971226624');

-- --------------------------------------------------------

--
-- Structure de la table `mosque_favorite`
--

CREATE TABLE `mosque_favorite` (
  `id` int NOT NULL,
  `mosque_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mosque_favorite`
--

INSERT INTO `mosque_favorite` (`id`, `mosque_id`, `user_id`) VALUES
(13, 2, 1),
(16, 4, 1),
(17, 10, 1),
(18, 8, 1),
(19, 12, 1);

-- --------------------------------------------------------

--
-- Structure de la table `mosque_notif_dece`
--

CREATE TABLE `mosque_notif_dece` (
  `id` int NOT NULL,
  `mosque_id` int NOT NULL,
  `dece_id` int NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `show_on_page` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mosque_notif_dece`
--

INSERT INTO `mosque_notif_dece` (`id`, `mosque_id`, `dece_id`, `created_at`, `show_on_page`) VALUES
(1, 3, 13, '2024-02-19 17:55:12', 0),
(2, 3, 15, '2024-02-27 09:07:35', 0),
(3, 10, 15, '2024-02-27 09:07:37', 0);

-- --------------------------------------------------------

--
-- Structure de la table `nav_page_content`
--

CREATE TABLE `nav_page_content` (
  `id` int NOT NULL,
  `image_id` int DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `video` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `nav_page_content`
--

INSERT INTO `nav_page_content` (`id`, `image_id`, `slug`, `title`, `content`, `video`) VALUES
(1, NULL, 'dette', 'Dette', '&Agrave; saisir', 'https://www.youtube.com/watch?v=nA98lpMZ2qc'),
(2, NULL, 'deuil', 'Deuil', '', NULL),
(3, 7, 'pray', 'Prière', '&amp;lt;p&amp;gt;&Agrave; saisir en admin &hellip;.Aliquam tincidunt purus dolor. Cras ornare erat ante, ut dictum arcu blandit vitae. Morbi ac elit aliquet, dignissim mauris imperdiet, consequat erat. Sed urna turpis,&amp;lt;/p&amp;gt;', 'https://www.youtube.com/watch?v=qcDl1W0iFwk'),
(4, NULL, 'don', 'Don', '&amp;lt;p&amp;gt;&Agrave; saisir don text&amp;lt;/p&amp;gt;', NULL),
(5, NULL, 'bidha', 'BIDHA/SUNNAH', '&amp;lt;p&amp;gt;&Agrave; saisir bidha&amp;lt;/p&amp;gt;', NULL),
(6, NULL, 'prep-salat', 'PREPARER SALAT JANAZA', 'À saisir', NULL),
(7, NULL, 'learn_pray', 'Apprendre la priere pour débutant', 'À saisir', NULL),
(8, NULL, 'learn_salat', 'Apprendre Salat al-janaza', '&Agrave; saisir', 'https://www.youtube.com/watch?v=5Bon404unOA'),
(9, NULL, 'learn_sourat', 'ourate facile a apprendre', 'À saisir', NULL),
(10, NULL, 'duha', 'Invocations Duha', 'À saisir', NULL),
(11, NULL, 'herite', 'Héritage', 'À saisir', NULL),
(12, NULL, 'ramadan', 'Ramadan', '&amp;lt;p&amp;gt;&Agrave; saisir cooo&amp;lt;/p&amp;gt;', NULL),
(13, NULL, 'puit', 'Ramadan', 'À saisir contenu offir un puit', NULL),
(14, NULL, 'offerCoran', 'Ramadan', 'À saisir contenu offir un Coran', NULL),
(15, NULL, 'buildMosque', 'Ramadan', 'À saisir contenu consgtruire une mosquée', NULL),
(16, NULL, 'hajiProcur', 'Ramadan', 'À saisir contenu Urma/ hajj par procuratio', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `notif_to_send`
--

CREATE TABLE `notif_to_send` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `datas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `send_at` datetime NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `view` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notif_to_send`
--

INSERT INTO `notif_to_send` (`id`, `user_id`, `message`, `title`, `datas`, `send_at`, `type`, `view`) VALUES
(33, 16, 'Vous entrez bientôt dans le temps de prière de la Salât : Maghrib', 'Rappel de prière', NULL, '2025-05-14 21:09:00', 'maghreb', 'pray');

-- --------------------------------------------------------

--
-- Structure de la table `obligation`
--

CREATE TABLE `obligation` (
  `id` int NOT NULL,
  `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adress` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime DEFAULT NULL,
  `raison` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `delay` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditon_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by_id` int NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_to_id` int DEFAULT NULL,
  `moyen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `obligation`
--

INSERT INTO `obligation` (`id`, `firstname`, `lastname`, `adress`, `tel`, `amount`, `date`, `raison`, `delay`, `conditon_type`, `created_by_id`, `created_at`, `type`, `related_to_id`, `moyen`, `date_start`, `status`, `deleted_at`) VALUES
(1, 'Franck', 'Fidgerald delapqsder hhd-dijhdb', '4 rue ds tremio\n8904 saint mol', '012345678', '60000', '2024-01-02 00:00:00', 'Dépannage\nÀ le ligne ', '😅 6 %mois ', 'single', 1, '2024-01-02 16:07:34', 'jed', NULL, 'Def edfff', '2024-01-02 00:00:00', 'refund', NULL),
(2, 'fdsfds', 'Eeeee', 'fdsfds', '333', '333', '2024-01-02 16:09:34', 'sfdsfds', 'sfdsfds', 'single', 1, '2024-01-02 16:16:54', 'jed', 6, NULL, '2024-01-10 00:00:00', NULL, NULL),
(3, 'Eeerr', 'Edfr', 'Errrrddd', 'Eeeee', '12€', '2024-01-02 16:17:53', 'Fusce lacinia euismod rutrum. Pellentesque rutrum sem at ex gravida, quis aliquam libero posuere. Morbi eleifend nec quam et ultricies. Donec rhoncus rutrum dui quis consequat. Nulla aliquam risus massa, tristique faucibus augue sollicitudin at. Phasellus eu ipsum eros. Duis pulvinar pellentesque accumsan. Nulla facilisi. Aliquam eu velit sit amet lectus tempus egestas sed quis leo. Nam eget fermentum enim. Curabitur tincidunt nisi sed ultricies dictum. Praesent in justo eget metus scelerisque sagittis. Curabitur convallis, turpis ac sollicitudin rutrum, sem sapien suscipit augue, at mattis nibh metus ut mi. Sed sed mi sed nulla interdum mollis a at odio. Nullam non vehicula magna, vitae scelerisque orci. ', 'Eee', 'single', 1, '2024-01-02 16:18:46', 'onm', 6, NULL, '2024-01-10 00:00:00', 'ini', '2024-04-15 09:50:37'),
(4, 'Sandrine', 'Dupuis', 'Errrrddd', 'Eeeee', '153€', '2024-01-02 16:17:53', 'Fusce lacinia euismod rutrum. Pellentesque rutrum sem at ex gravida, quis aliquam libero posuere. Morbi eleifend nec quam et ultricies. Donec rhoncus rutrum dui quis consequat. Nulla aliquam risus massa, tristique faucibus augue sollicitudin at. Phasellus eu ipsum eros. Duis pulvinar pellentesque accumsan. Nulla facilisi. Aliquam eu velit sit amet lectus tempus egestas sed quis leo. Nam eget fermentum enim. Curabitur tincidunt nisi sed ultricies dictum. Praesent in justo eget metus scelerisque sagittis. Curabitur convallis, turpis ac sollicitudin rutrum, sem sapien suscipit augue, at mattis nibh metus ut mi. Sed sed mi sed nulla interdum mollis a at odio. Nullam non vehicula magna, vitae scelerisque orci. ', 'Eee', 'single', 2, '2024-01-02 16:18:46', 'onm', 6, NULL, '2024-01-10 00:00:00', NULL, NULL),
(5, 'Emile ', 'Lopez', 'fdsfds', '444', '1500€', '2024-01-02 16:09:34', ' Sed vulputate dui ac lacus pulvinar eleifend. Nunc ipsum elit, lobortis eu aliquet at, maximus sed felis. Pellentesque leo magna, interdum nec erat ut, vestibulum pharetra est. Suspendisse euismod vitae turpis efficitur aliquet. Proin tincidunt, dolor in semper dignissim, nisi ante luctus erat, a vulputate justo enim fringilla neque. Quisque sed laoreet eros. Nam eu semper sapien, in viverra lorem. Suspendisse varius tortor ut tincidunt dictum.\n\nIn hac habitasse platea dictumst. Suspendisse venenatis metus vel metus tristique pellentesque. Etiam hendrerit hendrerit condimentum. Vestibulum tristique mollis sem ac laoreet. Donec placerat nec magna ac pretium. Fusce eget consectetur lacus. Aliquam in nulla quis felis ullamcorper efficitur quis in nibh. Nulla facilisi. Aenean non dolor sodales, pretium nibh eget, luctus tellus.\n\nNam vitae condimentum tellus. Proin tortor felis, lacinia blandit eros sit amet, posuere malesuada ipsum. Etiam mattis leo sed felis feugiat rutrum. Curabitur non sollicitudin ante. Nulla ornare semper dapibus. Aliquam erat volutpat. Vivamus fermentum laoreet tincidunt. Nulla eget viverra enim. Nunc facilisis accumsan nulla sit amet auctor. Nulla dictum velit justo, non finibus justo tristique sed. Praesent at risus feugiat, tempus augue id, semper ex. Vivamus ac purus placerat quam interdum rhoncus. ', 'sfdsfds', 'single', 2, '2024-01-02 16:16:54', 'jed', 6, NULL, '2024-01-10 00:00:00', NULL, NULL),
(6, 'Boris', 'Dhys', 'Dfddd', 'Dff', '10', '2024-03-24 00:00:00', 'Ffcggg', 'Dfffg', 'single', 6, '2024-03-24 12:00:28', 'jed', 1, 'moyen edd', '2024-05-01 00:00:00', 'refund', NULL),
(7, 'Boris', 'Bruyere', 'Ggg', '+3366995231', 'Hgggff', '2024-03-25 00:00:00', 'Ggcgg', 'Gff', 'notdefined', 6, '2024-03-25 08:08:58', 'onm', 1, 'Gggg', '2024-01-09 00:00:00', NULL, NULL),
(8, 'nocid4', 'dhys', 'Zdd', 'Sde', '445', '2024-03-25 08:29:11', 'Fff', 'Ers', 'notdefined', 6, '2024-03-25 08:29:41', 'amana', 4, 'Ffr', '1900-01-08 00:00:00', NULL, NULL),
(9, 'frali', 'gthbd', 'Shhhshv shhs', 'Shbvvd', '0', '2024-04-15 00:00:00', 'Dde edsdhhh', 'Dfqsdg', 'single', 1, '2024-04-15 09:57:52', 'amana', 3, '', '2025-03-13 00:00:00', NULL, NULL),
(10, 'Frali', 'Gthbd', 'Dddf', 'Wff', '0', '2024-04-15 00:00:00', 'Zerrr', 'Pret', 'single', 1, '2024-04-15 11:11:44', 'onm', 3, 'Dddd', '2025-04-15 00:00:00', 'ini', NULL),
(11, 'Yhbh', 'Hgg', '', '6788', '0', '2023-03-08 00:00:00', 'Eee', 'Ddffggg', 'single', 1, '2024-05-08 19:03:01', 'onm', 15, 'Eeedddd', '2024-12-30 00:00:00', NULL, NULL),
(12, 'nicolas', 'salem', '', '435677223', '0', '2024-05-08 00:00:00', 'Rdv Amana ggggg', '', 'single', 1, '2024-05-08 19:14:58', 'amana', 2, '', '2025-03-13 18:33:21', NULL, NULL),
(13, 'Isabelle S', 'Boris)', '', '+41 77 530 02 65', 'Ddd', '2024-05-14 11:38:02', '', 'Edd', 'single', 1, '2024-05-14 11:38:26', 'jed', NULL, 'Sdd', NULL, NULL, '2024-05-14 11:38:38'),
(14, 'Tddno r', 'Dfgd', '', 'Xdd', '5789', '2024-12-30 00:00:00', '', 'Yvccv pret', 'single', 1, '2024-12-30 14:57:10', 'onm', NULL, 'Vvhjjh', '2024-12-30 00:00:00', NULL, NULL),
(15, 'Boris', 'Dhys', 'Dfddd', '99999', '56', '2024-03-24 00:00:00', 'Ffcggg', 'Dfffg', 'single', 1, '2025-01-01 14:16:47', 'jed', NULL, 'moyen edd', '2024-05-01 00:00:00', NULL, NULL),
(16, 'Boris', 'Dhys jhhy hhffbb hhfghcjkuhhh', 'Dfddd', 'Dff', '6', '2024-03-24 00:00:00', 'Ffcggg', 'Dfffg', 'single', 1, '2025-01-01 14:22:00', 'jed', NULL, 'moyen edd', '2024-05-01 00:00:00', NULL, NULL),
(17, 'Nicolas', 'Salem', '', '098877777', '2', '2025-01-01 14:38:51', '', 'Hjjjvv', 'single', 1, '2025-01-01 14:39:18', 'onm', 2, '', '2025-04-01 00:00:00', NULL, '2025-02-13 14:59:49'),
(18, 'Nicolas', 'Salem', '', '789087778765', '20', '2025-02-13 00:00:00', '', 'Pret', 'single', 1, '2025-02-13 15:00:24', 'onm', 2, '', '2025-02-27 07:26:01', NULL, NULL),
(19, 'Nicolas', 'Salem', '', '45 222', '8888', '2025-02-26 08:35:16', '', 'Fdhnsfjfsjfsjfs', 'single', 1, '2025-02-26 05:35:45', 'jed', 2, '', '2025-07-26 00:00:00', NULL, NULL),
(20, 'Sahred with me', 'Dfffxftt', '', '67888', '668', '2025-02-26 08:40:27', '', 'Ghhv', 'single', 8, '2025-02-26 05:40:50', 'onm', 1, '', '2025-08-31 00:00:00', NULL, NULL),
(21, 'Borisrrr', 'Bruyère', 'Grenoble', '555368', '20', '2025-03-10 00:00:00', '', 'Test de prêt de testne', 'single', 15, '2025-03-10 12:07:15', 'onm', 1, '', '2024-03-26 00:00:00', NULL, NULL),
(22, 'Testne', 'Tyiih', '', '666666666', '66', '2025-03-10 00:00:00', '', 'Test emprunt de boris à testé edit', 'single', 1, '2025-03-10 12:37:30', 'onm', 15, '', '2025-01-26 00:00:00', 'processing', NULL),
(23, 'Borisrrr', 'Bruyère', 'Grenoble', '555368', '333', '2025-05-10 00:00:00', '', 'Emprunt de testnr a borisb', 'single', 15, '2025-03-10 13:53:49', 'jed', 1, '', '2025-03-28 17:19:49', 'processing', NULL),
(24, 'Borisrrr', 'Bruyère', 'Grenoble', '555368', '0', '2025-03-10 16:59:36', 'Amana', '', 'single', 15, '2025-03-10 13:59:51', 'amana', 1, '', NULL, 'processing', NULL),
(25, 'Nicolas', 'Salem', '', '8888888888', '321', '2025-03-11 08:46:18', '', 'Prêt de Nicolas à Boris ', 'single', 1, '2025-03-11 05:46:54', 'jed', 2, '', '2025-03-12 15:22:35', 'processing', NULL),
(26, 'No user', 'No in msl', '', '0pppp', '20', '2025-03-17 08:31:02', '', 'Jggvjk', 'single', 1, '2025-03-17 05:31:35', 'jed', 2, '', '2025-03-21 00:00:00', 'processing', NULL),
(27, 'Borisrrr', 'Bruyère', 'Grenoble', '4555368', '10', '2025-03-24 00:00:00', '', 'Hhhh', 'single', 15, '2025-03-24 11:32:46', 'onm', 1, '', '2025-03-24 00:00:00', 'processing', '2025-03-24 13:31:17'),
(28, 'Borisrrr', 'Bruyère', 'Grenoble', '4555368', '10', '2025-03-24 00:00:00', '', 'from restfox', 'single', 1, '2025-03-24 11:39:27', 'jed', 15, '', '2025-03-31 00:00:00', 'processing', NULL),
(29, 'Borisrrr', 'Bruyère', 'Grenoble', '4555368', '10', '2025-03-24 14:32:18', '', 'DETTE de Boris BR from restfox', 'single', 1, '2025-03-24 11:40:34', 'jed', 15, '', '2025-05-24 00:00:00', 'refund', NULL),
(30, 'Borisrrr', 'Bruyère', 'Grenoble', '4555368', '1155', '2025-03-24 14:32:18', '', 'DETTE de Boris BR from restfox', 'single', 1, '2025-03-24 11:56:37', 'jed', 15, '', '2025-05-24 00:00:00', 'processing', '2025-03-26 15:01:16'),
(31, 'Borisrrr', 'Bruyère', 'Grenoble', '4555368', '96', '2025-03-24 15:18:33', '', 'Ggg', 'single', 15, '2025-03-25 12:21:29', 'jed', 1, '', '2025-03-25 15:21:25', 'processing', '2025-03-26 15:01:31'),
(32, 'Borisrrr', 'Bruyère', 'Grenoble', '4555368', '0', '2025-03-24 15:21:45', 'Jjj', '', 'single', 15, '2025-03-24 12:22:20', 'amana', 1, '', NULL, 'processing', NULL),
(33, 'Nocid4', 'Dhys', '', '09999999', '1', '2025-03-28 00:00:00', '', 'Hggg', 'single', 1, '2025-03-28 14:20:57', 'onm', 4, '', '2025-03-28 17:39:21', 'processing', NULL),
(34, 'Borisrrr', 'Bruyère', 'Grenoble', '4555368', '58', '2025-05-23 00:00:00', '', 'Raghcg', 'single', 16, '2025-05-23 08:11:01', 'onm', 1, '', '2025-01-27 00:00:00', 'processing', NULL),
(35, 'Borisrrr', 'Bruyère', 'Grenoble', '4555368', '8', '2025-05-23 10:11:56', '', 'E4Ds', 'single', 16, '2025-05-23 08:12:18', 'jed', 1, '', '2025-11-23 00:00:00', 'processing', NULL),
(36, 'Nicolas', 'Salem', '', 'H66677', '20', '2025-02-02 00:00:00', '', 'Hhhbh', 'single', 1, '2025-06-01 07:48:05', 'onm', 2, '', '2025-08-01 00:00:00', 'processing', NULL),
(37, 'Nicolas', 'Salem', '', '66y', '20', '2025-02-13 00:00:00', '', 'Hhjjnb', 'single', 1, '2025-06-01 07:53:38', 'onm', 2, '', '2025-12-01 00:00:00', 'processing', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `page`
--

CREATE TABLE `page` (
  `id` int NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `page`
--

INSERT INTO `page` (`id`, `slug`, `content`, `title`) VALUES
(1, 'cgu', '&amp;lt;p&amp;gt;&Agrave; saisir update cgu&amp;lt;/p&amp;gt;', 'Conditions Générales d\'utilisation'),
(2, 'mentions', '&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;D&eacute;finitions&amp;lt;/strong&amp;gt;&amp;lt;br&amp;gt;Client : tout professionnel ou personne physique capable au sens des articles 1123 et suivants du Code civil, ou personne morale, qui visite le Site objet des pr&eacute;sentes conditions g&eacute;n&eacute;rales.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Prestations et Services : https://muslim-connect.fr met &agrave; disposition des Clients :&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Contenu : Ensemble des &eacute;l&eacute;ments constituants l&rsquo;information pr&eacute;sente sur le Site, notamment textes &ndash; images &ndash; vid&eacute;os.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Informations clients : Ci apr&egrave;s d&eacute;nomm&eacute; &laquo; Information (s) &raquo; qui correspondent &agrave; l&rsquo;ensemble des donn&eacute;es personnelles susceptibles d&rsquo;&ecirc;tre d&eacute;tenues par https://muslim-connect.fr pour la gestion de votre compte, de la gestion de la relation client et &agrave; des fins d&rsquo;analyses et de statistiques.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Utilisateur : Internaute se connectant, utilisant le site susnomm&eacute;.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Informations personnelles : &laquo; Les informations qui permettent, sous quelque forme que ce soit, directement ou non, l&amp;#039;identification des personnes physiques auxquelles elles s&amp;#039;appliquent &raquo; (article 4 de la loi n&deg; 78-17 du 6 janvier 1978).&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Les termes &laquo; donn&eacute;es &agrave; caract&egrave;re personnel &raquo;, &laquo; personne concern&eacute;e &raquo;, &laquo; sous traitant &raquo; et &laquo; donn&eacute;es sensibles &raquo; ont le sens d&eacute;fini par le R&egrave;glement G&eacute;n&eacute;ral sur la Protection des Donn&eacute;es (RGPD : n&deg; 2016-679)&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;lt;strong&amp;gt;1. Pr&eacute;sentation du site internet.&amp;lt;/strong&amp;gt;&amp;lt;br&amp;gt;En vertu de l&amp;#039;article 6 de la loi n&deg; 2004-575 du 21 juin 2004 pour la confiance dans l&amp;#039;&eacute;conomie num&eacute;rique, il est pr&eacute;cis&eacute; aux utilisateurs du site internet https://muslim-connect.fr l&amp;#039;identit&eacute; des diff&eacute;rents intervenants dans le cadre de sa r&eacute;alisation et de son suivi:&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Propri&eacute;taire : MUSLIM CONNECT Capital social de 1000&euro; Num&eacute;ro de TVA: xxx &ndash; 1 rue des peupliers &amp;amp;nbsp;94230 cachan&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Responsable publication : MUSLIM CONNECT &ndash; contact@muslim-connect.fr&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Le responsable publication est une personne physique ou une personne morale.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Webmaster : Webmaster &ndash; contact@muslim-connect.fr&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;H&eacute;bergeur : ovh &ndash; 2 rue Kellermann 59100 Roubaix 1007&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;D&eacute;l&eacute;gu&eacute; &agrave; la protection des donn&eacute;es : Contact &ndash; contact@muslim-connect.fr&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;2. Conditions g&eacute;n&eacute;rales d&rsquo;utilisation du site et des services propos&eacute;s.&amp;lt;br&amp;gt;Le Site constitue une &oelig;uvre de l&rsquo;esprit prot&eacute;g&eacute;e par les dispositions du Code de la Propri&eacute;t&eacute; Intellectuelle et des R&eacute;glementations Internationales applicables. Le Client ne peut en aucune mani&egrave;re r&eacute;utiliser, c&eacute;der ou exploiter pour son propre compte tout ou partie des &eacute;l&eacute;ments ou travaux du Site.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;L&rsquo;utilisation du site https://muslim-connect.fr implique l&rsquo;acceptation pleine et enti&egrave;re des conditions g&eacute;n&eacute;rales d&rsquo;utilisation ci-apr&egrave;s d&eacute;crites. Ces conditions d&rsquo;utilisation sont susceptibles d&rsquo;&ecirc;tre modifi&eacute;es ou compl&eacute;t&eacute;es &agrave; tout moment, les utilisateurs du site https://muslim-connect.fr sont donc invit&eacute;s &agrave; les consulter de mani&egrave;re r&eacute;guli&egrave;re.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Ce site internet est normalement accessible &agrave; tout moment aux utilisateurs. Une interruption pour raison de maintenance technique peut &ecirc;tre toutefois d&eacute;cid&eacute;e par https://muslim-connect.fr, qui s&rsquo;efforcera alors de communiquer pr&eacute;alablement aux utilisateurs les dates et heures de l&rsquo;intervention. Le site web https://muslim-connect.fr est mis &agrave; jour r&eacute;guli&egrave;rement par https://muslim-connect.fr responsable. De la m&ecirc;me fa&ccedil;on, les mentions l&eacute;gales peuvent &ecirc;tre modifi&eacute;es &agrave; tout moment : elles s&rsquo;imposent n&eacute;anmoins &agrave; l&rsquo;utilisateur qui est invit&eacute; &agrave; s&rsquo;y r&eacute;f&eacute;rer le plus souvent possible afin d&rsquo;en prendre connaissance.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;3. Description des services fournis.&amp;lt;br&amp;gt;Le site internet https://muslim-connect.fr a pour objet de fournir une information concernant l&rsquo;ensemble des activit&eacute;s de la soci&eacute;t&eacute;. https://muslim-connect.fr s&rsquo;efforce de fournir sur le site https://muslim-connect.fr des informations aussi pr&eacute;cises que possible. Toutefois, il ne pourra &ecirc;tre tenu responsable des oublis, des inexactitudes et des carences dans la mise &agrave; jour, qu&rsquo;elles soient de son fait ou du fait des tiers partenaires qui lui fournissent ces informations.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Toutes les informations indiqu&eacute;es sur le site https://muslim-connect.fr sont donn&eacute;es &agrave; titre indicatif, et sont susceptibles d&rsquo;&eacute;voluer. Par ailleurs, les renseignements figurant sur le site https://muslim-connect.frne sont pas exhaustifs. Ils sont donn&eacute;s sous r&eacute;serve de modifications ayant &eacute;t&eacute; apport&eacute;es depuis leur mise en ligne.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;4. Limitations contractuelles sur les donn&eacute;es techniques.&amp;lt;br&amp;gt;Le site utilise la technologie JavaScript. Le site Internet ne pourra &ecirc;tre tenu responsable de dommages mat&eacute;riels li&eacute;s &agrave; l&rsquo;utilisation du site. De plus, l&rsquo;utilisateur du site s&rsquo;engage &agrave; acc&eacute;der au site en utilisant un mat&eacute;riel r&eacute;cent, ne contenant pas de virus et avec un navigateur de derni&egrave;re g&eacute;n&eacute;ration mis-&agrave;-jour Le site https://muslim-connect.fr est h&eacute;berg&eacute; chez un prestataire sur le territoire de l&rsquo;Union Europ&eacute;enne conform&eacute;ment aux dispositions du R&egrave;glement G&eacute;n&eacute;ral sur la Protection des Donn&eacute;es (RGPD : n&deg; 2016-679)&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;L&rsquo;objectif est d&rsquo;apporter une prestation qui assure le meilleur taux d&rsquo;accessibilit&eacute;. L&rsquo;h&eacute;bergeur assure la continuit&eacute; de son service 24 Heures sur 24, tous les jours de l&rsquo;ann&eacute;e. Il se r&eacute;serve n&eacute;anmoins la possibilit&eacute; d&rsquo;interrompre le service d&rsquo;h&eacute;bergement pour les dur&eacute;es les plus courtes possibles notamment &agrave; des fins de maintenance, d&rsquo;am&eacute;lioration de ses infrastructures, de d&eacute;faillance de ses infrastructures ou si les Prestations et Services g&eacute;n&egrave;rent un trafic r&eacute;put&eacute; anormal.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;https://muslim-connect.fr et l&rsquo;h&eacute;bergeur ne pourront &ecirc;tre tenus responsables en cas de dysfonctionnement du r&eacute;seau Internet, des lignes t&eacute;l&eacute;phoniques ou du mat&eacute;riel informatique et de t&eacute;l&eacute;phonie li&eacute; notamment &agrave; l&rsquo;encombrement du r&eacute;seau emp&ecirc;chant l&rsquo;acc&egrave;s au serveur.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;5. Propri&eacute;t&eacute; intellectuelle et contrefa&ccedil;ons.&amp;lt;br&amp;gt;https://muslim-connect.fr est propri&eacute;taire des droits de propri&eacute;t&eacute; intellectuelle et d&eacute;tient les droits d&rsquo;usage sur tous les &eacute;l&eacute;ments accessibles sur le site internet, notamment les textes, images, graphismes, logos, vid&eacute;os, ic&ocirc;nes et sons. Toute reproduction, repr&eacute;sentation, modification, publication, adaptation de tout ou partie des &eacute;l&eacute;ments du site, quel que soit le moyen ou le proc&eacute;d&eacute; utilis&eacute;, est interdite, sauf autorisation &eacute;crite pr&eacute;alable de : https://muslim-connect.fr.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Toute exploitation non autoris&eacute;e du site ou de l&rsquo;un quelconque des &eacute;l&eacute;ments qu&rsquo;il contient sera consid&eacute;r&eacute;e comme constitutive d&rsquo;une contrefa&ccedil;on et poursuivie conform&eacute;ment aux dispositions des articles L.335-2 et suivants du Code de Propri&eacute;t&eacute; Intellectuelle.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;6. Limitations de responsabilit&eacute;.&amp;lt;br&amp;gt;https://muslim-connect.fr agit en tant qu&rsquo;&eacute;diteur du site. https://muslim-connect.fr &amp;amp;nbsp;est responsable de la qualit&eacute; et de la v&eacute;racit&eacute; du Contenu qu&rsquo;il publie.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;https://muslim-connect.fr ne pourra &ecirc;tre tenu responsable des dommages directs et indirects caus&eacute;s au mat&eacute;riel de l&rsquo;utilisateur, lors de l&rsquo;acc&egrave;s au site internet https://muslim-connect.fr &amp;amp;nbsp;et r&eacute;sultant soit de l&rsquo;utilisation d&rsquo;un mat&eacute;riel ne r&eacute;pondant pas aux sp&eacute;cifications indiqu&eacute;es au point 4, soit de l&rsquo;apparition d&rsquo;un bug ou d&rsquo;une incompatibilit&eacute;.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;https://muslim-connect.fr ne pourra &eacute;galement &ecirc;tre tenu responsable des dommages indirects (tels par exemple qu&rsquo;une perte de march&eacute; ou perte d&rsquo;une chance) cons&eacute;cutifs &agrave; l&rsquo;utilisation du site https://muslim-connect.fr. Des espaces interactifs (possibilit&eacute; de poser des questions dans l&rsquo;espace contact) sont &agrave; la disposition des utilisateurs. https://muslim-connect.fr se r&eacute;serve le droit de supprimer, sans mise en demeure pr&eacute;alable, tout contenu d&eacute;pos&eacute; dans cet espace qui contreviendrait &agrave; la l&eacute;gislation applicable en France, en particulier aux dispositions relatives &agrave; la protection des donn&eacute;es. Le cas &eacute;ch&eacute;ant, https://muslim-connect.fr se r&eacute;serve &eacute;galement la possibilit&eacute; de mettre en cause la responsabilit&eacute; civile et/ou p&eacute;nale de l&rsquo;utilisateur, notamment en cas de message &agrave; caract&egrave;re raciste, injurieux, diffamant, ou pornographique, quel que soit le support utilis&eacute; (texte, photographie &hellip;).&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;7. Gestion des donn&eacute;es personnelles.&amp;lt;br&amp;gt;Le Client est inform&eacute; des r&eacute;glementations concernant la communication marketing, la loi du 21 Juin 2014 pour la confiance dans l&rsquo;Economie Num&eacute;rique, la Loi Informatique et Libert&eacute; du 06 Ao&ucirc;t 2004 ainsi que du R&egrave;glement G&eacute;n&eacute;ral sur la Protection des Donn&eacute;es (RGPD : n&deg; 2016-679).&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;7.1 Responsables de la collecte des donn&eacute;es personnelles&amp;lt;br&amp;gt;Pour les Donn&eacute;es Personnelles collect&eacute;es dans le cadre de la cr&eacute;ation du compte personnel de l&rsquo;Utilisateur et de sa navigation sur le Site, le responsable du traitement des Donn&eacute;es Personnelles est : MUSLIM CONNECT. https://muslim-connect.fr &amp;amp;nbsp;est repr&eacute;sent&eacute; par Repres, son repr&eacute;sentant l&eacute;gal&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;En tant que responsable du traitement des donn&eacute;es qu&rsquo;il collecte, https://muslim-connect.fr s&rsquo;engage &agrave; respecter le cadre des dispositions l&eacute;gales en vigueur. Il lui appartient notamment au Client d&rsquo;&eacute;tablir les finalit&eacute;s de ses traitements de donn&eacute;es, de fournir &agrave; ses prospects et clients, &agrave; partir de la collecte de leurs consentements, une information compl&egrave;te sur le traitement de leurs donn&eacute;es personnelles et de maintenir un registre des traitements conforme &agrave; la r&eacute;alit&eacute;. Chaque fois que https://muslim-connect.fr traite des Donn&eacute;es Personnelles, https://muslim-connect.fr prend toutes les mesures raisonnables pour s&rsquo;assurer de l&rsquo;exactitude et de la pertinence des Donn&eacute;es Personnelles au regard des finalit&eacute;s pour lesquelles https://muslim-connect.fr les traite.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;7.2 Finalit&eacute; des donn&eacute;es collect&eacute;es&amp;amp;nbsp;&amp;lt;br&amp;gt;https://muslim-connect.fr est susceptible de traiter tout ou partie des donn&eacute;es :&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;pour permettre la navigation sur le Site et la gestion et la tra&ccedil;abilit&eacute; des prestations et services command&eacute;s par l&rsquo;utilisateur : donn&eacute;es de connexion et d&rsquo;utilisation du Site, facturation, historique des commandes, etc.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;pour pr&eacute;venir et lutter contre la fraude informatique (spamming, hacking&hellip;) : mat&eacute;riel informatique utilis&eacute; pour la navigation, l&rsquo;adresse IP, le mot de passe (hash&eacute;)&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;pour am&eacute;liorer la navigation sur le Site : donn&eacute;es de connexion et d&rsquo;utilisation&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;pour mener des enqu&ecirc;tes de satisfaction facultatives sur https://muslim-connect.fr : adresse email&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;pour mener des campagnes de communication (sms, mail) : num&eacute;ro de t&eacute;l&eacute;phone, adresse email&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;https://muslim-connect.fr ne commercialise pas vos donn&eacute;es personnelles qui sont donc uniquement utilis&eacute;es par n&eacute;cessit&eacute; ou &agrave; des fins statistiques et d&rsquo;analyses.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;7.3 Droit d&rsquo;acc&egrave;s, de rectification et d&rsquo;opposition&amp;lt;br&amp;gt;Conform&eacute;ment &agrave; la r&eacute;glementation europ&eacute;enne en vigueur, les Utilisateurs de https://muslim-connect.fr disposent des droits suivants :&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;droit d&amp;#039;acc&egrave;s (article 15 RGPD) et de rectification (article 16 RGPD), de mise &agrave; jour, de compl&eacute;tude des donn&eacute;es des Utilisateurs droit de verrouillage ou d&rsquo;effacement des donn&eacute;es des Utilisateurs &agrave; caract&egrave;re personnel (article 17 du RGPD), lorsqu&rsquo;elles sont inexactes, incompl&egrave;tes, &eacute;quivoques, p&eacute;rim&eacute;es, ou dont la collecte, l&amp;#039;utilisation, la communication ou la conservation est interdite&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;droit de retirer &agrave; tout moment un consentement (article 13-2c RGPD)&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;droit &agrave; la limitation du traitement des donn&eacute;es des Utilisateurs (article 18 RGPD)&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;droit d&rsquo;opposition au traitement des donn&eacute;es des Utilisateurs (article 21 RGPD)&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;droit &agrave; la portabilit&eacute; des donn&eacute;es que les Utilisateurs auront fournies, lorsque ces donn&eacute;es font l&rsquo;objet de traitements automatis&eacute;s fond&eacute;s sur leur consentement ou sur un contrat (article 20 RGPD)&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;droit de d&eacute;finir le sort des donn&eacute;es des Utilisateurs apr&egrave;s leur mort et de choisir &agrave; qui https://muslim-connect.fr devra communiquer (ou non) ses donn&eacute;es &agrave; un tiers qu&rsquo;ils aura pr&eacute;alablement d&eacute;sign&eacute;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;D&egrave;s que https://muslim-connect.fr a connaissance du d&eacute;c&egrave;s d&rsquo;un Utilisateur et &agrave; d&eacute;faut d&rsquo;instructions de sa part, https://muslim-connect.fr s&rsquo;engage &agrave; d&eacute;truire ses donn&eacute;es, sauf si leur conservation s&rsquo;av&egrave;re n&eacute;cessaire &agrave; des fins probatoires ou pour r&eacute;pondre &agrave; une obligation l&eacute;gale.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Si l&rsquo;Utilisateur souhaite savoir comment https://muslim-connect.fr utilise ses Donn&eacute;es Personnelles, demander &agrave; les rectifier ou s&rsquo;oppose &agrave; leur traitement, l&rsquo;Utilisateur peut contacter https://muslim-connect.fr par &eacute;crit &agrave; l&rsquo;adresse suivante :&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;MUSLIM CONNECT&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;1 rue des peupliers&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;94230 Cachan&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Dans ce cas, l&rsquo;Utilisateur doit indiquer les Donn&eacute;es Personnelles qu&rsquo;il souhaiterait que https://muslim-connect.fr &amp;amp;nbsp;corrige, mette &agrave; jour ou supprime, en s&rsquo;identifiant pr&eacute;cis&eacute;ment avec une copie d&rsquo;une pi&egrave;ce d&rsquo;identit&eacute; (carte d&rsquo;identit&eacute; ou passeport).&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Les demandes de suppression de Donn&eacute;es Personnelles seront soumises aux obligations qui sont impos&eacute;es &agrave; https://muslim-connect.fr par la loi, notamment en mati&egrave;re de conservation ou d&rsquo;archivage des documents. Enfin, les Utilisateurs de https://muslim-connect.fr peuvent d&eacute;poser une r&eacute;clamation aupr&egrave;s des autorit&eacute;s de contr&ocirc;le, et notamment de la CNIL (https://www.cnil.fr/fr/plaintes).&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;7.4 Non-communication des donn&eacute;es personnelles&amp;lt;br&amp;gt;https://muslim-connect.fr &amp;amp;nbsp;s&rsquo;interdit de traiter, h&eacute;berger ou transf&eacute;rer les Informations collect&eacute;es sur ses Clients vers un pays situ&eacute; en dehors de l&rsquo;Union europ&eacute;enne ou reconnu comme &laquo; non ad&eacute;quat &raquo; par la Commission europ&eacute;enne sans en informer pr&eacute;alablement le client. Pour autant, https://muslim-connect.fr &amp;amp;nbsp;reste libre du choix de ses sous-traitants techniques et commerciaux &agrave; la condition qu&rsquo;il pr&eacute;sentent les garanties suffisantes au regard des exigences du R&egrave;glement G&eacute;n&eacute;ral sur la Protection des Donn&eacute;es (RGPD : n&deg; 2016-679).&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;https://muslim-connect.fr &amp;amp;nbsp;s&rsquo;engage &agrave; prendre toutes les pr&eacute;cautions n&eacute;cessaires afin de pr&eacute;server la s&eacute;curit&eacute; des Informations et notamment qu&rsquo;elles ne soient pas communiqu&eacute;es &agrave; des personnes non autoris&eacute;es. Cependant, si un incident impactant l&rsquo;int&eacute;grit&eacute; ou la confidentialit&eacute; des Informations du Client est port&eacute;e &agrave; la connaissance de https://muslim-connect.fr &amp;amp;nbsp;celle-ci devra dans les meilleurs d&eacute;lais informer le Client et lui communiquer les mesures de corrections prises. Par ailleurs https://muslim-connect.fr &amp;amp;nbsp;ne collecte aucune &laquo; donn&eacute;es sensibles &raquo;.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Les Donn&eacute;es Personnelles de l&rsquo;Utilisateur peuvent &ecirc;tre trait&eacute;es par des filiales de https://muslim-connect.fr et des sous-traitants (prestataires de services), exclusivement afin de r&eacute;aliser les finalit&eacute;s de la pr&eacute;sente politique.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Dans la limite de leurs attributions respectives et pour les finalit&eacute;s rappel&eacute;es ci-dessus, les principales personnes susceptibles d&rsquo;avoir acc&egrave;s aux donn&eacute;es des Utilisateurs de https://muslim-connect.fr &amp;amp;nbsp;sont principalement les agents de notre service client.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Utitlisation de Google calendar&amp;lt;br&amp;gt;Afin de synchronniser votre calendrier iDO au calendrier Google, muslim-connect.fr a besoin d&amp;#039;acfficher l&amp;#039;adresse e-mail principale associ&eacute;e &agrave; votre compte Google, acc&eacute;der que vos informations personnelles, y compris celles que vous avez choisi de rendre disponibles publiquement et votre identifiant OpenId &amp;amp;nbsp;Google afin de pouvoir envoyer et mettre &agrave; jour des &eacute;v&eacute;nements dans votre calendrier Google.&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;L&amp;#039;utilisation et transfert de donn&eacute;es provenants de Google APIs de idosport.app &agrave; d&amp;#039;autres applications restpecte les Google API Services User Data Policy cela inclue les &amp;#039;Limited Use requirements&amp;#039;.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;8. Notification d&rsquo;incident&amp;lt;br&amp;gt;Quels que soient les efforts fournis, aucune m&eacute;thode de transmission sur Internet et aucune m&eacute;thode de stockage &eacute;lectronique n&amp;#039;est compl&egrave;tement s&ucirc;re. Nous ne pouvons en cons&eacute;quence pas garantir une s&eacute;curit&eacute; absolue. Si nous prenions connaissance d&amp;#039;une br&egrave;che de la s&eacute;curit&eacute;, nous avertirions les utilisateurs concern&eacute;s afin qu&amp;#039;ils puissent prendre les mesures appropri&eacute;es. Nos proc&eacute;dures de notification d&rsquo;incident tiennent compte de nos obligations l&eacute;gales, qu&amp;#039;elles se situent au niveau national ou europ&eacute;en. Nous nous engageons &agrave; informer pleinement nos clients de toutes les questions relevant de la s&eacute;curit&eacute; de leur compte et &agrave; leur fournir toutes les informations n&eacute;cessaires pour les aider &agrave; respecter leurs propres obligations r&eacute;glementaires en mati&egrave;re de reporting.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Aucune information personnelle de l&amp;#039;utilisateur du site https://muslim-connect.fr &amp;amp;nbsp;n&amp;#039;est publi&eacute;e &agrave; l&amp;#039;insu de l&amp;#039;utilisateur, &eacute;chang&eacute;e, transf&eacute;r&eacute;e, c&eacute;d&eacute;e ou vendue sur un support quelconque &agrave; des tiers. Seule l&amp;#039;hypoth&egrave;se du rachat de https://muslim-connect.fr et de ses droits permettrait la transmission des dites informations &agrave; l&amp;#039;&eacute;ventuel acqu&eacute;reur qui serait &agrave; son tour tenu de la m&ecirc;me obligation de conservation et de modification des donn&eacute;es vis &agrave; vis de l&amp;#039;utilisateur du site https://muslim-connect.fr.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;S&eacute;curit&eacute;&amp;lt;br&amp;gt;Pour assurer la s&eacute;curit&eacute; et la confidentialit&eacute; des Donn&eacute;es Personnelles et des Donn&eacute;es Personnelles de Sant&eacute;, https://muslim-connect.fr utilise des r&eacute;seaux prot&eacute;g&eacute;s par des dispositifs standards tels que par pare-feu, la pseudonymisation, l&rsquo;encryption et mot de passe.&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Lors du traitement des Donn&eacute;es Personnelles, https://muslim-connect.fr prend toutes les mesures raisonnables visant &agrave; les prot&eacute;ger contre toute perte, utilisation d&eacute;tourn&eacute;e, acc&egrave;s non autoris&eacute;, divulgation, alt&eacute;ration ou destruction.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;9. Liens hypertextes &laquo; cookies &raquo; et balises (&ldquo;tags&rdquo;) internet&amp;lt;br&amp;gt;Le site https://muslim-connect.fr contient un certain nombre de liens hypertextes vers d&rsquo;autres sites, mis en place avec l&rsquo;autorisation de https://muslim-connect.fr. Cependant, https://muslim-connect.fr n&rsquo;a pas la possibilit&eacute; de v&eacute;rifier le contenu des sites ainsi visit&eacute;s, et n&rsquo;assumera en cons&eacute;quence aucune responsabilit&eacute; de ce fait.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Sauf si vous d&eacute;cidez de d&eacute;sactiver les cookies, vous acceptez que le site puisse les utiliser. Vous pouvez &agrave; tout moment d&eacute;sactiver ces cookies et ce gratuitement &agrave; partir des possibilit&eacute;s de d&eacute;sactivation qui vous sont offertes et rappel&eacute;es ci-apr&egrave;s, sachant que cela peut r&eacute;duire ou emp&ecirc;cher l&rsquo;accessibilit&eacute; &agrave; tout ou partie des Services propos&eacute;s par le site.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;9.1. &laquo; COOKIES &raquo;&amp;lt;br&amp;gt;Un &laquo; cookie &raquo; est un petit fichier d&rsquo;information envoy&eacute; sur le navigateur de l&rsquo;Utilisateur et enregistr&eacute; au sein du terminal de l&rsquo;Utilisateur (ex : ordinateur, smartphone), (ci-apr&egrave;s &laquo; Cookies &raquo;). Ce fichier comprend des informations telles que le nom de domaine de l&rsquo;Utilisateur, le fournisseur d&rsquo;acc&egrave;s Internet de l&rsquo;Utilisateur, le syst&egrave;me d&rsquo;exploitation de l&rsquo;Utilisateur, ainsi que la date et l&rsquo;heure d&rsquo;acc&egrave;s. Les Cookies ne risquent en aucun cas d&rsquo;endommager le terminal de l&rsquo;Utilisateur.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;https://muslim-connect.fr est susceptible de traiter les informations de l&rsquo;Utilisateur concernant sa visite du Site, telles que les pages consult&eacute;es, les recherches effectu&eacute;es. Ces informations permettent &agrave; https://muslim-connect.fr d&rsquo;am&eacute;liorer le contenu du Site, de la navigation de l&rsquo;Utilisateur.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Les Cookies facilitant la navigation et/ou la fourniture des services propos&eacute;s par le Site, l&rsquo;Utilisateur peut configurer son navigateur pour qu&rsquo;il lui permette de d&eacute;cider s&rsquo;il souhaite ou non les accepter de mani&egrave;re &agrave; ce que des Cookies soient enregistr&eacute;s dans le terminal ou, au contraire, qu&rsquo;ils soient rejet&eacute;s, soit syst&eacute;matiquement, soit selon leur &eacute;metteur. L&rsquo;Utilisateur peut &eacute;galement configurer son logiciel de navigation de mani&egrave;re &agrave; ce que l&rsquo;acceptation ou le refus des Cookies lui soient propos&eacute;s ponctuellement, avant qu&rsquo;un Cookie soit susceptible d&rsquo;&ecirc;tre enregistr&eacute; dans son terminal. https://muslim-connect.fr informe l&rsquo;Utilisateur que, dans ce cas, il se peut que les fonctionnalit&eacute;s de son logiciel de navigation ne soient pas toutes disponibles.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Si l&rsquo;Utilisateur refuse l&rsquo;enregistrement de Cookies dans son terminal ou son navigateur, ou si l&rsquo;Utilisateur supprime ceux qui y sont enregistr&eacute;s, l&rsquo;Utilisateur est inform&eacute; que sa navigation et son exp&eacute;rience sur le Site peuvent &ecirc;tre limit&eacute;es. Cela pourrait &eacute;galement &ecirc;tre le cas lorsque https://muslim-connect.fr ou l&rsquo;un de ses prestataires ne peut pas reconna&icirc;tre, &agrave; des fins de compatibilit&eacute; technique, le type de navigateur utilis&eacute; par le terminal, les param&egrave;tres de langue et d&rsquo;affichage ou le pays depuis lequel le terminal semble connect&eacute; &agrave; Internet.&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Le cas &eacute;ch&eacute;ant, https://muslim-connect.fr d&eacute;cline toute responsabilit&eacute; pour les cons&eacute;quences li&eacute;es au fonctionnement d&eacute;grad&eacute; du Site et des services &eacute;ventuellement propos&eacute;s par https://muslim-connect.fr, r&eacute;sultant (i) du refus de Cookies par l&rsquo;Utilisateur (ii) de l&rsquo;impossibilit&eacute; pour https://muslim-connect.fr d&rsquo;enregistrer ou de consulter les Cookies n&eacute;cessaires &agrave; leur fonctionnement du fait du choix de l&rsquo;Utilisateur. Pour la gestion des Cookies et des choix de l&rsquo;Utilisateur, la configuration de chaque navigateur est diff&eacute;rente. Elle est d&eacute;crite dans le menu d&rsquo;aide du navigateur, qui permettra de savoir de quelle mani&egrave;re l&rsquo;Utilisateur peut modifier ses souhaits en mati&egrave;re de Cookies.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&Agrave; tout moment, l&rsquo;Utilisateur peut faire le choix d&rsquo;exprimer et de modifier ses souhaits en mati&egrave;re de Cookies. https://muslim-connect.fr pourra en outre faire appel aux services de prestataires externes pour l&rsquo;aider &agrave; recueillir et traiter les informations d&eacute;crites dans cette section.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Enfin, en cliquant sur les ic&ocirc;nes d&eacute;di&eacute;es aux r&eacute;seaux sociaux Twitter, Facebook, Instagram, Linkedin et Google Plus figurant sur le Site de https://muslim-connect.fr ou dans son application mobile et si l&rsquo;Utilisateur a accept&eacute; le d&eacute;p&ocirc;t de cookies en poursuivant sa navigation sur le Site Internet ou l&rsquo;application mobile de https://muslim-connect.fr , Facebook, Instagram, Linkedin et Google Plus peuvent &eacute;galement d&eacute;poser des cookies sur vos terminaux (ordinateur, tablette, t&eacute;l&eacute;phone portable).&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Ces types de cookies ne sont d&eacute;pos&eacute;s sur vos terminaux qu&rsquo;&agrave; condition que vous y consentiez, en continuant votre navigation sur le Site Internet ou l&rsquo;application mobile de https://muslim-connect.fr. &Agrave; tout moment, l&rsquo;Utilisateur peut n&eacute;anmoins revenir sur son consentement &agrave; ce que https://muslim-connect.fr d&eacute;pose ce type de cookies.&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Article 9.2. BALISES (&ldquo;TAGS&rdquo;) INTERNET&amp;lt;br&amp;gt;https://muslim-connect.fr peut employer occasionnellement des balises Internet (&eacute;galement appel&eacute;es &laquo; tags &raquo;, ou balises d&rsquo;action, GIF &agrave; un pixel, GIF transparents, GIF invisibles et GIF un &agrave; un) et les d&eacute;ployer par l&rsquo;interm&eacute;diaire d&rsquo;un partenaire sp&eacute;cialiste d&rsquo;analyses Web susceptible de se trouver (et donc de stocker les informations correspondantes, y compris l&rsquo;adresse IP de l&rsquo;Utilisateur) dans un pays &eacute;tranger.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Ces balises sont plac&eacute;es &agrave; la fois dans les publicit&eacute;s en ligne permettant aux internautes d&rsquo;acc&eacute;der au Site, et sur les diff&eacute;rentes pages de celui-ci. &amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Cette technologie permet &agrave; https://muslim-connect.fr d&rsquo;&eacute;valuer les r&eacute;ponses des visiteurs face au Site et l&rsquo;efficacit&eacute; de ses actions (par exemple, le nombre de fois o&ugrave; une page est ouverte et les informations consult&eacute;es), ainsi que l&rsquo;utilisation de ce Site par l&rsquo;Utilisateur.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;Le prestataire externe pourra &eacute;ventuellement recueillir des informations sur les visiteurs du Site et d&rsquo;autres sites Internet gr&acirc;ce &agrave; ces balises, constituer des rapports sur l&rsquo;activit&eacute; du Site &agrave; l&rsquo;attention de https://muslim-connect.fr, et fournir d&rsquo;autres services relatifs &agrave; l&rsquo;utilisation de celui-ci et d&rsquo;Internet.&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;br&amp;gt;&amp;lt;br&amp;gt;10. Droit applicable et attribution de juridiction.&amp;lt;br&amp;gt;Tout litige en relation avec l&rsquo;utilisation du site https://muslim-connect.fr est soumis au droit fran&ccedil;ais. En dehors des cas o&ugrave; la loi ne le permet pas, il est fait attribution exclusive de juridiction aux tribunaux comp&eacute;tents de Draguignan.&amp;lt;/p&amp;gt;', 'Mentions légales'),
(3, 'qui-sommes-nous', '&amp;lt;p&amp;gt;Salam aleykoum wa rahmatullah wa barakatou,&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;Que la paix, la mis&eacute;ricorde et la b&eacute;n&eacute;diction d&amp;#039;Allah soient sur vous.&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;L&rsquo;id&eacute;e de Muslim Connect a germ&eacute; dans les profondeurs de notre peine, suite &agrave; la perte&amp;lt;br&amp;gt;tragique d&amp;#039;un &ecirc;tre cher. Avant m&ecirc;me que le corps ne soit mis en terre, une question&amp;lt;br&amp;gt;br&ucirc;lante nous a assaillis : quelles &eacute;taient ses dettes ? Comment r&eacute;pondre &agrave; cette&amp;lt;br&amp;gt;question sans testament ? &Agrave; cet instant, nous avons pris conscience de l&rsquo;importance&amp;lt;br&amp;gt;vitale du testament, une pratique trop souvent d&eacute;laiss&eacute;e au sein de notre communaut&eacute;.&amp;lt;br&amp;gt;Comment savoir si le d&eacute;funt avait des dettes, &agrave; qui et de combien, surtout lorsque &laquo;&amp;lt;br&amp;gt;l&rsquo;&acirc;me du croyant est suspendue &agrave; sa dette jusqu&rsquo;&agrave; son r&egrave;glement &raquo; ? La mort frappe&amp;lt;br&amp;gt;sans pr&eacute;venir, et il est de notre devoir, en tant que musulmans, de nous y pr&eacute;parer en&amp;lt;br&amp;gt;tenant un testament. &laquo; Il n&amp;#039;appartient pas &agrave; un musulman qui a des choses &agrave;&amp;lt;br&amp;gt;recommander de passer deux nuits sans que son testament ne soit &agrave; ses c&ocirc;t&eacute;s. &raquo;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;Ainsi, nous avons choisi de donner naissance &agrave; l&rsquo;application Muslim Connect avec les&amp;lt;br&amp;gt;objectifs suivants :&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;Permettre aux musulmans de r&eacute;diger leur testament et de tenir un registre de leurs&amp;lt;br&amp;gt;comptes (dettes, emprunts), avec la possibilit&eacute; de partager ces informations avec les&amp;lt;br&amp;gt;membres de leur famille choisis.&amp;lt;br&amp;gt;Laisser des recommandations, telles que l&rsquo;endroit o&ugrave; vous souhaitez &ecirc;tre enterr&eacute; et la&amp;lt;br&amp;gt;destination de vos biens, afin de pr&eacute;venir des conflits futurs.&amp;lt;br&amp;gt;Accompagner les familles musulmanes en deuil en facilitant la mise en relation avec les&amp;lt;br&amp;gt;pompes fun&egrave;bres, en aidant avec les d&eacute;marches administratives, en fournissant des&amp;lt;br&amp;gt;douas, en apprenant la salat Janaza, et en r&eacute;pondant &agrave; toutes les questions li&eacute;es au&amp;lt;br&amp;gt;deuil.&amp;lt;br&amp;gt;Muslim Connect aspire &agrave; offrir un soutien complet et adapt&eacute; aux besoins des familles&amp;lt;br&amp;gt;endeuill&eacute;es, tout en honorant les principes de notre foi.&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;De plus, Muslim Connect propose un service de cr&eacute;ation de cartes virtuelles&amp;lt;br&amp;gt;automatis&eacute;es, permettant d&rsquo;envoyer les douas appropri&eacute;es selon les circonstances&amp;lt;br&amp;gt;(annonce d&rsquo;un d&eacute;c&egrave;s, soutien, remerciements, annonce d&rsquo;une salat Janaza, etc.).&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;Muslim Connect vous permet &eacute;galement d&rsquo;offrir des sadaka jariya &agrave; vos d&eacute;funts ou &agrave;&amp;lt;br&amp;gt;vos malades gr&acirc;ce &agrave; nos partenaires certifi&eacute;s. Honorez vos proches d&eacute;funts ou maladesen r&eacute;alisant un umrah ou un hajj par procuration, ou en construisant un puits. Tout cela&amp;lt;br&amp;gt;est possible avec Muslim Connect, et ce, en seulement quelques clics.&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;Nous passons notre vie &agrave; pr&eacute;parer une existence &eacute;ph&eacute;m&egrave;re, n&eacute;gligeant souvent notre&amp;lt;br&amp;gt;&eacute;ternit&eacute;. &OElig;uvrez pour l&rsquo;Au-del&agrave; comme si vous deviez quitter ce monde demain, car en&amp;lt;br&amp;gt;v&eacute;rit&eacute;, nous sommes des voyageurs sur cette terre, en route vers notre demeure&amp;lt;br&amp;gt;&eacute;ternelle.&amp;lt;/p&amp;gt;', 'Qui sommes nous');

-- --------------------------------------------------------

--
-- Structure de la table `pardon`
--

CREATE TABLE `pardon` (
  `id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pardon`
--

INSERT INTO `pardon` (`id`, `created_by_id`, `firstname`, `lastname`, `content`, `created_at`) VALUES
(1, 1, 'tipil', 'yann', 'Ffgg rttz. Zhhehvvzvzyhhhe yshhhhe hdhhhhd yyehhhd.\nJjdjj yshhh zyyyd sujhd ushhhr\nUdjhhhhf yshhhhe dhhhhhe sujhd duh. ', '2024-01-10 09:38:19'),
(2, 2, 'Nicole', 'Bedouet', 'Without additional information, we are unfortunately not sure how to resolve this issue. We are therefore reluctantly going to close this bug for now.\r\nIf you find this problem please file a new issue with the same description, what happens, logs and the output of \'=\'. All system setups can be slightly different so it\'s always better to open new issues and reference the related ones.\r\nThanks for your contribution.', '2024-01-10 10:20:27');

-- --------------------------------------------------------

--
-- Structure de la table `pardon_share`
--

CREATE TABLE `pardon_share` (
  `id` int NOT NULL,
  `pardon_id` int NOT NULL,
  `share_with_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pardon_share`
--

INSERT INTO `pardon_share` (`id`, `pardon_id`, `share_with_id`) VALUES
(1, 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `pompe`
--

CREATE TABLE `pompe` (
  `id` int NOT NULL,
  `location_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `online` tinyint(1) NOT NULL,
  `validated` tinyint(1) NOT NULL,
  `managed_by_id` int DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fullname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emailpro` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_prefix` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_urgence` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefix_urgence` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pompe`
--

INSERT INTO `pompe` (`id`, `location_id`, `name`, `created_at`, `description`, `online`, `validated`, `managed_by_id`, `phone`, `fullname`, `emailpro`, `phone_prefix`, `phone_urgence`, `prefix_urgence`) VALUES
(1, 13, 'Pompe 1', '2023-12-16 16:39:36', '&amp;lt;p&amp;gt;fdsfq&amp;lt;/p&amp;gt;', 1, 1, 6, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 14, 'pompe 2 gré clea', '2023-12-16 16:44:20', 'dsfdsqf ddfgg\n&agrave; la ligne', 1, 1, 1, '87905476', 'Return vui', 'gertru@hgc..gh', '+32', '666666666', '+501'),
(6, 18, 'ma pompe', '2023-12-17 16:27:58', 'Rffff\n &agrave; la ligne', 1, 1, 1, NULL, 'Pierre huber', NULL, NULL, NULL, NULL),
(7, 29, 'Pompe Gap', '2024-02-16 11:59:21', 'Gap', 1, 1, 1, NULL, 'NIcolas feron', NULL, NULL, NULL, NULL),
(8, 43, 'efgg', '2024-04-24 17:48:49', 'Sde', 0, 0, 1, '53467ddd', 'Borisrrr Bruyère', NULL, NULL, NULL, NULL),
(20, 57, 'rdfg', '2024-05-23 16:39:31', '', 0, 1, 1, '77789999999', 'efgg', 'test@test.fr ', '+33', '55555555555', '+33');

-- --------------------------------------------------------

--
-- Structure de la table `pompe_notification`
--

CREATE TABLE `pompe_notification` (
  `id` int NOT NULL,
  `pompe_id` int NOT NULL,
  `dece_id` int NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `accepted` tinyint(1) NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pompe_notification`
--

INSERT INTO `pompe_notification` (`id`, `pompe_id`, `dece_id`, `created_at`, `accepted`, `status`) VALUES
(1, 2, 8, '2024-02-16 14:00:10', 1, 'accepted'),
(2, 1, 8, '2024-02-16 14:00:10', 0, 'rejected'),
(3, 6, 8, '2024-02-16 14:00:10', 0, 'rejected'),
(4, 7, 8, '2024-02-16 14:00:10', 0, 'rejected'),
(5, 7, 16, '2024-04-11 11:01:01', 1, 'accepted'),
(6, 2, 15, '2024-04-24 18:59:40', 0, 'accepted'),
(7, 7, 15, '2024-04-24 18:59:40', 0, 'rejected'),
(8, 6, 15, '2024-04-24 18:59:40', 0, 'canDemand'),
(10, 7, 17, '2024-05-07 19:30:58', 0, 'canDemand'),
(11, 7, 18, '2024-05-08 09:32:29', 0, 'canDemand');

-- --------------------------------------------------------

--
-- Structure de la table `pray_notification`
--

CREATE TABLE `pray_notification` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `prays` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notif_added` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pray_notification`
--

INSERT INTO `pray_notification` (`id`, `user_id`, `prays`, `notif_added`) VALUES
(4, 15, '[\"fajr\",\"dohr\"]', 0),
(7, 16, '[\"fajr\",\"maghreb\"]', 0);

-- --------------------------------------------------------

--
-- Structure de la table `refresh_tokens`
--

CREATE TABLE `refresh_tokens` (
  `id` int NOT NULL,
  `refresh_token` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valid` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `refresh_token`, `username`, `valid`) VALUES
(25, '574fcfb49c226edd998ad3814b1ef28aab3c00617a61d3c2673dea8130a512ab79ecfbed7f853a3097475a30c956620362fc447368072d948c994e388990925f', 'user2@test.fr', '2024-08-15 03:14:47'),
(34, '2caf6c05a43c5f5c4b5ae3642f4ef6fcdb44b82325f71cd518d97a189997f33452f68b259f79d5c9638f33e726a1671a8943dc46ca423633fe0e46e63a8973b1', 'invitemslconnect2@gmail.fr', '2024-09-22 02:54:51'),
(38, '87042eddbd85e4a8f26628877bc164d93baaf1345306d71103d59b4c6eb1939910330d0c634a0265c4e29c648684f3fa810df44ac34ea0caa03d0bed495a469e', 'invitemslconnect2@gmail.fr', '2024-10-13 01:15:31'),
(41, '2a230491cb34ceffdab7f587d940d58a6320ae9151fa13120aead0dde7ce726c9dc39a898c673ebcefea23f329e3ef112706b1b4d57231af0f233f9169489279', 'invitemslconnect2@gmail.fr', '2024-10-15 02:03:54'),
(53, '3ef6bf8649001214b4a8a223891a31b051f7999b4fe400612bdff4abcf28a835abb82e654b8fb78456975b95d3b31d53884b20b432f6134b62c1e5edd196fbca', 'test@test.fr', '2024-11-08 00:22:22'),
(54, 'ce4f1bf80e104c0a6ebc9e2854978c4d04b1332ee663358a0b888fa21f51852ea167392421e00a333273358d5cd4c772b002fe179e43aed7cb8b98c49559f666', 'test@test.fr', '2024-11-08 00:26:22'),
(55, '099e1f9fb6977da05f8d38fbb5daa2289add6c2a7347e2c9cab5d908c98c34c77a68cb5d1662767a1ac0e1695d1d28a0035bfe33aa0068611e7dc349b8ca3d74', 'test@test.fr', '2024-11-08 06:06:03'),
(56, 'd38540cad15587139d2fc0cd0c3de4d3e65ce1cffc97c84d93db044c65542b2d79bc7ad18575d50bea1c0fdc8a38ea25946342c834d954bb24787a8f9bb01f5d', 'test@test.fr', '2024-11-11 09:37:52'),
(57, 'c1c2e510164f7fba980663111ffcd155d519e7b94933f6a487cc83682cac9d2c77248b6352ebf08d2d6efde6f0d9eb9a763ac1be9f847959aded481076de0ca0', 'test@test.fr', '2024-11-13 02:26:36'),
(58, '74e0d95a8da26eb1d0f47cc9758168c17b1e78dccfc6f75ea479cd916a53134406fcdb4a30e0ae761ae26fde03b14747acd3d196ded09bd80ca5555ebf0bdbba', 'test@test.fr', '2024-11-15 02:43:14'),
(59, '249ee40ed9cfcb32c9b80566533817402dc9c9141701be8a5dbd0d3018c6c8a9038970d03d384bb52f1d15cfe12f5e9770e658f0768d1da75ada922bba661186', 'test@test.fr', '2024-11-15 06:32:51'),
(60, 'bab93b13d65791815b846595732b341c8cf0bb1228944162763eb7eb39847000c5c50ffa220091d49741eaacc589aec435e567f4e759a9937dffb9d99fa10ada', 'test@test.fr', '2024-11-20 06:31:23'),
(61, '98aa5f2ee05723333b85be104a104fc524bb2bd4640247a1b0382093ec6cff6bd49d0d675470c8c23098614cc64fd7586e1a5dd5df864905090f429ccf73ee2f', 'usegr2@test.fr', '2024-11-21 05:26:11'),
(62, 'ae2c8693a65773396138413f377778fca5d320de7c601ac89e7fc2349953824f5d9d9f6c42d75b340c44accf1ee54ea4e454a69d5ff4ef892e65c68d42d8ea95', 'test@test.fr', '2024-11-21 10:58:13'),
(63, '0ee5714878f4f68ae365c3da27250c23c85bd5b9fe85dd907ebffaa0c157432c13ce92819cef23dff01896dd6a0bab29e6e45d88b7a0645c3ddc931f4938e578', 'test@test.fr', '2024-11-22 06:45:05'),
(64, '929f9a7025de1f73a225a46ee87277845229afc5b3b3a6f2bd2d394afa1dfb354215233fef802260544c96463f8a82c7a4861ef2a5fbd87d4e70eca4142d1557', 'test@test.fr', '2024-11-22 07:53:53'),
(65, 'c41f72caa56a3dec2f301567c5e95b240586d5f33b752a410547c213fe18aa17a521285f9e8c13acb692288772fab03904718090d80789d5e8753c9a9e03484a', 'test@test.fr', '2024-11-22 11:02:29'),
(66, 'be1c8ac7952a1f4a97302f3504a1351d0ffe60c2b7379df1aa2ca87b5eb51fbf9623ad25eac360b895a1be0f1b1ea2ae93e5246f4bb6e6596a1c8f51ef72a941', 'test@test.fr', '2024-11-23 02:25:58'),
(67, 'eee194dc60a1c23d9b5367b0b5433db0772fa46ddb64679d5ef09aece4cb8a7b0036094c2d97cf7e499950268583acc0ef95d2993061301172fbb079ba832660', 'test@test.fr', '2025-07-01 03:46:33'),
(68, '3f88daddaf7b045c65ac8e289c0e321ed47576a0601faec36a42e7f275f994e908aa097331eff543c50fce0976c04415ad5884639c577e77fba82d36038c928f', 'test@test.fr', '2025-07-03 07:55:56'),
(69, 'fa354235e3b0e1ef3093333eeda29d3ce280549fcf65f1043f361314f82573f76b95aaaa4a95bf4f53531e33db653902a864431c00440f984515f835387519b0', 'test@test.fr', '2025-07-27 04:25:15'),
(70, 'f81a4ad812b9b9dc4b6c6954ff6b0e9a8cdf035401246f033b15fe1b539a5b5d56350ef03d4450c08a6392626842d89d2b28d4ec65da34770837df5d404f2dbb', 'test@test.fr', '2025-07-28 07:00:50'),
(71, '9088c699b514dca2f18bce47f791a8f413bc1c55a6507ae991d53840cc4652dc7fd3b7a313bc629a96b8cfbdeedbc420d2721cad619d845279e996f2ea666bea', 'test@test.fr', '2025-07-31 03:55:33'),
(72, '241963b3a68ae24efc3e3fd4be0356750511f3491dc2edfe657d57aef31d8d10122404012e32a435935211d43a91bd5571ace8572feedf58cb9be177e3471358', 'test@test.fr', '2025-08-11 06:01:28'),
(76, '793834eddd988bb6903ea0164af188062da2f98cd1ac9382e524ca95d65e1f0c99f65718d403ae0c5db5cf2b065f1285d9c476d53c238efaf0e1d59dd55c5dae', 'test@test.fr', '2025-08-12 02:34:54'),
(77, '03e3cd0b2f75bb3e4a424a84c5c82618b4a3825d3f6e3ca2b25bd9a760aac846d215316bc1eaafc3f507a683c3ce59191e7feee651afe7c3d3460a21011d5785', 'test@test.fr', '2025-08-15 06:42:10'),
(78, 'f9b39cc21aee1b1f12b499c0298883c2aa9a98d353ed2f83501224f8e28b5ba0214de8a19e0ecb3aa1d2f8478f7af7a1c8a498633e700ea54ee6c5b84d05b0c5', 'test@test.fr', '2025-08-19 10:39:00'),
(79, '6c7e7760864f5437991cbd55e403075b88b1ce090ba886abc75065b9be71b99b77fd7db015f3bf8dfbfe025b7ba04eeafe6ee9234d73bb9e9a744bb713f9ab2c', 'test@test.fr', '2025-08-27 21:11:17'),
(87, 'eb8fd144e9a4941f87bbd4da2d79a81f92af95ea59c874d79aa13ec48e907e584f59ef2b677d9dd7132cecc358f5b2efea572dbcf1dbed5655801d9fc2103aa0', 'test@test.fr', '2025-09-09 02:36:32'),
(98, '380cbf1dcb01274f5ae71476830b7d4adb90a2f3e6254df8eb88959a2d92a62e51f24ac9e60308c9dcaded9d7e996acb2ddec681f38167dc413229b5a5b92064', 'testhh@test.fr', '2025-09-09 23:32:44'),
(102, '6ab105e4c759d2b64acf52421202cfcafd99d69cfac5ba02999950d3cc9e907da7eb4fc731c8556f00ed3169b2368e98786e12fa9fa1186cdf57ecece98d6b48', 'test@test.fr', '2025-09-12 06:30:14'),
(103, '9c8db12ca1c7a62e932956d63593df6c9200aeb47afe7735fc5018295ee75f9abe7aed24729f3375f10eb7d211a5f9b9ae39b67eb0cbf4e52a745a65fb1812de', 'testhh@test.fr', '2025-09-12 06:03:06'),
(105, '75d6c4e985762ea4a4e0355fe619db90a7228a01f1acb5de4e0e1278765276cb6741c7b3227cab7a733907f7be32fd1ffea4288f0ee0d05b8892dbc3f9cc76d8', 'testhh@test.fr', '2025-09-12 20:41:13'),
(106, 'f83374f632884469f6bc0762db71673e2cf973a07b05fdead2bf4661ab239b6f6dc7a21916096505fbf25ec0e5428d094e117fcc76e8d6003cdf70dbd052857e', 'testhh@test.fr', '2025-09-13 05:24:30'),
(107, '58f366e81361b19a0aebb134f74d0f882a693823a0e2f5a177aec69019bb63263dba73e255de81c46a9c3b60330916d4cbf9b38acd2fd5f8c9ad3083ea37fc6f', 'test@test.fr', '2025-09-15 20:46:02'),
(112, 'f019f2ee858e33b58a9833d441bf1dc76a54e8ffbeccc820c7eb95fe87a638bae0dfff34810ac8a271482d0a705a7b62af3346c915e10bde479f0221158c4168', 'test@test.fr', '2025-09-17 05:51:40'),
(116, '455aebd95cff5ccefba5cb3e5fe38719a4c4979c85f66ff672003d683e34515e3cccf0179f1d63795bb8180a58382868e305544c9b92942399a0234857f4a402', 'test@test.fr', '2025-09-23 02:58:32'),
(118, '32b2e10f860ecb89592f20683a82e40b468e11509b5fb3b3cac1d90276bd43e864c14ac60e6a48224d0214bda2cbcd48d446cf0cc2d5957ac152d6c77e47e10c', 'testhh@test.fr', '2025-09-26 21:10:28'),
(119, 'ec96a8517625ed73c744a5897ab7c80177ee1d4be739778eb6dbbea011797eaa0a5be413dd0d309bebf5e98d2bbc452f6fd514bd180be20714f3fb8b130e1fd1', 'testhh@test.fr', '2025-09-23 03:56:00'),
(120, 'c242aa2e890121a015d70122311601220ad872c1070a3bfc15d3c0f9ddf7f63a88f12cdaf359391d8dc9ff5f104fc0fd002bd51122dd189331769fa463c6fa21', 'test@test.fr', '2025-09-23 04:11:36'),
(121, 'cd20c2d90cb18334436d50549dba5428dad546a91149d414b576a3299b3f61d70ce43a221314c07b8119af1ce5e76e202875bb0f07b02182f86ccd6dcdad27c1', 'test@test.fr', '2025-09-23 05:25:10'),
(122, '8ef3fc0d9f2e5be9fcb9ae8fa049dc7f4c6eb8781cf3294b62fc4e9a703be8eaf81e22d887803d01cc0d4c22ab4edf4703f96e9028f6fae65c771997e6176f27', 'test@test.fr', '2025-09-25 06:18:04'),
(125, '395b9bbc40ba2a9995ad15a347984038f95067eabda23fb81e0aeb4f77f4d7b736ba2557d3d9e4b1b0258f133a452a7378d13de8feaa49d34cf3f79bd1430196', 'test@test.fr', '2025-10-02 23:53:58'),
(126, 'd4586e6940e7cab0e9d6c7526953ba4c0c93d6d6df812e40922a7e3bd8e9bcf3b50d6744cfb6d17901ba7af9cc1e47bcbf7de67dff746d04d11b94f7999dc5d8', 'test@test.fr', '2025-10-03 00:42:27'),
(127, '6b103f24fd028905c1ab7d7ed7bc679e33c7480bbda45e713fc0a6eb6b44f3aba7f3d06f23d9a7ac76ddd2ec796d0025fe72afbd5fbcf5948e7d25975413149e', 'test@test.fr', '2025-11-22 21:14:31'),
(130, '1a4043431f628276d1f7655edbd9c0982e5c399aa1d4af09a31bc50ec1b86c27ad4ca361fa3a1f5873ae267df90ae74df083aa9a247afe5dbfa5ad3a6fe3f263', 'athlegggte1@test.com', '2025-11-30 21:54:50'),
(132, '238c6b970ea44e4379546647508a71d0c14d7058afadfd3dc77c798a79e1d610d5e5cfca7e7effad86a83a7a6bd1ccda817d5bddef149d28c1c55c6cf720f404', 'test@test.fr', '2025-11-30 22:33:49');

-- --------------------------------------------------------

--
-- Structure de la table `relation`
--

CREATE TABLE `relation` (
  `id` int NOT NULL,
  `user_source_id` int NOT NULL,
  `user_target_id` int NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `relation`
--

INSERT INTO `relation` (`id`, `user_source_id`, `user_target_id`, `type`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 1, NULL, 'active', '2023-12-13 14:25:30', '2023-12-13 14:25:30'),
(2, 2, 1, NULL, 'active', '2023-12-13 14:27:35', '2023-12-13 14:27:35'),
(3, 6, 4, NULL, 'active', '2024-03-23 11:05:13', '2024-03-23 11:05:13'),
(5, 1, 5, NULL, 'blocked_by_source', '2024-04-12 17:01:55', '2024-04-12 17:01:55'),
(7, 1, 4, NULL, 'active', '2025-02-12 12:48:08', '2025-02-12 12:48:08'),
(8, 1, 10, NULL, 'active', '2025-02-12 12:49:46', '2025-02-12 12:49:46'),
(20, 1, 16, NULL, 'active', '2025-04-03 08:56:29', '2025-04-03 08:56:29');

-- --------------------------------------------------------

--
-- Structure de la table `resetpassword`
--

CREATE TABLE `resetpassword` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expire_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `resetpassword`
--

INSERT INTO `resetpassword` (`id`, `user_id`, `code`, `created_at`, `expire_at`) VALUES
(13, 10, '173023', '2025-06-01 07:09:00', '2025-06-01 07:19:00');

-- --------------------------------------------------------

--
-- Structure de la table `salat`
--

CREATE TABLE `salat` (
  `id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `mosque_id` int DEFAULT NULL,
  `location_id` int DEFAULT NULL,
  `afiliation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ceremony_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cimetary` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mosque_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `salat`
--

INSERT INTO `salat` (`id`, `created_by_id`, `mosque_id`, `location_id`, `afiliation`, `firstname`, `lastname`, `ceremony_at`, `created_at`, `content`, `cimetary`, `mosque_name`) VALUES
(1, 1, 4, NULL, 'bro', 'Gyr', 'Jen', '2024-04-15 19:39:00', '2023-12-21 15:35:09', 'Fggg hibiscus hbhthbyhhtjvfhbtgerhv', '', ''),
(2, 1, 2, NULL, 'ami', 'Grety', 'Nicolas ', '2023-12-21 00:00:00', '2023-12-21 18:19:32', 'Ecce eggdegc efghg', '', ''),
(3, 1, 2, NULL, 'bro', 'gimz', 'ged', '2024-04-15 10:40:00', '2024-04-15 10:41:30', 'Fgghhh', '', ''),
(4, 9, NULL, NULL, 'bro', 'Nic', 'Dff', '2024-05-21 14:50:00', '2024-05-08 18:34:31', '', 'gap', ''),
(8, 1, NULL, NULL, 'bro', 'sffr', 'tzww', '2024-05-13 18:38:04', '2024-05-13 18:38:14', '', 'zfgff', ''),
(9, 1, NULL, NULL, 'bro', 'sdfff', 'fresh htrt', '2024-05-13 18:40:11', '2024-05-13 18:40:22', '', 'sdfty', ''),
(10, 1, NULL, NULL, 'bro', 'ggg', 'hhh', '2024-06-03 11:35:00', '2024-05-16 11:36:08', '', 'vhhhhj', ''),
(11, 1, NULL, NULL, 'bro', 'dff', 'nic', '2020-05-27 14:52:00', '2024-05-16 14:52:52', '', 'gggggf', ''),
(12, 1, 4, NULL, 'sister', 'eedr', 'sde', '2024-05-23 10:14:44', '2024-05-23 10:15:03', '', 'drrty', ''),
(13, 1, NULL, NULL, 'bro', 'eeeee', 'ddwd', '2025-01-03 15:15:00', '2025-01-01 15:15:31', '', 'dddfttt', ''),
(15, 1, 2, NULL, 'father', 'tyyjv', 'trsh', '2025-03-02 15:13:00', '2025-02-21 12:14:18', '', 'trrfg 15h13 ajout ', ''),
(16, 1, 12, NULL, 'grandm', 'ghv', 'ggv', '2025-02-26 08:24:15', '2025-02-26 05:24:30', '', 'jjhvb', ''),
(17, 1, 2, NULL, 'dot', 'dsz', 'dzz', '2025-03-10 07:31:46', '2025-03-10 04:31:59', '', 'ddee3', ''),
(18, 1, NULL, NULL, 'father', 'Manual', 'M9s', '2025-03-11 00:00:00', '2025-03-11 06:11:24', '', 'jjjhh', 'dfdsfdsfdsfgg'),
(19, 1, NULL, NULL, 'father', 'hhhhh', 'suur', '2025-03-11 09:19:46', '2025-03-11 06:20:05', '', 'hhh', 'manual mosq'),
(20, 1, 2, NULL, 'father', 'Hyy', 'Ttt', '2025-03-19 00:00:00', '2025-03-19 10:22:52', '', 'u7u', NULL),
(21, 1, NULL, NULL, 'father', 'Xde', 'Newwh', '2025-03-19 00:00:00', '2025-03-19 10:44:12', '', 'dfgggf', NULL),
(22, 1, NULL, NULL, 'father', 'ooooo', 'juu', '2025-03-19 13:57:17', '2025-03-19 10:57:27', '', '999999', NULL),
(23, 1, NULL, NULL, 'father', 'Ggg', 'Yyfgg', '2025-03-19 00:00:00', '2025-03-19 10:59:12', '', 'fgg', NULL),
(24, 1, NULL, NULL, 'father', 'yygtyy', 'tttttt', '2025-03-19 14:00:17', '2025-03-19 11:00:36', '', 'yhuu', NULL),
(25, 7, 2, NULL, 'brother', 'hhhhh', 'hjj', '2025-03-23 14:02:14', '2025-03-19 11:02:27', '', 'hhjju', NULL),
(28, 1, 2, NULL, 'father', 'test', 'test', '2025-03-21 17:22:00', '2025-03-20 14:22:40', '', 'test', NULL),
(29, 15, NULL, NULL, 'dot', 'nnn', 'nnn', '2025-03-21 23:25:00', '2025-03-21 04:26:00', '', 'hhvhh', 'hhhh'),
(30, 1, 2, NULL, 'father', 'tgggg', 'mosque 2', '2025-03-31 15:31:31', '2025-03-24 12:31:48', '', 'jjjjj', NULL),
(31, 16, 2, NULL, 'father', 'gghhh', 'tfc', '2025-05-12 11:11:00', '2025-04-03 09:12:00', '', 'jgcv', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `salat_share`
--

CREATE TABLE `salat_share` (
  `id` int NOT NULL,
  `salat_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `salat_share`
--

INSERT INTO `salat_share` (`id`, `salat_id`, `user_id`) VALUES
(2, 11, 1),
(3, 11, 2),
(4, 11, 3),
(7, 28, 15);

-- --------------------------------------------------------

--
-- Structure de la table `testament`
--

CREATE TABLE `testament` (
  `id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `location` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `family` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `goods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `update_at` datetime NOT NULL,
  `toilette` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fixe` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `lastwill` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `testament`
--

INSERT INTO `testament` (`id`, `created_by_id`, `location`, `family`, `goods`, `created_at`, `update_at`, `toilette`, `fixe`, `lastwill`) VALUES
(1, 1, 'Eetgfde fdwgrd gfc 454', '', 'Zfgt ddff ', '2024-01-04 14:15:04', '2024-01-04 14:15:04', 'Hhfvb', 'Ghuuhklkvv', 'Jwhhsbbbs dbbjjjd\nDjjdjjjd\nHhhdhhd\n\n\n\n\nDhhhdhhd\n\nJjdjhhhhd\n\n\n\n\nDhhhdhhhhhhdhhd\n\n\n\n\n\n\nHdhhhhdhhd\n\n\n\n\n\nJdhhhhdhhhhd\n\n\n\n\nJdhhdhhhhd\n\n\n\n\n\n\n\n\n\n\nHdhhhhhd'),
(3, 2, 'Test 2 from user 2 fdwgrd gfc 454', 'Eetgfde fdwgrd gfc esdd', 'Zfgt ddff ', '2024-01-04 14:15:04', '2024-01-04 14:15:04', NULL, NULL, NULL),
(4, 3, 'Test 3 from user 3 fdwgrd gfc 454', 'Eetgfde fdwgrd gfc esdd', 'Zfgt ddff ', '2024-01-04 14:15:04', '2024-01-04 14:15:04', NULL, NULL, NULL),
(5, 6, 'Hggff yytt', 'Hellsbbd', 'Shvvdvv dhhd', '2024-03-24 11:07:09', '2024-03-24 11:07:09', 'Toilette famille ', 'Fixe bri', 'Last will zre'),
(6, 15, 'Only ond', NULL, NULL, '2025-03-11 07:45:08', '2025-03-11 07:45:08', NULL, NULL, NULL),
(7, 16, NULL, NULL, NULL, '2025-04-28 15:20:53', '2025-04-28 15:20:53', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `testament_share`
--

CREATE TABLE `testament_share` (
  `id` int NOT NULL,
  `testament_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `testament_share`
--

INSERT INTO `testament_share` (`id`, `testament_id`, `user_id`) VALUES
(6, 3, 1),
(7, 4, 1),
(8, 4, 16),
(10, 1, 5),
(11, 1, 2),
(13, 1, 4);

-- --------------------------------------------------------

--
-- Structure de la table `todo`
--

CREATE TABLE `todo` (
  `id` int NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordered` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `todo`
--

INSERT INTO `todo` (`id`, `content`, `ordered`) VALUES
(1, '&amp;lt;p&amp;gt;A Salem Alaykoum&amp;amp;nbsp;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;Moi {user_fullname}, je souhaite sinc&egrave;rement vous demander pardon pour toutes paroles, gestes, regard ou tort qui&amp;lt;strong&amp;gt; auraient pu vous blesser&amp;amp;nbsp;&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&ldquo;Que celui qui a fait du tort &agrave; son fr&egrave;re, lui demande pardon, car, (le jour du jugement) il n&rsquo;y aura ni dinar, ni dirham. Sinon, il sera pris de ses bonnes actions pour les donner &agrave; son fr&egrave;re et s&rsquo;il n&rsquo;en a pas, alors, il sera pris des mauvaises actions de son fr&egrave;re pour les mettre sur son compte.&rdquo;&amp;lt;/p&amp;gt;', 0),
(2, '&amp;lt;p&amp;gt;formatt&amp;lt;/p&amp;gt;', 1),
(4, '&amp;lt;p&amp;gt;dsq kfds fdsf dsqF D&amp;lt;/p&amp;gt;', 2),
(5, '&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;COROS supports the following 3rd Party Apps for data synchronization.&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;h3&amp;gt;&amp;lt;strong&amp;gt;To connect to a new app, please go to the COROS app &amp;amp;gt; Profile page &amp;amp;gt; Settings &amp;amp;gt; 3rd Party Apps.&amp;amp;nbsp;&amp;lt;/strong&amp;gt;&amp;lt;/h3&amp;gt;&amp;lt;p&amp;gt;&amp;lt;a target=&amp;quot;_blank&amp;quot; rel=&amp;quot;noopener noreferrer&amp;quot; href=&amp;quot;https://www.strava.com/&amp;quot;&amp;gt;Strava&amp;lt;/a&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;Designed by athletes, for athletes, Strava&amp;#039;s mobile app and website connect millions of runners and cyclists through the sports they love.&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;a target=&amp;quot;_blank&amp;quot; rel=&amp;quot;noopener noreferrer&amp;quot; href=&amp;quot;https://www.trainingpeaks.com/&amp;quot;&amp;gt;TrainingPeaks&amp;lt;/a&amp;gt;&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;Featuring plans from expert coaches like Matt Fitzgerald, David Glover and Joe Friel. Take the Training Plan Quiz. Skip the search and find the perfect plan for you!&amp;lt;/p&amp;gt;&amp;lt;p&amp;gt;&amp;lt;br&amp;gt;&amp;amp;nbsp;&amp;lt;/p&amp;gt;', 3),
(6, '&amp;lt;p&amp;gt;FSQD FQSF SQF&amp;lt;br&amp;gt;FDSFDQDF DSQFDSFDSQFDSFSQD&amp;lt;/p&amp;gt;', 4),
(7, '&amp;lt;p&amp;gt;FDS GKJHHsddsgffdgfdgfdg fd&amp;lt;/p&amp;gt;', 5),
(8, '&amp;lt;p&amp;gt;There are not many programming languages which don&rsquo;t have at least one way to access a database. In a few decades, databases, and more precisely relational databases, have become the standard way to store software data in an organized, accessible and safe way. While the recent introduction of NoSQL platforms like MongoDB or Elasticsearch i&amp;lt;strong&amp;gt;s changing the way databases are used, relational databases are still the norm and the most used databases around the world.&amp;lt;/strong&amp;gt;&amp;lt;/p&amp;gt;', 6),
(9, '&amp;lt;p&amp;gt;There are not many programming languages which don&rsquo;t have at least one way to access a database. In a few decades, data&amp;lt;a href=&amp;quot;dsqdsq dsq&amp;quot;&amp;gt;bases, and more precisely relational d&amp;lt;/a&amp;gt;atabases, have become the standard way to store software data in an organized, accessible and safe way. While the recent introduction of NoSQL platforms like MongoDB or Elasticsearch is changing the way databases are used, relational databases are still the norm and the most used databases around the world.&amp;lt;/p&amp;gt;', 7);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `create_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `last_login` datetime DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_id` int DEFAULT NULL,
  `photo_id` int DEFAULT NULL,
  `show_dette_infos` tinyint(1) DEFAULT NULL,
  `phone_prefix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `deleted_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `firstname`, `lastname`, `create_at`, `last_login`, `phone`, `location_id`, `photo_id`, `show_dette_infos`, `phone_prefix`, `enabled`, `deleted_at`) VALUES
(1, 'test@test.fr', '[\"ROLE_ADMIN\"]', '$2y$13$C0tTcX.9lpq0pY/HPvfgn.h3cUd1dHpMb59XKRbI1OSq.zfuJaSBi', 'Borisrrr', 'Bruyère', '2023-12-03 13:30:38', '2023-12-03 00:00:00', '4555368', 1, 14, 0, '+126', 1, NULL),
(2, 'user1@test.fr', '[]', '$2y$13$MXKgns5zZIktvCYK6Zq1yuCnVn5CHlxpIMOLtq/DBOBhX/9O9Su0O', 'nicolas', 'salem', '2023-12-05 10:19:12', '2023-12-05 10:19:12', NULL, NULL, NULL, NULL, '', 1, NULL),
(3, 'user2@test.fr', '[]', '$2y$13$WEoZ110e.kG7YCne4.rAVu.bKyG5rRXdkzQrE0F4uAjEitmqJSgwC', 'frali', 'gthbd', '2023-12-05 10:21:14', '2023-12-05 10:21:14', NULL, NULL, NULL, NULL, '', 1, NULL),
(4, 'nocontact@tzst.fr', '[]', '$2y$13$Si5HX0NLD8Nz/uvx2pwnQu/PWp2vHKv/bRC2mSzVO5YP6sDCptpN.', 'nocid4', 'dhys', '2024-03-23 09:52:06', '2024-03-23 09:52:06', NULL, NULL, NULL, NULL, '', 1, NULL),
(5, 'invitemslconnect@gmail.fr', '[]', '$2y$13$LzmVAJJNxtUz0QhoJ//Cd.QZR7MuZfvqX4d4F1WPlpv7tDMaJ4gG2', 'invite', 'hdggs', '2024-03-23 11:03:45', '2024-03-23 11:03:45', NULL, NULL, NULL, NULL, '', 1, NULL),
(6, 'invitemslconnect2@gmail.fr', '[]', '$2y$13$usAtjNvsslZjhA1ECMXFL.LA0HvvpRrug.nYCtdIZxyS75P/ulVV2', 'invite2', 'hdggs', '2024-03-23 11:05:12', '2024-03-23 11:05:12', NULL, 40, NULL, NULL, '', 1, NULL),
(7, 'phobd@ehhd.dh', '[]', '$2y$13$VsHsH2mmLaTAlnqvp1F8D.Bg7k92BU9F5R9UaRc3rNWXKSDlZn4Xu', 'shhhe', 'hehhd', '2024-05-08 10:21:22', '2024-05-08 10:21:22', '+333555533', NULL, NULL, 1, '', 0, '2024-05-09 09:14:02'),
(8, 'tedddst@test.fr', '[]', '$2y$13$UNN1EocE6AP8krjGkGWSLuvtebKAola5kph5zeoSmfsuHHPDtR4pG', 'dfftdff', 'dfffxftt', '2024-05-08 10:42:35', '2024-05-08 10:42:35', '', NULL, NULL, 1, '+33', 1, NULL),
(9, 'ssddtest@test.fr', '[]', '$2y$13$bVYb/ybkT5WXe8VPcGQBuOEaxEX50zgrLkk5/rXPN.Ff8f6iULXgS', 'sdr', 'fffrr', '2024-05-08 12:12:56', '2024-05-08 12:12:56', '4566778888', NULL, NULL, 1, '+355', 1, NULL),
(10, 'usegr2@test.fr', '[]', '$2y$13$3zb9K1ZiwBtQokosTMCRTefiQTZkL4lYCokiWvrGHTJHM9j4bUHRO', 'hdggd', 'gsgggz', '2024-05-22 12:06:08', '2024-05-22 12:06:08', '736639849', NULL, NULL, 1, '+33', 1, NULL),
(11, 'boris.bruyere.web@gmail.com', '[]', '$2y$13$MNiKYH05D4eMWYlud0AhzuSyWGRInzpcRXirkcMtDDiuPHV.0HJc2', 'Maint', 'testzur', '2025-03-10 06:12:04', '2025-03-10 06:12:04', '6655', NULL, NULL, 0, '+33', 1, NULL),
(12, 'hhdhhe@dhh.dd', '[]', '$2y$13$1musO6Fun.BDFX/D3jOiVuS36aPAyn4lRDQ0yqkTxeKRK84JdH4BS', 'suhhd', 'hhhe', '2025-03-10 06:18:55', '2025-03-10 06:18:55', '6666666', NULL, NULL, 1, '+33', 1, NULL),
(13, 'deleteeed-athlegggte1@test.com', '[]', '$2y$13$xkf3CeaHZ.j/4TtsUPGksOfBHm5tA1uDZd7VJOKRLg26O4HiYZib.', 'hshhdhehdh', 'ehhhdh', '2025-03-10 06:21:28', '2025-03-10 06:21:28', NULL, NULL, NULL, 1, '+33', 0, '2025-06-01 06:43:52'),
(14, 'deleteeed-athleshhshhte1@test.com', '[]', '$2y$13$cMJ7nv86o/.EalImABlBDemMEk.2bMWIGongrxxkggp5JO4DvU3Mq', 'hhshh', 'dhhhhh', '2025-03-10 06:22:53', '2025-03-10 06:22:53', NULL, NULL, NULL, 1, '+33', 0, '2025-06-01 06:45:50'),
(15, 'testhh@test.fr', '[]', '$2y$13$ONGXkjwb8VUV7pm7pvbnN.8vAVk/sZQ40YFEzAU36Yt4djKTvMZ2S', 'testne', 'tyiih', '2025-03-10 11:43:23', '2025-03-10 11:43:23', '780003242', 60, NULL, 0, '+33', 1, NULL),
(16, 'ttt@ttt.fr', '[]', '$2y$13$WT3kEJCINbuVX5LNHIuTdeJEUUpsGpVqDS.OTuzzh.HUVp5Q.7r9i', 'ttt', 'ttt', '2025-03-28 14:41:35', '2025-03-28 14:41:35', '69852148', 61, NULL, 0, '+33', 1, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `carte`
--
ALTER TABLE `carte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_BAD4FFFDCC05B47E` (`salat_id`),
  ADD KEY `IDX_BAD4FFFDB03A8386` (`created_by_id`);

--
-- Index pour la table `carte_share`
--
ALTER TABLE `carte_share`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_67A9E985C9C7CEB6` (`carte_id`),
  ADD KEY `IDX_67A9E985A76ED395` (`user_id`);

--
-- Index pour la table `carte_text`
--
ALTER TABLE `carte_text`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `chat_message`
--
ALTER TABLE `chat_message`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_FAB3FC1693CB796C` (`file_id`),
  ADD KEY `IDX_FAB3FC16B03A8386` (`created_by_id`),
  ADD KEY `IDX_FAB3FC16C47D5262` (`chat_thread_id`);

--
-- Index pour la table `chat_notification`
--
ALTER TABLE `chat_notification`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_relation` (`user_id`,`thread_id`),
  ADD KEY `IDX_41BF1F4BE2904019` (`thread_id`),
  ADD KEY `IDX_41BF1F4BA76ED395` (`user_id`);

--
-- Index pour la table `chat_participant`
--
ALTER TABLE `chat_participant`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_relation` (`user_id`,`thread_id`),
  ADD KEY `IDX_E8ED9C89A76ED395` (`user_id`),
  ADD KEY `IDX_E8ED9C89E2904019` (`thread_id`);

--
-- Index pour la table `chat_thread`
--
ALTER TABLE `chat_thread`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_56FC7BA2BA0E79C3` (`last_message_id`),
  ADD UNIQUE KEY `UNIQ_56FC7BA23DA5256D` (`image_id`),
  ADD KEY `IDX_56FC7BA2B03A8386` (`created_by_id`);

--
-- Index pour la table `dece`
--
ALTER TABLE `dece`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_A8141B3064D218E` (`location_id`),
  ADD KEY `IDX_A8141B30B03A8386` (`created_by_id`);

--
-- Index pour la table `deuil`
--
ALTER TABLE `deuil`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `deuil_date`
--
ALTER TABLE `deuil_date`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_9943E108A76ED395` (`user_id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `don`
--
ALTER TABLE `don`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_F8F081D93DA5256D` (`image_id`);

--
-- Index pour la table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `fcm_token`
--
ALTER TABLE `fcm_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_19B88AF9A76ED395` (`user_id`);

--
-- Index pour la table `imam`
--
ALTER TABLE `imam`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_688078E564D218E` (`location_id`);

--
-- Index pour la table `intro`
--
ALTER TABLE `intro`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `invitation`
--
ALTER TABLE `invitation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_F11D61A2B03A8386` (`created_by_id`);

--
-- Index pour la table `jeun`
--
ALTER TABLE `jeun`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_C381005CB03A8386` (`created_by_id`);

--
-- Index pour la table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `mail`
--
ALTER TABLE `mail`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `maraude`
--
ALTER TABLE `maraude`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_DA5E9CA264D218E` (`location_id`),
  ADD KEY `IDX_DA5E9CA2873649CA` (`managed_by_id`);

--
-- Index pour la table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Index pour la table `mosque`
--
ALTER TABLE `mosque`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_5DE348CA64D218E` (`location_id`),
  ADD KEY `IDX_5DE348CA873649CA` (`managed_by_id`);

--
-- Index pour la table `mosque_favorite`
--
ALTER TABLE `mosque_favorite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_13CC3C94FBDAA034` (`mosque_id`),
  ADD KEY `IDX_13CC3C94A76ED395` (`user_id`);

--
-- Index pour la table `mosque_notif_dece`
--
ALTER TABLE `mosque_notif_dece`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_144C6C0CFBDAA034` (`mosque_id`),
  ADD KEY `IDX_144C6C0CF1C63FEF` (`dece_id`);

--
-- Index pour la table `nav_page_content`
--
ALTER TABLE `nav_page_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_4D9AA9963DA5256D` (`image_id`);

--
-- Index pour la table `notif_to_send`
--
ALTER TABLE `notif_to_send`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BC2080E6A76ED395` (`user_id`);

--
-- Index pour la table `obligation`
--
ALTER TABLE `obligation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_720EBF27B03A8386` (`created_by_id`),
  ADD KEY `IDX_720EBF2740B4AC4E` (`related_to_id`);

--
-- Index pour la table `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `pardon`
--
ALTER TABLE `pardon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D835C243B03A8386` (`created_by_id`);

--
-- Index pour la table `pardon_share`
--
ALTER TABLE `pardon_share`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3B48DEA4BBE2879E` (`pardon_id`),
  ADD KEY `IDX_3B48DEA4B2F44014` (`share_with_id`);

--
-- Index pour la table `pompe`
--
ALTER TABLE `pompe`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_E5D44D564D218E` (`location_id`),
  ADD KEY `IDX_E5D44D5873649CA` (`managed_by_id`);

--
-- Index pour la table `pompe_notification`
--
ALTER TABLE `pompe_notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CF4699096CCC95AD` (`pompe_id`),
  ADD KEY `IDX_CF469909F1C63FEF` (`dece_id`);

--
-- Index pour la table `pray_notification`
--
ALTER TABLE `pray_notification`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_A01A295BA76ED395` (`user_id`);

--
-- Index pour la table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_9BACE7E1C74F2195` (`refresh_token`);

--
-- Index pour la table `relation`
--
ALTER TABLE `relation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6289474995DC9185` (`user_source_id`),
  ADD KEY `IDX_62894749156E8682` (`user_target_id`);

--
-- Index pour la table `resetpassword`
--
ALTER TABLE `resetpassword`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_C88C64F6A76ED395` (`user_id`);

--
-- Index pour la table `salat`
--
ALTER TABLE `salat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_918920E364D218E` (`location_id`),
  ADD KEY `IDX_918920E3B03A8386` (`created_by_id`),
  ADD KEY `IDX_918920E3FBDAA034` (`mosque_id`);

--
-- Index pour la table `salat_share`
--
ALTER TABLE `salat_share`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_58AF1C98CC05B47E` (`salat_id`),
  ADD KEY `IDX_58AF1C98A76ED395` (`user_id`);

--
-- Index pour la table `testament`
--
ALTER TABLE `testament`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_116A262DB03A8386` (`created_by_id`);

--
-- Index pour la table `testament_share`
--
ALTER TABLE `testament_share`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D9F263AA386D1BF0` (`testament_id`),
  ADD KEY `IDX_D9F263AAA76ED395` (`user_id`);

--
-- Index pour la table `todo`
--
ALTER TABLE `todo`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  ADD UNIQUE KEY `UNIQ_8D93D64964D218E` (`location_id`),
  ADD UNIQUE KEY `UNIQ_8D93D6497E9E4C8C` (`photo_id`);
ALTER TABLE `user` ADD FULLTEXT KEY `IDX_8D93D64983A00E683124B5B6` (`firstname`,`lastname`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `carte`
--
ALTER TABLE `carte`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT pour la table `carte_share`
--
ALTER TABLE `carte_share`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `carte_text`
--
ALTER TABLE `carte_text`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `chat_message`
--
ALTER TABLE `chat_message`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT pour la table `chat_notification`
--
ALTER TABLE `chat_notification`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pour la table `chat_participant`
--
ALTER TABLE `chat_participant`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT pour la table `chat_thread`
--
ALTER TABLE `chat_thread`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `dece`
--
ALTER TABLE `dece`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `deuil`
--
ALTER TABLE `deuil`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `deuil_date`
--
ALTER TABLE `deuil_date`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT pour la table `don`
--
ALTER TABLE `don`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `fcm_token`
--
ALTER TABLE `fcm_token`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT pour la table `imam`
--
ALTER TABLE `imam`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `intro`
--
ALTER TABLE `intro`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `invitation`
--
ALTER TABLE `invitation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `jeun`
--
ALTER TABLE `jeun`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `location`
--
ALTER TABLE `location`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT pour la table `mail`
--
ALTER TABLE `mail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `maraude`
--
ALTER TABLE `maraude`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `media`
--
ALTER TABLE `media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `mosque`
--
ALTER TABLE `mosque`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `mosque_favorite`
--
ALTER TABLE `mosque_favorite`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `mosque_notif_dece`
--
ALTER TABLE `mosque_notif_dece`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `nav_page_content`
--
ALTER TABLE `nav_page_content`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `notif_to_send`
--
ALTER TABLE `notif_to_send`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `obligation`
--
ALTER TABLE `obligation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT pour la table `page`
--
ALTER TABLE `page`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `pardon`
--
ALTER TABLE `pardon`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `pardon_share`
--
ALTER TABLE `pardon_share`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `pompe`
--
ALTER TABLE `pompe`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `pompe_notification`
--
ALTER TABLE `pompe_notification`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `pray_notification`
--
ALTER TABLE `pray_notification`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT pour la table `relation`
--
ALTER TABLE `relation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `resetpassword`
--
ALTER TABLE `resetpassword`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `salat`
--
ALTER TABLE `salat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `salat_share`
--
ALTER TABLE `salat_share`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `testament`
--
ALTER TABLE `testament`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `testament_share`
--
ALTER TABLE `testament_share`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `todo`
--
ALTER TABLE `todo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `carte`
--
ALTER TABLE `carte`
  ADD CONSTRAINT `FK_BAD4FFFDB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_BAD4FFFDCC05B47E` FOREIGN KEY (`salat_id`) REFERENCES `salat` (`id`);

--
-- Contraintes pour la table `carte_share`
--
ALTER TABLE `carte_share`
  ADD CONSTRAINT `FK_67A9E985A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_67A9E985C9C7CEB6` FOREIGN KEY (`carte_id`) REFERENCES `carte` (`id`);

--
-- Contraintes pour la table `chat_message`
--
ALTER TABLE `chat_message`
  ADD CONSTRAINT `FK_FAB3FC1693CB796C` FOREIGN KEY (`file_id`) REFERENCES `media` (`id`),
  ADD CONSTRAINT `FK_FAB3FC16B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_FAB3FC16C47D5262` FOREIGN KEY (`chat_thread_id`) REFERENCES `chat_thread` (`id`);

--
-- Contraintes pour la table `chat_notification`
--
ALTER TABLE `chat_notification`
  ADD CONSTRAINT `FK_41BF1F4BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_41BF1F4BE2904019` FOREIGN KEY (`thread_id`) REFERENCES `chat_thread` (`id`);

--
-- Contraintes pour la table `chat_participant`
--
ALTER TABLE `chat_participant`
  ADD CONSTRAINT `FK_E8ED9C89A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_E8ED9C89E2904019` FOREIGN KEY (`thread_id`) REFERENCES `chat_thread` (`id`);

--
-- Contraintes pour la table `chat_thread`
--
ALTER TABLE `chat_thread`
  ADD CONSTRAINT `FK_56FC7BA23DA5256D` FOREIGN KEY (`image_id`) REFERENCES `media` (`id`),
  ADD CONSTRAINT `FK_56FC7BA2B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_56FC7BA2BA0E79C3` FOREIGN KEY (`last_message_id`) REFERENCES `chat_message` (`id`);

--
-- Contraintes pour la table `dece`
--
ALTER TABLE `dece`
  ADD CONSTRAINT `FK_A8141B3064D218E` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `FK_A8141B30B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `deuil_date`
--
ALTER TABLE `deuil_date`
  ADD CONSTRAINT `FK_9943E108A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `don`
--
ALTER TABLE `don`
  ADD CONSTRAINT `FK_F8F081D93DA5256D` FOREIGN KEY (`image_id`) REFERENCES `media` (`id`);

--
-- Contraintes pour la table `fcm_token`
--
ALTER TABLE `fcm_token`
  ADD CONSTRAINT `FK_19B88AF9A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `imam`
--
ALTER TABLE `imam`
  ADD CONSTRAINT `FK_688078E564D218E` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`);

--
-- Contraintes pour la table `invitation`
--
ALTER TABLE `invitation`
  ADD CONSTRAINT `FK_F11D61A2B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `jeun`
--
ALTER TABLE `jeun`
  ADD CONSTRAINT `FK_C381005CB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `maraude`
--
ALTER TABLE `maraude`
  ADD CONSTRAINT `FK_DA5E9CA264D218E` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `FK_DA5E9CA2873649CA` FOREIGN KEY (`managed_by_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `mosque`
--
ALTER TABLE `mosque`
  ADD CONSTRAINT `FK_5DE348CA64D218E` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `FK_5DE348CA873649CA` FOREIGN KEY (`managed_by_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `mosque_favorite`
--
ALTER TABLE `mosque_favorite`
  ADD CONSTRAINT `FK_13CC3C94A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_13CC3C94FBDAA034` FOREIGN KEY (`mosque_id`) REFERENCES `mosque` (`id`);

--
-- Contraintes pour la table `mosque_notif_dece`
--
ALTER TABLE `mosque_notif_dece`
  ADD CONSTRAINT `FK_144C6C0CF1C63FEF` FOREIGN KEY (`dece_id`) REFERENCES `dece` (`id`),
  ADD CONSTRAINT `FK_144C6C0CFBDAA034` FOREIGN KEY (`mosque_id`) REFERENCES `mosque` (`id`);

--
-- Contraintes pour la table `nav_page_content`
--
ALTER TABLE `nav_page_content`
  ADD CONSTRAINT `FK_4D9AA9963DA5256D` FOREIGN KEY (`image_id`) REFERENCES `media` (`id`);

--
-- Contraintes pour la table `notif_to_send`
--
ALTER TABLE `notif_to_send`
  ADD CONSTRAINT `FK_BC2080E6A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `obligation`
--
ALTER TABLE `obligation`
  ADD CONSTRAINT `FK_720EBF2740B4AC4E` FOREIGN KEY (`related_to_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_720EBF27B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `pardon`
--
ALTER TABLE `pardon`
  ADD CONSTRAINT `FK_D835C243B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `pardon_share`
--
ALTER TABLE `pardon_share`
  ADD CONSTRAINT `FK_3B48DEA4B2F44014` FOREIGN KEY (`share_with_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_3B48DEA4BBE2879E` FOREIGN KEY (`pardon_id`) REFERENCES `pardon` (`id`);

--
-- Contraintes pour la table `pompe`
--
ALTER TABLE `pompe`
  ADD CONSTRAINT `FK_E5D44D564D218E` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `FK_E5D44D5873649CA` FOREIGN KEY (`managed_by_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `pompe_notification`
--
ALTER TABLE `pompe_notification`
  ADD CONSTRAINT `FK_CF4699096CCC95AD` FOREIGN KEY (`pompe_id`) REFERENCES `pompe` (`id`),
  ADD CONSTRAINT `FK_CF469909F1C63FEF` FOREIGN KEY (`dece_id`) REFERENCES `dece` (`id`);

--
-- Contraintes pour la table `pray_notification`
--
ALTER TABLE `pray_notification`
  ADD CONSTRAINT `FK_A01A295BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `relation`
--
ALTER TABLE `relation`
  ADD CONSTRAINT `FK_62894749156E8682` FOREIGN KEY (`user_target_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_6289474995DC9185` FOREIGN KEY (`user_source_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `resetpassword`
--
ALTER TABLE `resetpassword`
  ADD CONSTRAINT `FK_C88C64F6A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `salat`
--
ALTER TABLE `salat`
  ADD CONSTRAINT `FK_918920E364D218E` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `FK_918920E3B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_918920E3FBDAA034` FOREIGN KEY (`mosque_id`) REFERENCES `mosque` (`id`);

--
-- Contraintes pour la table `salat_share`
--
ALTER TABLE `salat_share`
  ADD CONSTRAINT `FK_58AF1C98A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_58AF1C98CC05B47E` FOREIGN KEY (`salat_id`) REFERENCES `salat` (`id`);

--
-- Contraintes pour la table `testament`
--
ALTER TABLE `testament`
  ADD CONSTRAINT `FK_116A262DB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `testament_share`
--
ALTER TABLE `testament_share`
  ADD CONSTRAINT `FK_D9F263AA386D1BF0` FOREIGN KEY (`testament_id`) REFERENCES `testament` (`id`),
  ADD CONSTRAINT `FK_D9F263AAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D64964D218E` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `FK_8D93D6497E9E4C8C` FOREIGN KEY (`photo_id`) REFERENCES `media` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
