<?php

define('PLUGIN_NAME', 'Radar Details');

// Log and file paths
define('PLUGIN_DIR', __DIR__ . '/');
define('LOG_FILE', PLUGIN_DIR . 'logs/plugin_logs.log');

// Table names
define('OST_LIST_TABLE', TABLE_PREFIX . 'list');
define('OST_LIST_ITEMS_TABLE', TABLE_PREFIX . 'list_items');

// Session keys
define('SESSION_FORM_ID_PREFIX', 'form_id_');
define('SESSION_PREVIOUS_STATE_INSTANCE_PREFIX', 'previousActiveStateInstance');
define('SESSION_PREVIOUS_STATE_PLUGIN_PREFIX', 'previousActiveStatePlugin');


define('INITIALIZED','Plugin_SINCRO_initialized');

// Custom code tags
define('CUSTOM_CODE_START', '// Custom code start');
define('CUSTOM_CODE_END', '// Custom code end');
define('CUSTOM_JS_CLIENT_PATH', PLUGIN_DIR . 'custom_code/custom-client.js');
define('CUSTOM_JS_STAFF_PATH', PLUGIN_DIR . 'custom_code/custom-staff.js');

// Messages
define('MSG_PLUGIN_BOOTSTRAP', 'Plugin is bootstrapping.');
define('MSG_NO_ACTIVE_INSTANCES', 'No active instances found.');
define('MSG_PLUGIN_INSTALLED', 'Plugin is being installed for instance ID: ');
define('MSG_RESTORING_INFO', 'Restoring info...');
define('MSG_PLUGIN_DISABLED', 'Plugin is being disabled for instance ID: ');
define('MSG_SAVING_DATA', 'Saving data for form ID: ');
define('MSG_NO_FORM_SELECTED', 'No form selected for instance ID: ');
define('MSG_ROUTE_REGISTERED', 'Route /ajax-options/ added to dispatcher.');
define('MSG_FIRST_RUN', 'First run detected for instance ID: ');
define('MSG_FAILED_FIRST_RUN', 'Failed to configure first run for instance ID: ');
define('MSG_CUSTOM_CODE_ALREADY_INJECTED', 'Custom code already injected in ');
define('MSG_CUSTOM_CODE_INJECTED', 'Custom code injected into ');
define('MSG_CUSTOM_CODE_REMOVED', 'Custom code removed from ');
define('MSG_FILE_NOT_FOUND', 'File not found: ');
define('MSG_CUSTOM_CODE_NOT_FOUND', 'Custom code not found in ');

// Field Constants
define('FIELD_LISTA_CABINES', 'Lista de Cabines');
define('FIELD_SELECT_DEVICES', 'Selecione os dispositivos com avarias');
define('FIELD_CABINE', 'Cabine');
define('FIELD_ROUTER', 'Router');
define('FIELD_CINEMOMETRO', 'Cinemómetro');
define('FIELD_UPS', 'UPS');
define('FIELD_CAIXA', 'Caixa');
define('FIELD_OUTRO', 'Outro');
define('FIELD_DESCRICAO', 'Descrição');

define('FIELD_SELECT_CABINE', 'Selecione uma Cabine');
define('FIELD_HINT', '');
define('FIELD_DESCREVE_AVARIA', 'Descreve a avaria');

// Field Names
define('NAME_LISTA_CABINES', 'lista_cabines');
define('NAME_BREAK_AVARIAS', 'break_avarias');
define('NAME_CABINE', 'cabine');
define('NAME_ROUTER', 'router');
define('NAME_CINEMOMETRO', 'cinemometro');
define('NAME_UPS', 'ups');
define('NAME_CAIXA', 'caixa');
define('NAME_OUTRO', 'outro');
define('NAME_DESCRICAO', 'descricao');

// Custom code tags
define('TAG_START', '<!-- custom code via plugin -->');
define('TAG_END', '<!-- end of custom code via plugin-->');

// Header file paths
define('HEADER_CLIENT', 'client/header.inc.php');
define('HEADER_STAFF', 'staff/header.inc.php');


// Table names
define('PLUGIN_INSTANCE_TABLE', TABLE_PREFIX . 'plugin_instance');
define('FORM_FIELD_TABLE', TABLE_PREFIX . 'form_field');
define('FORM_ENTRY_VALUES_TABLE', TABLE_PREFIX . 'form_entry_values');
define('FORM_ENTRY_TABLE', TABLE_PREFIX . 'form_entry');
define('BACKUP_FORM_FIELD_TABLE', TABLE_PREFIX . 'backup_form_field');
define('BACKUP_FORM_ENTRY_VALUES_TABLE', TABLE_PREFIX . 'backup_form_entry_values');
define('BACKUP_FORM_ENTRY_TABLE', TABLE_PREFIX . 'backup_form_entry');
define('LIST_TABLE', TABLE_PREFIX . 'list');
define('LIST_ITEMS_TABLE', TABLE_PREFIX . 'list_items');
define('FORM_TABLE', TABLE_PREFIX . 'form');
define('FILE_TABLE', TABLE_PREFIX . 'file');
define('CONFIG_TABLE', TABLE_PREFIX . 'config');
define('SINCRO_CABINET_TABLE', TABLE_PREFIX . 'sincro_cabinet');



?>
