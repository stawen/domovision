-- --------------------------------------------------------
-- Hôte:                         192.168.79.20
-- Version du serveur:           5.5.40-0+wheezy1 - (Debian)
-- Serveur OS:                   debian-linux-gnu
-- HeidiSQL Version:             9.1.0.4867
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Export de la structure de la base pour domovision
DROP DATABASE IF EXISTS `domovision`;
CREATE DATABASE IF NOT EXISTS `domovision` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `domovision`;


-- Export de la structure de procedure domovision. deleteEqt
DROP PROCEDURE IF EXISTS `deleteEqt`;
DELIMITER //
CREATE DEFINER=`root`@`%` PROCEDURE `deleteEqt`(IN `GroupAddress` TINYTEXT)
    COMMENT 'Suppression d''un eqt et de toutes ses asso, action differente si eqt d''etat ou d''action'
BEGIN

DECLARE idEqt INT;
DECLARE idEqtAction INT;
DECLARE idTypeEqt INT;
DECLARE CheckExists int;  
SET CheckExists = 0; 
# Recuperation de l'id de l'eqt
Select id, knx_type_equipement_id into idEqt, idTypeEqt from knx_equipement where knx_equipement.group_addr = GroupAddress;

# Si eqt d'etat, suppression du l'asso a un graphe
IF idTypeEqt = 2 THEN
	# suppresion de l'asso au graphe
	Delete from knx_asso_eq_graphe where knx_asso_eq_graphe.knx_equipement_id = idEqt;
	# suppression de toutes les données historisées
	Delete from knx_tracking where knx_tracking.knx_equipement_id = idEqt;
	#recuperation de l'eqt d'action s'il existe
	Select count(*) into CheckExists from knx_equipement where knx_equipement.grp_state = idEqt;
	#Si un eqt d'action lui est associé, on le retire du groupe d'action car il n'y aura plus de retour d'etat :)
	IF (CheckExists > 0) THEN
		Select id into idEqtAction from knx_equipement where knx_equipement.grp_state = idEqt;
		Delete from knx_asso_eq_grpaction where knx_asso_eq_grpaction.knx_equipement_id = idEqtAction;
	END IF;
	# suppression de la liasion avec une eqt d'action
	Update knx_equipement set knx_equipement.grp_state=null where knx_equipement.grp_state = idEqt;
END IF;

# si eqt d'action, alors suppression de l'asso GrpAction
IF idTypeEqt = 1 THEN
	Delete from knx_asso_eq_grpaction where knx_asso_eq_grpaction.knx_equipement_id = idEqt;
END IF;

#Suppression de l'eqt
Delete from knx_equipement where knx_equipement.id = idEqt;

END//
DELIMITER ;


-- Export de la structure de procedure domovision. deleteGraphe
DROP PROCEDURE IF EXISTS `deleteGraphe`;
DELIMITER //
CREATE DEFINER=`root`@`%` PROCEDURE `deleteGraphe`(IN `nomGraphe` TINYTEXT)
    COMMENT 'suppression du graphe et de l''asso graphe <-> eqt'
BEGIN
DECLARE idGraphe INT;
# Recuperation de l'id du graphe
Select id into idGraphe from knx_graphe where knx_graphe.name = nomGraphe;
# Suppression des liens eqt <-> graphe
Delete from knx_asso_eq_graphe where knx_asso_eq_graphe.knx_graphe_id = idGraphe;
# Suppression du graphe
Delete from knx_graphe where knx_graphe.id = idGraphe;

END//
DELIMITER ;


-- Export de la structure de procedure domovision. deleteGrpAction
DROP PROCEDURE IF EXISTS `deleteGrpAction`;
DELIMITER //
CREATE DEFINER=`root`@`%` PROCEDURE `deleteGrpAction`(IN `nomGrpAction` TINYTEXT)
    COMMENT 'Si suppresion du groupe d''asso action, alors de la liaison avec des eqt'
BEGIN
DECLARE idGrpAction INT;
# Recuperation de l'id du groupe d'action
Select id into idGrpAction from knx_groupe_action where knx_groupe_action.name = nomGrpAction;
# Suppression des liens eqt <-> grp action
Delete from knx_asso_eq_grpaction where knx_asso_eq_grpaction.knx_groupe_action_id = idGrpAction;
# Suppression du groupe d'action
Delete from knx_groupe_action where knx_groupe_action.id = idGrpAction;

END//
DELIMITER ;


-- Export de la structure de table domovision. knx_asso_eq_graphe
DROP TABLE IF EXISTS `knx_asso_eq_graphe`;
CREATE TABLE IF NOT EXISTS `knx_asso_eq_graphe` (
  `knx_graphe_id` tinyint(3) NOT NULL,
  `knx_equipement_id` tinyint(3) NOT NULL,
  `position` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- L'exportation de données n'été pas sélectionné.


-- Export de la structure de table domovision. knx_asso_eq_grpaction
DROP TABLE IF EXISTS `knx_asso_eq_grpaction`;
CREATE TABLE IF NOT EXISTS `knx_asso_eq_grpaction` (
  `knx_groupe_action_id` tinyint(3) NOT NULL,
  `knx_equipement_id` tinyint(3) NOT NULL,
  `position` tinyint(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- L'exportation de données n'été pas sélectionné.
-- Export de la structure de table domovision. knx_eqt_affichage
DROP TABLE IF EXISTS `knx_eqt_affichage`;
CREATE TABLE IF NOT EXISTS `knx_eqt_affichage` (
  `id` tinyint(3) NOT NULL,
  `type` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export de données de la table domovision.knx_eqt_affichage: ~3 rows (environ)
/*!40000 ALTER TABLE `knx_eqt_affichage` DISABLE KEYS */;
INSERT INTO `knx_eqt_affichage` (`id`, `type`) VALUES
	(1, 'Bouton'),
	(2, 'Slider'),
	(3, 'Indicateur');
/*!40000 ALTER TABLE `knx_eqt_affichage` ENABLE KEYS */;


-- Export de la structure de table domovision. knx_equipement
DROP TABLE IF EXISTS `knx_equipement`;
CREATE TABLE IF NOT EXISTS `knx_equipement` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `group_addr` tinytext NOT NULL,
  `dpt` tinytext NOT NULL,
  `name` mediumtext NOT NULL,
  `knx_type_reporting_id` int(2) DEFAULT NULL,
  `knx_type_equipement_id` int(2) DEFAULT NULL,
  `is_track` tinyint(1) NOT NULL DEFAULT '0',
  `grp_state` int(3) DEFAULT NULL,
  `comments` text,
  `knx_eqt_affichage_id` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- L'exportation de données n'été pas sélectionné.


-- Export de la structure de table domovision. knx_graphe
DROP TABLE IF EXISTS `knx_graphe`;
CREATE TABLE IF NOT EXISTS `knx_graphe` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `position` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- L'exportation de données n'été pas sélectionné.


-- Export de la structure de table domovision. knx_groupe_action
DROP TABLE IF EXISTS `knx_groupe_action`;
CREATE TABLE IF NOT EXISTS `knx_groupe_action` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `position` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- L'exportation de données n'été pas sélectionné.


-- Export de la structure de table domovision. knx_tracking

CREATE TABLE IF NOT EXISTS `knx_tracking` (
  `knx_equipement_id` int(3) NOT NULL,
  `jour` date NOT NULL,
  `heure` time NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`knx_equipement_id`,`jour`,`heure`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Export de la structure de table domovision. knx_type_equipement
DROP TABLE IF EXISTS `knx_type_equipement`;
CREATE TABLE IF NOT EXISTS `knx_type_equipement` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Export de données de la table domovision.knx_type_equipement: 2 rows
/*!40000 ALTER TABLE `knx_type_equipement` DISABLE KEYS */;
INSERT INTO `knx_type_equipement` (`id`, `name`) VALUES
	(1, 'Action'),
	(2, 'Etat');
	
	
-- L'exportation de données n'été pas sélectionné.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
