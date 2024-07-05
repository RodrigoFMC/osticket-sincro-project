-- Desativa a verificação de chaves estrangeiras para evitar erros de dependência
SET FOREIGN_KEY_CHECKS = 0;

-- Remoção de tabelas que contêm chaves estrangeiras para outras tabelas
DROP TABLE IF EXISTS `{TABLE_PREFIX}sincro_cabinet_has_router`;
DROP TABLE IF EXISTS `{TABLE_PREFIX}sincro_cabinet_has_cinemometer`;
DROP TABLE IF EXISTS `{TABLE_PREFIX}sincro_cabinet`;

-- Remoção das tabelas que são referenciadas pelas chaves estrangeiras nas tabelas acima
DROP TABLE IF EXISTS `{TABLE_PREFIX}sincro_router`;
DROP TABLE IF EXISTS `{TABLE_PREFIX}sincro_ups`;
DROP TABLE IF EXISTS `{TABLE_PREFIX}sincro_box`;
DROP TABLE IF EXISTS `{TABLE_PREFIX}sincro_cinemometer`;

-- Finalmente, remova as tabelas de elementos que são referenciadas por outras tabelas
DROP TABLE IF EXISTS `{TABLE_PREFIX}sincro_elemento_composto`;
DROP TABLE IF EXISTS `{TABLE_PREFIX}sincro_elemento_terminal`;

-- Reativa a verificação de chaves estrangeiras
SET FOREIGN_KEY_CHECKS = 1;
