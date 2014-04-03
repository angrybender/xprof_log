CREATE TABLE IF NOT EXISTS `xhp_profiler`.`log_dumps` (
  `id_dump` INT(11) not null primary key auto_increment,
  `full_path` TEXT NULL DEFAULT NULL)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `xhp_profiler`.`projects_files` (
  `id_file` INT(11) not null primary key auto_increment,
  `full_path` VARCHAR(1500) NULL DEFAULT NULL)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `xhp_profiler`.`functions_call` (
  `id_function` INT(11) not null primary key auto_increment,
  `timestamp` DOUBLE NULL DEFAULT NULL,
  `file` INT(11) NULL DEFAULT NULL,
  `line` INT(11) NULL DEFAULT NULL,
  `name` VARCHAR(1000) NULL,
  `dump` INT(11) NULL DEFAULT NULL)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `xhp_profiler`.`vars_dump` (
	`var_id` INT not null primary key auto_increment,
	`function` INT NULL,
	`type` VARCHAR(20) NULL,
	`source` MEDIUMTEXT NULL)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `xhp_profiler`.`var_sources` (
	`id_sources` INT(11) not null primary key auto_increment,
	`var` INT NULL,
	`assoc_key` VARCHAR(1000) NULL,
	`assoc_value` MEDIUMTEXT NULL)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;
