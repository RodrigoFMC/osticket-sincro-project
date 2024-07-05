-- Tabela para conter a definição de "Elemento Terminal"
CREATE TABLE `{TABLE_PREFIX}sincro_elemento_terminal` (
    `id` INT NOT NULL,
    `description` VARCHAR(64) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Tabela para conter a definição de "Elemento Composto"
CREATE TABLE `{TABLE_PREFIX}sincro_elemento_composto` (
    `id` INT NOT NULL,
    `description` VARCHAR(64) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Tabela para conter a definição de "Router"
CREATE TABLE `{TABLE_PREFIX}sincro_router` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `model` VARCHAR(64) NOT NULL,
    `suplier` VARCHAR(64) NOT NULL,
    `serial_number` VARCHAR(64) NOT NULL,
    `ip_address` VARCHAR(32) NOT NULL,
    `id_term` INT NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_term`) REFERENCES `{TABLE_PREFIX}sincro_elemento_terminal` (`id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Tabela para conter a definição de "UPS"
CREATE TABLE `{TABLE_PREFIX}sincro_ups` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `model` VARCHAR(64) NOT NULL,
    `suplier` VARCHAR(64) NOT NULL,
    `serial_number` VARCHAR(64) NOT NULL,
    `id_term` INT NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_term`) REFERENCES `{TABLE_PREFIX}sincro_elemento_terminal` (`id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Tabela para conter a definição de "Cinemómetro"
CREATE TABLE `{TABLE_PREFIX}sincro_cinemometer` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `model` VARCHAR(64) NOT NULL,
    `suplier` VARCHAR(64) NOT NULL,
    `serial_number` VARCHAR(64) NOT NULL,
    `id_term` INT NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_term`) REFERENCES `{TABLE_PREFIX}sincro_elemento_terminal` (`id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Tabela para conter a definição de "Caixa"
CREATE TABLE `{TABLE_PREFIX}sincro_box` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `model` VARCHAR(64) NOT NULL,
    `suplier` VARCHAR(64) NOT NULL,
    `serial_number` VARCHAR(64) NOT NULL,
    `id_term` INT NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_term`) REFERENCES `{TABLE_PREFIX}sincro_elemento_terminal` (`id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Tabela para conter a definição de "Cabine"
CREATE TABLE `{TABLE_PREFIX}sincro_cabinet` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_comp` INT NOT NULL,
    `id_ups` INT NOT NULL,
    `id_box` INT NOT NULL,
    `model` VARCHAR(64) NOT NULL,
    `suplier` VARCHAR(64) NOT NULL,
    `serial_number` VARCHAR(64) NOT NULL,
    `address` VARCHAR(32) NOT NULL,
    `district` VARCHAR(32) NOT NULL,
    `pk` VARCHAR(16) NOT NULL,
    `c_d` CHAR(1) NOT NULL,
    `ap_af` VARCHAR(5) NOT NULL,
    `lat` FLOAT NOT NULL,
    `lon` FLOAT NOT NULL,
    `ip_address` VARCHAR(32) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id_comp`) REFERENCES `{TABLE_PREFIX}sincro_elemento_composto` (`id`),
    FOREIGN KEY (`id_ups`) REFERENCES `{TABLE_PREFIX}sincro_ups` (`id`),
    FOREIGN KEY (`id_box`) REFERENCES `{TABLE_PREFIX}sincro_box` (`id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Tabela para conter a relação "has" entre a "Cabine" e "Router"
CREATE TABLE `{TABLE_PREFIX}sincro_cabinet_has_router` (
    `idCabin` INT NOT NULL,
    `idRouter` INT NOT NULL,
    `startDate` DATETIME NOT NULL,
    `endDate` DATETIME,
    FOREIGN KEY (`idCabin`) REFERENCES `{TABLE_PREFIX}sincro_cabinet` (`id`),
    FOREIGN KEY (`idRouter`) REFERENCES `{TABLE_PREFIX}sincro_router` (`id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Tabela para conter a relação "has" entre a "Cabine" e "Cinemometro"
CREATE TABLE `{TABLE_PREFIX}sincro_cabinet_has_cinemometer` (
    `idCabin` INT NOT NULL,
    `idCinemometer` INT NOT NULL,
    `startDate` DATETIME NOT NULL,
    `endDate` DATETIME,
    FOREIGN KEY (`idCabin`) REFERENCES `{TABLE_PREFIX}sincro_cabinet` (`id`),
    FOREIGN KEY (`idCinemometer`) REFERENCES `{TABLE_PREFIX}sincro_cinemometer` (`id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;
