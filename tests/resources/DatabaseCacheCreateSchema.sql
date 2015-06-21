CREATE SCHEMA IF NOT EXISTS `comodojo` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `comodojo` ;

DROP TABLE IF EXISTS `comodojo`.`comodojo_cache` ;

CREATE TABLE IF NOT EXISTS `comodojo`.`comodojo_cache` (
  `name` VARCHAR(200) NOT NULL,
  `data` TEXT NULL,
  `namespace` VARCHAR(64) NULL,
  `expire` INT NULL,
  PRIMARY KEY (`name`))
ENGINE = InnoDB;