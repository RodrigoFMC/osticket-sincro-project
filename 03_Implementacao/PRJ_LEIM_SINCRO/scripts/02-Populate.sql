-- Valores por omissão do tipo "Elemento Terminal"
INSERT INTO `{TABLE_PREFIX}sincro_elemento_terminal` (`id`, `description`) VALUES ('1', 'Router de rede');
INSERT INTO `{TABLE_PREFIX}sincro_elemento_terminal` (`id`, `description`) VALUES ('2', 'Cinemómetro');
INSERT INTO `{TABLE_PREFIX}sincro_elemento_terminal` (`id`, `description`) VALUES ('3', 'Uninterruptible Power Supply');
INSERT INTO `{TABLE_PREFIX}sincro_elemento_terminal` (`id`, `description`) VALUES ('50', 'Cabine metálica SINCRO');
INSERT INTO `{TABLE_PREFIX}sincro_elemento_terminal` (`id`, `description`) VALUES ('51', 'Cabine metálica SINCRO no chão');
INSERT INTO `{TABLE_PREFIX}sincro_elemento_terminal` (`id`, `description`) VALUES ('52', 'Cabine metálica SINCRO em poste');

-- Valores por omissão do tipo "Elemento Composto"
INSERT INTO `{TABLE_PREFIX}sincro_elemento_composto` (`id`, `description`) VALUES ('100', 'Cabine SINCRO');
INSERT INTO `{TABLE_PREFIX}sincro_elemento_composto` (`id`, `description`) VALUES ('101', 'Local de Controlo de Velocidade');
INSERT INTO `{TABLE_PREFIX}sincro_elemento_composto` (`id`, `description`) VALUES ('102', 'Local de Controlo de Velocidade instantânea');
INSERT INTO `{TABLE_PREFIX}sincro_elemento_composto` (`id`, `description`) VALUES ('103', 'Local de Controlo de Velocidade média');
INSERT INTO `{TABLE_PREFIX}sincro_elemento_composto` (`id`, `description`) VALUES ('104', 'Sistema de Controlo');

-- Routers existentes
INSERT INTO `{TABLE_PREFIX}sincro_router` (`id`, `model`, `suplier`, `serial_number`, `ip_address`, `id_term`) VALUES ('1', '2900', 'Cisco', 'FCZ-1234-1', '10.10.1.10', '1');
INSERT INTO `{TABLE_PREFIX}sincro_router` (`id`, `model`, `suplier`, `serial_number`, `ip_address`, `id_term`) VALUES ('2', '2900', 'Cisco', 'FCZ-1234-2', '10.10.1.20', '1');
INSERT INTO `{TABLE_PREFIX}sincro_router` (`id`, `model`, `suplier`, `serial_number`, `ip_address`, `id_term`) VALUES ('3', '2900', 'Cisco', 'FCZ-1234-3', '10.10.1.30', '1');
INSERT INTO `{TABLE_PREFIX}sincro_router` (`id`, `model`, `suplier`, `serial_number`, `ip_address`, `id_term`) VALUES ('4', '2900', 'Cisco', 'FCZ-1234-4', '10.10.1.40', '1');
INSERT INTO `{TABLE_PREFIX}sincro_router` (`id`, `model`, `suplier`, `serial_number`, `ip_address`, `id_term`) VALUES ('5', '2900', 'Cisco', 'FCZ-1234-5', '10.10.1.50', '1');
INSERT INTO `{TABLE_PREFIX}sincro_router` (`id`, `model`, `suplier`, `serial_number`, `ip_address`, `id_term`) VALUES ('6', '2900', 'Cisco', 'FCZ-1234-6', '10.10.1.60', '1');

-- UPS existentes
INSERT INTO `{TABLE_PREFIX}sincro_ups` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('1', 'Smart-UPS', 'APC', 'SUA-1900-1', '3');
INSERT INTO `{TABLE_PREFIX}sincro_ups` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('2', 'Smart-UPS', 'APC', 'SUA-1900-2', '3');
INSERT INTO `{TABLE_PREFIX}sincro_ups` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('3', 'Smart-UPS', 'APC', 'SUA-1900-3', '3');
INSERT INTO `{TABLE_PREFIX}sincro_ups` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('4', 'Smart-UPS', 'APC', 'SUA-2200-1', '3');
INSERT INTO `{TABLE_PREFIX}sincro_ups` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('5', 'Smart-UPS', 'APC', 'SUA-2200-2', '3');
INSERT INTO `{TABLE_PREFIX}sincro_ups` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('6', 'Smart-UPS', 'APC', 'SUA-2200-3', '3');

-- Cinemometros existentes
INSERT INTO `{TABLE_PREFIX}sincro_cinemometer` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('1', 'Mc 2000', 'Micotec', 'Mc-PT-2000-1', '2');
INSERT INTO `{TABLE_PREFIX}sincro_cinemometer` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('2', 'Mc 2000', 'Micotec', 'Mc-PT-2000-2', '2');
INSERT INTO `{TABLE_PREFIX}sincro_cinemometer` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('3', 'Mc 2000', 'Micotec', 'Mc-PT-2000-3', '2');
INSERT INTO `{TABLE_PREFIX}sincro_cinemometer` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('4', 'PolCam 2022', 'Yunex Traffic', 'PC-2022-1', '2');
INSERT INTO `{TABLE_PREFIX}sincro_cinemometer` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('5', 'PolCam 2022', 'Yunex Traffic', 'PC-2022-2', '2');

-- Caixas existentes
INSERT INTO `{TABLE_PREFIX}sincro_box` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('1', 'Sincro V1', 'Metalo PT', 'Me-C-2000-1', '51');
INSERT INTO `{TABLE_PREFIX}sincro_box` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('2', 'Sincro V1', 'Metalo PT', 'Me-C-2000-2', '51');
INSERT INTO `{TABLE_PREFIX}sincro_box` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('3', 'Sincro V1', 'Metalo PT', 'Me-C-2000-3', '51');
INSERT INTO `{TABLE_PREFIX}sincro_box` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('4', 'Sincro V2', 'PolCam', 'YT-P-2024-1', '52');
INSERT INTO `{TABLE_PREFIX}sincro_box` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('5', 'Sincro V2', 'PolCam', 'YT-P-2024-2', '52');
INSERT INTO `{TABLE_PREFIX}sincro_box` (`id`, `model`, `suplier`, `serial_number`, `id_term`) VALUES ('6', 'Sincro V2', 'PolCam', 'YT-P-2024-3', '52');

-- Cabines existentes
INSERT INTO `{TABLE_PREFIX}sincro_cabinet` 
    (`id`, `id_comp`, `id_ups`, `id_box`, `model`, `suplier`, `serial_number`, `address`, `district`, `pk`, `c_d`, `ap_af`, `lat`, `lon`, `ip_address`) VALUES 
    ('1', '100', '1', '1', 'M-Box', 'Micotec', 'SN-B-SIN-1', 'A1', 'Porto', 'km 292.943', 'D', 'AF', '41.078557', '-8.581285', '10.10.1.11');
INSERT INTO `{TABLE_PREFIX}sincro_cabinet` 
    (`id`, `id_comp`, `id_ups`, `id_box`, `model`, `suplier`, `serial_number`, `address`, `district`, `pk`, `c_d`, `ap_af`, `lat`, `lon`, `ip_address`) VALUES 
    ('2', '100', '2', '2', 'M-Box', 'Micotec', 'SN-B-SIN-2', 'EN252', 'Setubal', 'km 9.016', 'D', 'AF/AP', '38.613397', '-8.911392', '10.10.1.21');
INSERT INTO `{TABLE_PREFIX}sincro_cabinet` 
    (`id`, `id_comp`, `id_ups`, `id_box`, `model`, `suplier`, `serial_number`, `address`, `district`, `pk`, `c_d`, `ap_af`, `lat`, `lon`, `ip_address`) VALUES 
    ('3', '100', '3', '3', 'M-Box', 'Micotec', 'SN-B-SIN-3', 'IC1', 'Setubal', 'km 548.590', 'D', 'AF/AP', '38.512197', '-8.611384', '10.10.1.31');
INSERT INTO `{TABLE_PREFIX}sincro_cabinet` 
    (`id`, `id_comp`, `id_ups`, `id_box`, `model`, `suplier`, `serial_number`, `address`, `district`, `pk`, `c_d`, `ap_af`, `lat`, `lon`, `ip_address`) VALUES 
    ('4', '100', '4', '4', 'YT-Box', 'Yunex Traffic', 'SN-C-SIN-1', 'A44', 'Vila Nova de Gaia', 'km 4.480', 'D', 'AF', '41.11988', '-8.62431', '10.10.1.41');
INSERT INTO `{TABLE_PREFIX}sincro_cabinet` 
    (`id`, `id_comp`, `id_ups`, `id_box`, `model`, `suplier`, `serial_number`, `address`, `district`, `pk`, `c_d`, `ap_af`, `lat`, `lon`, `ip_address`) VALUES 
    ('5', '100', '5', '5', 'YT-Box', 'Yunex Traffic', 'SN-C-SIN-2', 'EN103', 'Barcelos', 'km 22.942', 'D', 'AF/AP', '41.519874', '-8.606419', '10.10.1.51');
INSERT INTO `{TABLE_PREFIX}sincro_cabinet` 
    (`id`, `id_comp`, `id_ups`, `id_box`, `model`, `suplier`, `serial_number`, `address`, `district`, `pk`, `c_d`, `ap_af`, `lat`, `lon`, `ip_address`) VALUES 
    ('6', '100', '6', '6', 'YT-Box', 'Yunex Traffic', 'SN-C-SIN-3', 'A2', 'Faro', 'km 213.11', 'C', 'AF', '37.211742', '-8.238832', '10.10.1.61');

-- Definição dos routers das cabines
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_router` (`idCabin`, `idRouter`, `startDate`) VALUES ('1', '1', CURDATE());
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_router` (`idCabin`, `idRouter`, `startDate`) VALUES ('2', '2', CURDATE());
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_router` (`idCabin`, `idRouter`, `startDate`) VALUES ('3', '3', CURDATE());
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_router` (`idCabin`, `idRouter`, `startDate`) VALUES ('4', '4', CURDATE());
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_router` (`idCabin`, `idRouter`, `startDate`) VALUES ('5', '5', CURDATE());
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_router` (`idCabin`, `idRouter`, `startDate`) VALUES ('6', '6', CURDATE());

-- Definição dos cinemometros das cabines
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_cinemometer` (`idCabin`, `idCinemometer`, `startDate`) VALUES ('1', '1', CURDATE());
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_cinemometer` (`idCabin`, `idCinemometer`, `startDate`) VALUES ('2', '2', CURDATE());
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_cinemometer` (`idCabin`, `idCinemometer`, `startDate`) VALUES ('3', '3', CURDATE());
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_cinemometer` (`idCabin`, `idCinemometer`, `startDate`) VALUES ('4', '4', CURDATE());
INSERT INTO `{TABLE_PREFIX}sincro_cabinet_has_cinemometer` (`idCabin`, `idCinemometer`, `startDate`) VALUES ('5', '5', CURDATE());
