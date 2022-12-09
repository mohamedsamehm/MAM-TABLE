-- MySQL Script generated by MySQL Workbench
-- Mon Jul  5 09:36:47 2021
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mam_tables
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mam_tables
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mam_tables` DEFAULT CHARACTER SET utf8 ;
USE `mam_tables` ;

-- -----------------------------------------------------
-- Table `mam_tables`.`Department`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`Department` (
  `code` VARCHAR(45) NOT NULL,
  `name` VARCHAR(45) NULL,
  `dicated_place` VARCHAR(45) NULL,
  `Bulding` VARCHAR(45) NULL,
  PRIMARY KEY (`code`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`Professor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`Professor` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `phone` VARCHAR(45) NULL,
  `mail` VARCHAR(45) NULL,
  `hour_per_week` VARCHAR(45) NULL,
  `free_time` VARCHAR(45) NULL,
  `supervision_hour` VARCHAR(45) NULL,
  `vacation_size` INT NULL,
  `Number_Of_Office_periodes` INT NULL,
  `Department_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`ID`, `Department_code`),
  INDEX `fk_Professor_Department1_idx` (`Department_code` ASC),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC),
  UNIQUE INDEX `mail_UNIQUE` (`mail` ASC),
  CONSTRAINT `fk_Professor_Department1`
    FOREIGN KEY (`Department_code`)
    REFERENCES `mam_tables`.`Department` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`Courses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`Courses` (
  `code` VARCHAR(45) NOT NULL,
  `name` VARCHAR(45) NULL,
  `period_lecture` INT NULL,
  `period_section` INT NULL,
  `period_lab` INT NULL,
  `credit_hours` INT NULL,
  `Regulation` VARCHAR(45) NULL,
  PRIMARY KEY (`code`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`engineers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`engineers` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `phone` VARCHAR(45) NULL,
  `mail` VARCHAR(45) NULL,
  `hour_per_week` VARCHAR(45) NULL,
  `free_time` VARCHAR(45) NULL,
  `Vacation_size` INT NULL,
  `Number_Of_Office_periods` INT NULL,
  `Department_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`ID`, `Department_code`),
  INDEX `fk_engineers_Department1_idx` (`Department_code` ASC),
  UNIQUE INDEX `mail_UNIQUE` (`mail` ASC),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC),
  CONSTRAINT `fk_engineers_Department1`
    FOREIGN KEY (`Department_code`)
    REFERENCES `mam_tables`.`Department` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`Admin`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`Admin` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `mail` VARCHAR(45) NULL,
  `user_name` VARCHAR(45) NULL,
  `password` VARCHAR(45) NULL,
  `privileges` VARCHAR(45) NULL,
  PRIMARY KEY (`ID`),
  UNIQUE INDEX `user_name_UNIQUE` (`user_name` ASC),
  UNIQUE INDEX `mail_UNIQUE` (`mail` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`Place`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`Place` (
  `code` VARCHAR(45) NOT NULL,
  `address` VARCHAR(45) NULL,
  `bulding` VARCHAR(45) NULL,
  `no_of_chairs` VARCHAR(45) NULL,
  `specification` VARCHAR(45) NULL,
  `type` VARCHAR(45) NULL,
  PRIMARY KEY (`code`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`work_place`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`work_place` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `day` VARCHAR(45) NULL,
  `period_from` INT NULL,
  `period_to` INT NULL,
  `Place_code` VARCHAR(45) NOT NULL,
  `Courses_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`ID`, `Place_code`, `Courses_code`),
  INDEX `fk_Free_Place_Place1_idx` (`Place_code` ASC),
  INDEX `fk_work_place_Courses1_idx` (`Courses_code` ASC),
  CONSTRAINT `fk_Free_Place_Place10`
    FOREIGN KEY (`Place_code`)
    REFERENCES `mam_tables`.`Place` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_work_place_Courses1`
    FOREIGN KEY (`Courses_code`)
    REFERENCES `mam_tables`.`Courses` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`Load_professor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`Load_professor` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `Professor_ID` INT NOT NULL,
  `work_place_ID` INT NOT NULL,
  PRIMARY KEY (`ID`, `Professor_ID`, `work_place_ID`),
  INDEX `fk_Load_professor_Professor1_idx` (`Professor_ID` ASC),
  INDEX `fk_Load_professor_work_place1_idx` (`work_place_ID` ASC),
  CONSTRAINT `fk_Load_professor_Professor1`
    FOREIGN KEY (`Professor_ID`)
    REFERENCES `mam_tables`.`Professor` (`ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Load_professor_work_place1`
    FOREIGN KEY (`work_place_ID`)
    REFERENCES `mam_tables`.`work_place` (`ID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`Load_engineers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`Load_engineers` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `engineers_ID` INT NOT NULL,
  `work_place_ID` INT NOT NULL,
  PRIMARY KEY (`ID`, `engineers_ID`, `work_place_ID`),
  INDEX `fk_Load_engineers_engineers1_idx` (`engineers_ID` ASC),
  INDEX `fk_Load_engineers_work_place1_idx` (`work_place_ID` ASC),
  CONSTRAINT `fk_Load_engineers_engineers1`
    FOREIGN KEY (`engineers_ID`)
    REFERENCES `mam_tables`.`engineers` (`ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Load_engineers_work_place1`
    FOREIGN KEY (`work_place_ID`)
    REFERENCES `mam_tables`.`work_place` (`ID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`Free_Place`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`Free_Place` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `day` VARCHAR(45) NULL,
  `period_from` INT NULL,
  `period_to` INT NULL,
  `Place_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`ID`, `Place_code`),
  INDEX `fk_Free_Place_Place1_idx` (`Place_code` ASC),
  CONSTRAINT `fk_Free_Place_Place1`
    FOREIGN KEY (`Place_code`)
    REFERENCES `mam_tables`.`Place` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`levels`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`levels` (
  `code` VARCHAR(45) NOT NULL,
  `level` VARCHAR(45) NULL,
  `total_no_of_student` VARCHAR(45) NULL,
  `no_of_sections` INT NOT NULL,
  `semester` VARCHAR(45) NULL,
  `Regulation` VARCHAR(45) NULL,
  `Department_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`code`, `Department_code`),
  INDEX `fk_levels_Department1_idx` (`Department_code` ASC),
  CONSTRAINT `fk_levels_Department1`
    FOREIGN KEY (`Department_code`)
    REFERENCES `mam_tables`.`Department` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`level_sections`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`level_sections` (
  `code` VARCHAR(45) NOT NULL,
  `Section_name` VARCHAR(45) NULL,
  `levels_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`code`, `levels_code`),
  INDEX `fk_Section_levels1_idx` (`levels_code` ASC),
  CONSTRAINT `fk_Section_levels1`
    FOREIGN KEY (`levels_code`)
    REFERENCES `mam_tables`.`levels` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`Professor_has_Courses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`Professor_has_Courses` (
  `Professor_ID` INT NOT NULL,
  `Courses_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`Professor_ID`, `Courses_code`),
  INDEX `fk_Professor_has_Courses_Courses1_idx` (`Courses_code` ASC),
  INDEX `fk_Professor_has_Courses_Professor1_idx` (`Professor_ID` ASC),
  CONSTRAINT `fk_Professor_has_Courses_Professor1`
    FOREIGN KEY (`Professor_ID`)
    REFERENCES `mam_tables`.`Professor` (`ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Professor_has_Courses_Courses1`
    FOREIGN KEY (`Courses_code`)
    REFERENCES `mam_tables`.`Courses` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`engineers_has_Courses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`engineers_has_Courses` (
  `engineers_ID` INT NOT NULL,
  `Courses_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`engineers_ID`, `Courses_code`),
  INDEX `fk_engineers_has_Courses_Courses1_idx` (`Courses_code` ASC),
  INDEX `fk_engineers_has_Courses_engineers1_idx` (`engineers_ID` ASC),
  CONSTRAINT `fk_engineers_has_Courses_engineers1`
    FOREIGN KEY (`engineers_ID`)
    REFERENCES `mam_tables`.`engineers` (`ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_engineers_has_Courses_Courses1`
    FOREIGN KEY (`Courses_code`)
    REFERENCES `mam_tables`.`Courses` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`levels_has_Courses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`levels_has_Courses` (
  `levels_code` VARCHAR(45) NOT NULL,
  `Courses_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`levels_code`, `Courses_code`),
  INDEX `fk_levels_has_Courses_Courses1_idx` (`Courses_code` ASC),
  INDEX `fk_levels_has_Courses_levels1_idx` (`levels_code` ASC),
  CONSTRAINT `fk_levels_has_Courses_levels1`
    FOREIGN KEY (`levels_code`)
    REFERENCES `mam_tables`.`levels` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_levels_has_Courses_Courses1`
    FOREIGN KEY (`Courses_code`)
    REFERENCES `mam_tables`.`Courses` (`code`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`lab_and_sections`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`lab_and_sections` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(45) NOT NULL,
  `work_place_ID` INT NOT NULL,
  `engineers_ID` INT NOT NULL,
  `level_sections_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`ID`, `work_place_ID`, `engineers_ID`, `level_sections_code`),
  INDEX `fk_lab_work_place1_idx` (`work_place_ID` ASC),
  INDEX `fk_lab_engineers1_idx` (`engineers_ID` ASC),
  INDEX `fk_lab_and_sections_level_sections1_idx` (`level_sections_code` ASC),
  CONSTRAINT `fk_lab_work_place1`
    FOREIGN KEY (`work_place_ID`)
    REFERENCES `mam_tables`.`work_place` (`ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_lab_engineers1`
    FOREIGN KEY (`engineers_ID`)
    REFERENCES `mam_tables`.`engineers` (`ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_lab_and_sections_level_sections1`
    FOREIGN KEY (`level_sections_code`)
    REFERENCES `mam_tables`.`level_sections` (`code`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mam_tables`.`lecture`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mam_tables`.`lecture` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `work_place_ID` INT NOT NULL,
  `Professor_ID` INT NOT NULL,
  `level_sections_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`ID`, `work_place_ID`, `Professor_ID`, `level_sections_code`),
  INDEX `fk_lab_work_place1_idx` (`work_place_ID` ASC),
  INDEX `fk_levture_Professor1_idx` (`Professor_ID` ASC),
  INDEX `fk_lecture_level_sections1_idx` (`level_sections_code` ASC),
  CONSTRAINT `fk_lab_work_place10`
    FOREIGN KEY (`work_place_ID`)
    REFERENCES `mam_tables`.`work_place` (`ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_levture_Professor1`
    FOREIGN KEY (`Professor_ID`)
    REFERENCES `mam_tables`.`Professor` (`ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_lecture_level_sections1`
    FOREIGN KEY (`level_sections_code`)
    REFERENCES `mam_tables`.`level_sections` (`code`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;