CREATE SCHEMA IF NOT EXISTS `comodojocache` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `comodojocache` ;

DROP TABLE IF EXISTS `comodojocache`.`cmdj_cache` ;

CREATE TABLE IF NOT EXISTS `comodojocache`.`cmdj_cache` (
  `name` VARCHAR(200) NOT NULL,
  `data` TEXT NULL,
  `namespace` VARCHAR(64) NULL,
  `expire` INT NULL,
  PRIMARY KEY (`name`))
ENGINE = InnoDB;
