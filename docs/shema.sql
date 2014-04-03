CREATE TABLE IF NOT EXISTS `xhp_profiler`.`log_dumps` (
  `id_dump` INT(11) NOT NULL,
  `full_path` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_dump`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `xhp_profiler`.`projects_files` (
  `id_file` INT(11) NOT NULL,
  `full_path` VARCHAR(1500) NULL DEFAULT NULL,
  PRIMARY KEY (`id_file`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `xhp_profiler`.`functions_call` (
  `id_function` INT(11) NOT NULL,
  `timestamp` DOUBLE NULL DEFAULT NULL,
  `file` INT(11) NULL DEFAULT NULL,
  `line` INT(11) NULL DEFAULT NULL,
  `dump` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_function`),
  INDEX `function_of_dump_idx` (`dump` ASC),
  INDEX `file_of_function_idx` (`file` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `xhp_profiler`.`vars_dump` (
  `var_id` INT(11) NOT NULL,
  `function` INT(11) NULL DEFAULT NULL,
  `type` VARCHAR(20) NULL DEFAULT NULL,
  `assoc_key` VARCHAR(1000) NULL DEFAULT NULL,
  `assoc_value` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`var_id`),
  INDEX `var_of_function_idx` (`function` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;