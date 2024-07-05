<?php
require_once 'DatabaseHelper.php';
require_once 'Constants.php';

/**
 * PluginDataBaseManager class handles database operations for the plugin.
 */
class PluginDataBaseManager
{
     /**
     * Retrieves all inactive instances of a plugin from the database by plugin ID.
     *
     * @param int $pluginId The ID of the plugin.
     * @return array An array of inactive instances.
     */
    public static function getInactiveInstancesByPluginId($pluginId)
    {
        $query = "SELECT * FROM " . PLUGIN_INSTANCE_TABLE . " WHERE plugin_id = " . intval($pluginId) . " AND flags = 0";
        $resultado = DatabaseHelper::executeQuery($query);

        $instances = array();
        if ($resultado) {
            while ($row = DatabaseHelper::fetchRow($resultado)) {
                $instances[] = $row;
            }
        } else {
            error_log("Error executing query to fetch inactive instances for plugin ID: " . $pluginId);
        }

        return $instances;
    }

    /**
     * Adds a custom field to the specified form.
     *
     * @param int $form_id The form ID.
     * @param Campo $campo The field object containing field details.
     * @return bool True if the field was added successfully, false otherwise.
     */
    public static function adicionarCampoPersonalizado($form_id, $campo)
    {
        DatabaseHelper::startTransaction();

        try {
            // error_log("Adding custom field to the form: " . $campo->getFieldFormField());
            $sqlVerificacao = sprintf(
                "SELECT id FROM %s WHERE name = '%s' AND form_id = %d",
                FORM_FIELD_TABLE,
                addslashes($campo->getFieldVariable()),
                intval($form_id)
            );
            $resultadoVerificacao = DatabaseHelper::executeQuery($sqlVerificacao);
            if (DatabaseHelper::numRows($resultadoVerificacao) > 0) {
                // error_log("Custom field already exists. No action needed.");
                DatabaseHelper::commit();
                return true;
            }

            $sql = sprintf(
                "INSERT INTO %s (form_id, flags, type, label, name, configuration, hint, created, updated) VALUES (%d, %d, '%s', '%s', '%s', '%s', '%s', NOW(), NOW())",
                FORM_FIELD_TABLE,
                intval($form_id),
                $campo->getFieldFlags(),
                addslashes($campo->getFieldType()),
                addslashes($campo->getFieldLabel()),
                addslashes($campo->getFieldVariable()),
                addslashes($campo->getFieldConfiguration()),
                addslashes($campo->getFieldHint())
            );
            DatabaseHelper::executeQuery($sql);

            DatabaseHelper::commit();
            // error_log("Custom field added successfully.");
            return true;
        } catch (Exception $e) {
            DatabaseHelper::rollback();
            error_log("Error adding custom field: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Adds necessary custom fields to the specified instance.
     *
     * @param int $form_id The form ID.
     * @param array $campos_obj Array of Campo objects representing the fields to add.
     * @return bool True if all fields were added successfully, false otherwise.
     */
    public static function adicionarCamposNecessarios($form_id, $campos_obj)
    {
        if (!$form_id) {
            error_log("No form selected to add custom fields.");
            return false;
        }

        foreach ($campos_obj as $campo) {
            // error_log("Adding field " . $campo->getFieldLabel() . " to form ID: " . $form_id);

            $configuracao = $campo->getFieldConfiguration();
            if (isset($configuracao['regex']) && $configuracao['validator'] == 'regex') {
                $configuracao['regex'] = addslashes($configuracao['regex']);
                $campo->setFieldConfiguration($configuracao);
            }
            $campo->setFieldConfiguration(json_encode($campo->getFieldConfiguration(), JSON_UNESCAPED_UNICODE));

            $resultado = self::adicionarCampoPersonalizado($form_id, $campo);

            if (!$resultado) {
                error_log("Failed to add field " . $campo->getFieldLabel() . ".");
                return false;
            }
        }

        // error_log("All necessary fields added successfully.");
        return true;
    }

    /**
     * Removes necessary custom fields from the specified instance.
     *
     * @param int $form_id The form ID.
     * @param array $campos_obj Array of Campo objects representing the fields to remove.
     * @return bool True if all fields were removed successfully, false otherwise.
     */
    public static function removerCamposNecessarios($form_id, $campos_obj)
    {
        DatabaseHelper::startTransaction();

        if (!$form_id) {
            error_log("No form selected to remove custom fields.");
            return false;
        }

        foreach ($campos_obj as $campo) {
            $nome_campo = addslashes($campo->getFieldVariable());
            $nome_tabela_form_field = FORM_FIELD_TABLE;
            $nome_tabela_form_entry_values = FORM_ENTRY_VALUES_TABLE;

            try {
                $sqlVerificacao = sprintf(
                    "SELECT id FROM %s WHERE name = '%s' AND form_id = %d",
                    $nome_tabela_form_field,
                    $nome_campo,
                    intval($form_id)
                );
                $resultadoVerificacao = DatabaseHelper::executeQuery($sqlVerificacao);

                if ($resultado = DatabaseHelper::fetchRow($resultadoVerificacao)) {
                    $campo_id = $resultado['id'];

                    $sqlRemoveValues = sprintf(
                        "DELETE FROM %s WHERE field_id = %d",
                        $nome_tabela_form_entry_values,
                        intval($campo_id)
                    );
                    DatabaseHelper::executeQuery($sqlRemoveValues);

                    $sqlRemoveField = sprintf(
                        "DELETE FROM %s WHERE id = %d",
                        $nome_tabela_form_field,
                        intval($campo_id)
                    );
                    DatabaseHelper::executeQuery($sqlRemoveField);

                    // error_log("Field " . $nome_campo . " removed successfully.");
                } else {
                    error_log("Field " . $nome_campo . " not found in form ID " . $form_id);
                }
            } catch (Exception $e) {
                DatabaseHelper::rollback();
                error_log("Error removing field " . $nome_campo . ": " . $e->getMessage());
                return false;
            }
        }

        DatabaseHelper::commit();
        // error_log("All necessary fields removed successfully.");
        return true;
    }

    /**
     * Keeps plugin information by creating backups of the form fields and entries.
     */
    public static function keepPluginInfo($campos_obj)
    {
        self::createBackupTables();
        self::cleanBackupTables();

        $field_id_list = self::addFormField2Backup($campos_obj);
        $entry_id_list = self::addFormEntryValues2Backup($field_id_list);
        self::addFormEntry2Backup($entry_id_list);
    }

    /**
     * Cleans the backup tables.
     */
    private static function cleanBackupTables()
    {
        DatabaseHelper::executeQuery("TRUNCATE TABLE " . BACKUP_FORM_FIELD_TABLE);
        // error_log("Table " . BACKUP_FORM_FIELD_TABLE . " cleaned.");

        DatabaseHelper::executeQuery("TRUNCATE TABLE " . BACKUP_FORM_ENTRY_VALUES_TABLE);
        // error_log("Table " . BACKUP_FORM_ENTRY_VALUES_TABLE . " cleaned.");

        DatabaseHelper::executeQuery("TRUNCATE TABLE " . BACKUP_FORM_ENTRY_TABLE);
        // error_log("Table " . BACKUP_FORM_ENTRY_TABLE . " cleaned.");
    }

    /**
     * Adds form entries to the backup table.
     *
     * @param array $entry_id_list List of entry IDs to backup.
     */
    private static function addFormEntry2Backup($entry_id_list)
    {
        if (empty($entry_id_list)) {
            error_log("No entry IDs to process.");
            return;
        }
        $sql = "SELECT * FROM " . FORM_ENTRY_TABLE . " WHERE id IN (" . implode(", ", array_map('intval', $entry_id_list)) . ")";

        // error_log("SQL: " . $sql);
        $result = DatabaseHelper::executeQuery($sql);

        while ($row = DatabaseHelper::fetchRow($result)) {
            $object_id = $row['object_id'] ? $row['object_id'] : 'NULL';
            $extra = $row['extra'] ? addslashes($row['extra']) : 'NULL';
            $sql = sprintf(
                "INSERT INTO %s (id, form_id, object_id, object_type, sort, extra, created, updated) VALUES (%d, %d, %s, '%s', %d, '%s', '%s', '%s')",
                BACKUP_FORM_ENTRY_TABLE,
                intval($row['id']),
                intval($row['form_id']),
                $object_id,
                $row['object_type'],
                intval($row['sort']),
                $extra,
                $row['created'],
                $row['updated']
            );
            // error_log("SQL: " . $sql);
            DatabaseHelper::executeQuery($sql);
        }
    }

    /**
     * Adds form entry values to the backup table.
     *
     * @param array $field_id_list List of field IDs to backup.
     * @return array List of entry IDs.
     */
    private static function addFormEntryValues2Backup($field_id_list)
    {
        if (empty($field_id_list)) {
            error_log("No field IDs to process.");
            return [];
        }
        $sql = sprintf(
            "SELECT * FROM %s WHERE field_id IN (%s)",
            FORM_ENTRY_VALUES_TABLE,
            implode(", ", array_map('intval', $field_id_list))
        );

        // error_log("SQL: " . $sql);
        $result = DatabaseHelper::executeQuery($sql);

        $entry_id_list = [];
        while ($row = DatabaseHelper::fetchRow($result)) {
            $vid = $row['value_id'] ? $row['value_id'] : 'NULL';
            $sql = sprintf(
                "INSERT INTO %s (entry_id, field_id, value, value_id) VALUES (%d, %d, '%s', %s)",
                BACKUP_FORM_ENTRY_VALUES_TABLE,
                intval($row['entry_id']),
                intval($row['field_id']),
                addslashes($row['value']),
                $vid
            );
            // error_log("SQL: " . $sql);
            DatabaseHelper::executeQuery($sql);
            $entry_id_list[] = $row['entry_id'];
        }

        return $entry_id_list;
    }


    /**
     * Adds form fields to the backup table.
     *
     * @return array List of field IDs.
     */
    private static function addFormField2Backup($campos_obj)
    {
        $field_id_list = [];

        foreach ($campos_obj as $campo) {
            $sql = sprintf(
                "SELECT * FROM %s WHERE name = '%s'",
                FORM_FIELD_TABLE,
                $campo->getFieldVariable()
            );

            // error_log("SQL: " . $sql);
            $result = DatabaseHelper::executeQuery($sql);

            while ($row = DatabaseHelper::fetchRow($result)) {
                $sql = sprintf(
                    "REPLACE INTO %s (id, form_id, flags, type, label, name, configuration, sort, hint, created, updated) 
                    VALUES (%d, %d, %d, '%s', '%s', '%s', '%s', %d, '%s', '%s', '%s')",
                    BACKUP_FORM_FIELD_TABLE,
                    intval($row['id']),
                    intval($row['form_id']),
                    intval($row['flags']),
                    $row['type'],
                    $row['label'],
                    $row['name'],
                    addslashes($row['configuration']),
                    intval($row['sort']),
                    addslashes($row['hint']),
                    $row['created'],
                    $row['updated']
                );
                // error_log("SQL: " . $sql);
                DatabaseHelper::executeQuery($sql);
                $field_id_list[] = $row['id'];
            }
        }

        return $field_id_list;
    }

    /**
     * Creates the backup tables if they do not exist.
     */
    private static function createBackupTables()
    {
        $tables = [
            BACKUP_FORM_FIELD_TABLE => "CREATE TABLE " . BACKUP_FORM_FIELD_TABLE . " (
                id INT(11) NOT NULL,
                form_id INT(11) NOT NULL,
                flags INT(11),
                type VARCHAR(255) NOT NULL,
                label VARCHAR(255) NOT NULL,
                name VARCHAR(64) NOT NULL,
                configuration TEXT,
                sort INT(11) NOT NULL,
                hint VARCHAR(512),
                created DATETIME NOT NULL,
                updated DATETIME NOT NULL,
                PRIMARY KEY (id)
            )",
            BACKUP_FORM_ENTRY_VALUES_TABLE => "CREATE TABLE " . BACKUP_FORM_ENTRY_VALUES_TABLE . " (
                entry_id INT(11) NOT NULL,
                field_id INT(11) NOT NULL,
                value TEXT,
                value_id INT(11),
                PRIMARY KEY (entry_id, field_id)
            )",
            BACKUP_FORM_ENTRY_TABLE => "CREATE TABLE " . BACKUP_FORM_ENTRY_TABLE . " (
                id INT(11) NOT NULL,
                form_id INT(11) NOT NULL,
                object_id INT(11),
                object_type VARCHAR(255) NOT NULL,
                sort INT(11) NOT NULL,
                extra TEXT,
                created DATETIME NOT NULL,
                updated DATETIME NOT NULL,
                PRIMARY KEY (id)
            )"
        ];

        foreach ($tables as $table => $createSql) {
            $sql = "SHOW TABLES LIKE '" . $table . "'";
            $result = DatabaseHelper::executeQuery($sql);
            if (DatabaseHelper::numRows($result) == 0) {
                DatabaseHelper::executeQuery($createSql);
                // error_log("Table " . $table . " created.");
            } else {
                // error_log("Table " . $table . " already exists.");
            }
        }
    }

    /**
     * Restores plugin information from backup tables to original tables.
     */
    public static function restorePluginInfoFromBackup($campos_obj)
    {
        DatabaseHelper::startTransaction();
    
        try {
            // Restore form fields
            $sql = "SELECT * FROM " . BACKUP_FORM_FIELD_TABLE;
            $result = DatabaseHelper::executeQuery($sql);
    
            while ($row = DatabaseHelper::fetchRow($result)) {
                if (substr($row['type'], 0, 5) === 'list-') {
                    $newListId = self::obterIdListaRadares();
                    if ($newListId) {
                        $row['type'] = 'list-' . $newListId;
                    } else {
                        error_log("Failed to fetch new list ID.");
                        continue;
                    }
                }
    
                $sqlRestoreField = sprintf(
                    "INSERT INTO %s (id, form_id, flags, type, label, name, configuration, sort, hint, created, updated) 
                    VALUES (%d, %d, %d, '%s', '%s', '%s', '%s', %d, '%s', '%s', '%s')
                    ON DUPLICATE KEY UPDATE 
                    form_id = VALUES(form_id), flags = VALUES(flags), type = VALUES(type), label = VALUES(label), 
                    name = VALUES(name), configuration = VALUES(configuration), sort = VALUES(sort), 
                    hint = VALUES(hint), created = VALUES(created), updated = VALUES(updated);",
                    FORM_FIELD_TABLE,
                    $row['id'],
                    $row['form_id'],
                    $row['flags'],
                    $row['type'],
                    $row['label'],
                    $row['name'],
                    addslashes($row['configuration']),
                    $row['sort'],
                    addslashes($row['hint']),
                    $row['created'],
                    $row['updated']
                );
    
                $resultRestoreField = DatabaseHelper::executeQuery($sqlRestoreField);
                if (!$resultRestoreField) {
                    error_log("Failed to restore or update field {$row['name']}.");
                }
            }
    
            // Restore form entry values
            $sql = "SELECT * FROM " . BACKUP_FORM_ENTRY_VALUES_TABLE;
            $result = DatabaseHelper::executeQuery($sql);
    
            while ($row = DatabaseHelper::fetchRow($result)) {
                $value = json_decode($row['value'], true);
    
                if (is_array($value)) {
                    $firstKey = array_key_first($value);
                    $secondPart = explode(':', stripslashes($row['value']), 2)[1]; // Obter a segunda parte do valor após o :
                    $secondPart = trim($secondPart, '"}'); // Remover chaves e aspas adicionais
                    $secondPart = stripslashes($secondPart); // Remover barras invertidas extras
                    // error_log("First key: " . $firstKey . ", Second part: " . $secondPart);
                    $newListItemId = self::getNewListItemId($firstKey, $secondPart, self::obterIdListaRadares());
    
                    if ($newListItemId !== null) {
                        $value = self::alterarChaveJson($value, $firstKey, $newListItemId);
                        $row['value'] = json_encode($value, JSON_UNESCAPED_UNICODE);
                    }
                }
    
                $value_id_part = $row['value_id'] !== NULL ? $row['value_id'] : "NULL";
    
                $sqlRestoreEntryValue = sprintf(
                    "REPLACE INTO %s (entry_id, field_id, value, value_id) VALUES (%d, %d, '%s', %s);",
                    FORM_ENTRY_VALUES_TABLE,
                    $row['entry_id'],
                    $row['field_id'],
                    addslashes($row['value']),
                    $value_id_part
                );
    
                $resultRestoreEntryValue = DatabaseHelper::executeQuery($sqlRestoreEntryValue);
                if (!$resultRestoreEntryValue) {
                    error_log("Failed to restore entry value for field_id {$row['field_id']}.");
                }
            }
    
            // Restore form entries
            $sql = "SELECT * FROM " . BACKUP_FORM_ENTRY_TABLE;
            $result = DatabaseHelper::executeQuery($sql);
    
            while ($row = DatabaseHelper::fetchRow($result)) {
                $object_id_part = $row['object_id'] !== NULL ? $row['object_id'] : "NULL";
                $extra_part = $row['extra'] !== NULL ? "'" . addslashes($row['extra']) . "'" : "NULL";
    
                $sqlRestoreEntry = sprintf(
                    "REPLACE INTO %s (id, form_id, object_id, object_type, sort, extra, created, updated) 
                    VALUES (%d, %d, %s, '%s', %d, %s, '%s', '%s');",
                    FORM_ENTRY_TABLE,
                    $row['id'],
                    $row['form_id'],
                    $object_id_part,
                    $row['object_type'],
                    $row['sort'],
                    $extra_part,
                    $row['created'],
                    $row['updated']
                );
    
                $resultRestoreEntry = DatabaseHelper::executeQuery($sqlRestoreEntry);
                if (!$resultRestoreEntry) {
                    error_log("Failed to restore form entry id {$row['id']}.");
                }
            }
    
            DatabaseHelper::commit();
        } catch (Exception $e) {
            DatabaseHelper::rollback();
            error_log("Error restoring plugin info from backup: " . $e->getMessage());
        }
    }
    
    /**
     * Function to get new list item ID based on the old list item ID and the second part of the value
     */
    private static function getNewListItemId($oldListItemId, $secondPart, $listId)
    {
        $sql = "SELECT id FROM " . LIST_ITEMS_TABLE . " WHERE list_id = " . intval($listId) . " AND value LIKE '%" . addslashes($secondPart) . "'";
        $result = DatabaseHelper::executeQuery($sql);

        if ($row = DatabaseHelper::fetchRow($result)) {
            return intval($row['id']);
        }
        // error_log("Old list item ID: " . $oldListItemId . ", Second part: " . $secondPart . ", List ID: " . $listId);


        return $oldListItemId; // Retornar o ID antigo se não encontrar um novo
    }

    /**
     * Function to alter the key in a JSON array
     */
    private static function alterarChaveJson($jsonArray, $oldKey, $newKey)
    {
        $value = $jsonArray[$oldKey];
        unset($jsonArray[$oldKey]);
        $jsonArray[$newKey] = $value;
        return $jsonArray;
    }


    /**
     * Executes a SQL script from a specified file.
     *
     * @param string $file_path Path to the SQL file.
     * @return bool True if the script was executed successfully, false otherwise.
     */
    public static function executeSqlFile($file_path)
    {
        if (!file_exists($file_path)) {
            error_log("SQL file not found: " . $file_path);
            return false;
        }

        $sql_script = file_get_contents($file_path);
        if (!$sql_script) {
            error_log("Failed to read file: " . $file_path);
            return false;
        }

        // Substituir o marcador pelo prefixo
        $sql_script = str_replace('{TABLE_PREFIX}', TABLE_PREFIX, $sql_script);

        // error_log(nl2br(htmlentities($sql_script)));

        // Dividir o script em comandos SQL individuais
        $sql_commands = explode(';', $sql_script);
        $db_error = false;

        foreach ($sql_commands as $command) {
            $command = trim($command);
            if ($command) {
                if (!DatabaseHelper::executeQuery($command)) {
                    error_log("Error executing a SQL command: " . $command);
                    $db_error = true;
                }
            }
        }

        if ($db_error) {
            error_log("Errors found while executing the SQL file.");
            return false;
        }

        // error_log("SQL file executed successfully: " . $file_path);
        return true;
    }

    /**
     * Creates and populates the radar list.
     *
     * @return bool True if the list was created and populated successfully, false otherwise.
     */
    public static function criarEPopularListaCabines()
    {
        DatabaseHelper::startTransaction();

        $nomeLista = "Lista de Cabines";

        $sql = "SELECT id FROM " . LIST_TABLE . " WHERE name = '" . $nomeLista . "'";
        $resultado = DatabaseHelper::executeQuery($sql);
        if ($row = DatabaseHelper::fetchRow($resultado)) {
            $listId = $row['id'];
        } else {
            $sql = "INSERT INTO " . LIST_TABLE . " (name, created, updated) VALUES ('" . $nomeLista . "', NOW(), NOW())";
            if (!DatabaseHelper::executeQuery($sql)) {
                error_log("Error creating the list of cabins.");
                DatabaseHelper::rollback();
                return false;
            }
            $listId = DatabaseHelper::getLastInsertId();
        }

        $sqlCabines = "SELECT * FROM " . SINCRO_CABINET_TABLE;
        $resultadoCabines = DatabaseHelper::executeQuery($sqlCabines);
        while ($cabine = DatabaseHelper::fetchRow($resultadoCabines)) {
            // error_log("Cabin: " . $cabine['model']);
            $displayValue = implode(';', array_values($cabine));

            $checkItemSql = "SELECT 1 FROM " . LIST_ITEMS_TABLE . " WHERE list_id = '$listId' AND value = '" . addslashes($displayValue) . "'";
            $itemExists = DatabaseHelper::executeQuery($checkItemSql);
            if (DatabaseHelper::fetchRow($itemExists)) {
                // error_log("Item already exists and will not be added again: " . $displayValue);
                continue;
            }

            $itemSql = "INSERT INTO " . LIST_ITEMS_TABLE . " (list_id, value, status, sort, properties) 
                        VALUES ('$listId', '" . addslashes($displayValue) . "', 1, 0, '')";
            if (!DatabaseHelper::executeQuery($itemSql)) {
                error_log("Error adding item to the list: " . $displayValue);
                continue;
            }
        }

        DatabaseHelper::commit();
        // error_log("List of cabins created and populated successfully.");
        return true;
    }

    /**
     * Removes the radar list from list table and list items.
     *
     * @return bool True if the list and its items were removed successfully, false otherwise.
     */
    public static function removerListaCabines()
    {
        DatabaseHelper::startTransaction();

        $nomeLista = "Lista de Cabines";
        $sqlLista = "SELECT id FROM " . LIST_TABLE . " WHERE name = '" . $nomeLista . "'";
        $resultadoLista = DatabaseHelper::executeQuery($sqlLista);
        if ($lista = DatabaseHelper::fetchRow($resultadoLista)) {
            $listId = $lista['id'];

            $sqlRemoveItems = "DELETE FROM " . LIST_ITEMS_TABLE . " WHERE list_id = '$listId'";
            if (!DatabaseHelper::executeQuery($sqlRemoveItems)) {
                error_log("Error removing items from the list of cabins.");
                DatabaseHelper::rollback();
                return false;
            }

            $sqlRemoveLista = "DELETE FROM " . LIST_TABLE . " WHERE id = '$listId'";
            if (!DatabaseHelper::executeQuery($sqlRemoveLista)) {
                error_log("Error removing the list of cabins.");
                DatabaseHelper::rollback();
                return false;
            }

            DatabaseHelper::commit();
            // error_log("List of cabins and its items were removed successfully.");
            return true;
        } else {
            error_log("List of cabins not found.");
            DatabaseHelper::rollback();
            return false;
        }
    }

    /**
     * Retrieves the ID of the radar list.
     *
     * @return int|false The ID of the radar list, or false if not found.
     */
    public static function obterIdListaRadares()
    {
        $sql = "SELECT id FROM " . LIST_TABLE . " WHERE name='Lista de Cabines'";
        $result = DatabaseHelper::executeQuery($sql);
        if ($row = DatabaseHelper::fetchRow($result)) {
            return $row['id'];
        } else {
            error_log("List of Cabines not found in the system.");
            return false;
        }
    }


     /**
     * Retrieves the form options from the database.
     *
     * @return array The form options as an associative array.
     */
    public static function getFormOptions() {
        $options = array();
        $sql = "SELECT id, title FROM " . FORM_TABLE;
        $result = DatabaseHelper::executeQuery($sql);
        if ($result && DatabaseHelper::numRows($result) > 0) {
            while ($row = DatabaseHelper::fetchRow($result)) {
                $options[$row['id']] = $row['title'];
            }
        }
        return $options;
    }

      /**
     * Fetch data based on table name, column name, and filter value.
     *
     * @param string $tableName The name of the table.
     * @param string $columnName The name of the column.
     * @param string $filterValue The filter value for the query.
     * @return array The fetched data.
     */
    public static function fetchData($tableName, $columnName, $filterValue) {
        $query = "SELECT DISTINCT `$columnName` FROM `$tableName` WHERE `district` = '$filterValue' ORDER BY `$columnName`";
        $result = DatabaseHelper::executeQuery($query);
        $values = [];

        while ($row = DatabaseHelper::fetchRow($result)) {
            $values[] = $row[$columnName];
        }

        return $values;
    }

       /**
     * Updates the company information in the "Company Information" form.
     *
     * @param string $companyName New company name.
     * @param string $website New website.
     * @param string $phoneNumber New phone number.
     * @param string $address New address.
     * @return bool Returns true if the update was successful, false otherwise.
     */
    public static function updateCompanyInformation($companyName, $website, $phoneNumber, $address)
    {
        $formName = 'Company Information';
        
        // Get the ID of the "Company Information" form
        $formId = self::getFormIdByName($formName);
        if (!$formId) {
            error_log("Form 'Company Information' not found.");
            return false;
        }

        // Get the IDs of the relevant fields
        $fieldIds = self::getFieldIdsByFormId($formId, ['name', 'website', 'phone', 'address']);
        if (count($fieldIds) < 4) {
            error_log("Not all fields were found in the 'Company Information' form.");
            return false;
        }

        // Update the values in FORM_ENTRY_VALUES_TABLE
        $data = [
            'name' => $companyName,
            'website' => $website,
            'phone' => $phoneNumber,
            'address' => $address
        ];

        foreach ($data as $fieldName => $value) {
            if (!self::updateFormEntryValue($fieldIds[$fieldName], $value)) {
                error_log("Error updating the '$fieldName' field.");
                return false;
            }
        }

        return true;
    }


    /**
     * Clears the company information in the "Company Information" form,
     * except for the "Company Name" field, which will be set to "Helpdesk" if empty.
     *
     * @return bool Returns true if the operation was successful, false otherwise.
     */
    public static function clearCompanyInformation()
    {
        $formName = 'Company Information';

        // Get the ID of the "Company Information" form
        $formId = self::getFormIdByName($formName);
        if (!$formId) {
            error_log("Form 'Company Information' not found.");
            return false;
        }

        // Get the IDs of the relevant fields
        $fieldIds = self::getFieldIdsByFormId($formId, ['name', 'website', 'phone', 'address']);
        if (count($fieldIds) < 4) {
            error_log("Not all fields were found in the 'Company Information' form.");
            return false;
        }

        // Ensure "Company Name" is not empty
        if (!self::ensureCompanyNameIsNotNull($fieldIds['name'])) {
            error_log("Error setting the company name.");
            return false;
        }

        // Clear the "website", "phone", and "address" fields
        $fieldsToClear = ['website', 'phone', 'address'];

        foreach ($fieldsToClear as $fieldName) {
            if (!self::clearFormEntryValue($fieldIds[$fieldName])) {
                error_log("Error clearing the '$fieldName' field.");
                return false;
            }
        }

        return true;
    }

    /**
     * Ensures that the "Company Name" field is not empty, setting it to "Helpdesk" if necessary.
     *
     * @param int $fieldId ID of the "Company Name" field.
     * @return bool Returns true if the operation was successful, false otherwise.
     */
    private static function ensureCompanyNameIsNotNull($fieldId)
    {
        // Get the entry_ids related to the field_id
        $sql = "SELECT entry_id, value FROM " . FORM_ENTRY_VALUES_TABLE . " WHERE field_id = " . intval($fieldId);
        $result = DatabaseHelper::executeQuery($sql);

        $updateQueries = [];
        while ($row = DatabaseHelper::fetchRow($result)) {
            if (empty($row['value'])) {
                $updateQueries[] = "UPDATE " . FORM_ENTRY_VALUES_TABLE . " SET value = 'Helpdesk' WHERE entry_id = " . intval($row['entry_id']) . " AND field_id = " . intval($fieldId);
            }
        }

        foreach ($updateQueries as $query) {
            if (!DatabaseHelper::executeQuery($query)) {
                return false;
            }
        }

        // Confirm the "Helpdesk" update took effect
        $sqlCheck = "SELECT entry_id, value FROM " . FORM_ENTRY_VALUES_TABLE . " WHERE field_id = " . intval($fieldId);
        $resultCheck = DatabaseHelper::executeQuery($sqlCheck);
        while ($row = DatabaseHelper::fetchRow($resultCheck)) {
            if (empty($row['value']) || $row['value'] !== 'Helpdesk') {
                $updateQuery = "UPDATE " . FORM_ENTRY_VALUES_TABLE . " SET value = 'Helpdesk' WHERE entry_id = " . intval($row['entry_id']) . " AND field_id = " . intval($fieldId);
                if (!DatabaseHelper::executeQuery($updateQuery)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Clears the value of a specific field in ost_form_entry_values.
     *
     * @param int $fieldId ID of the field.
     * @return bool Returns true if the operation was successful, false otherwise.
     */
    private static function clearFormEntryValue($fieldId)
    {
        // Get the entry_ids related to the field_id
        $sql = "SELECT entry_id FROM " . FORM_ENTRY_VALUES_TABLE . " WHERE field_id = " . intval($fieldId);
        $result = DatabaseHelper::executeQuery($sql);

        $entryIds = [];
        while ($row = DatabaseHelper::fetchRow($result)) {
            $entryIds[] = $row['entry_id'];
        }

        // Clear the values in the found entry_ids
        foreach ($entryIds as $entryId) {
            $sqlUpdate = "UPDATE " . FORM_ENTRY_VALUES_TABLE . " SET value = '' WHERE entry_id = " . intval($entryId) . " AND field_id = " . intval($fieldId);
            if (!DatabaseHelper::executeQuery($sqlUpdate)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Get the ID of the form by name.
     *
     * @param string $formName Name of the form.
     * @return int|false Returns the ID of the form or false if not found.
     */
    private static function getFormIdByName($formName)
    {
        $sql = "SELECT id FROM " . FORM_TABLE . " WHERE title = '" . addslashes($formName) . "'";
        $result = DatabaseHelper::executeQuery($sql);
        if ($row = DatabaseHelper::fetchRow($result)) {
            return $row['id'];
        }
        return false;
    }

    /**
     * Get the IDs of the fields by form ID and field names.
     *
     * @param int $formId ID of the form.
     * @param array $fieldNames Names of the fields.
     * @return array Returns an array with the field IDs indexed by field names.
     */
    private static function getFieldIdsByFormId($formId, $fieldNames)
    {
        $fieldIds = [];
        $sql = "SELECT id, name FROM " . FORM_FIELD_TABLE . " WHERE form_id = " . intval($formId) . " AND name IN ('" . implode("','", array_map('addslashes', $fieldNames)) . "')";
        $result = DatabaseHelper::executeQuery($sql);

        while ($row = DatabaseHelper::fetchRow($result)) {
            $fieldIds[$row['name']] = $row['id'];
        }

        return $fieldIds;
    }

    /**
     * Update the value of a specific field in ost_form_entry_values.
     *
     * @param int $fieldId ID of the field.
     * @param string $value New value for the field.
     * @return bool Returns true if the update was successful, false otherwise.
     */
    private static function updateFormEntryValue($fieldId, $value)
    {
        // Get the entry_ids related to the field_id
        $sql = "SELECT entry_id FROM " . FORM_ENTRY_VALUES_TABLE . " WHERE field_id = " . intval($fieldId);
        $result = DatabaseHelper::executeQuery($sql);

        $entryIds = [];
        while ($row = DatabaseHelper::fetchRow($result)) {
            $entryIds[] = $row['entry_id'];
        }

        // Update the values in the found entry_ids
        foreach ($entryIds as $entryId) {
            $sqlUpdate = "UPDATE " . FORM_ENTRY_VALUES_TABLE . " SET value = '" . addslashes($value) . "' WHERE entry_id = " . intval($entryId) . " AND field_id = " . intval($fieldId);
            if (!DatabaseHelper::executeQuery($sqlUpdate)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Get the ID of the uploaded logo based on the file name.
     *
     * @param string $fileName The name of the logo file.
     * @return int|null The logo ID or null if not found.
     */
    public static function getUploadedLogoId($fileName)
    {
        // Log the file name being searched
        // error_log("Searching for logo file name: " . $fileName);

        // Properly escape the file name to prevent SQL injection
        $escapedFileName = addslashes($fileName);

        // Query the database to get the ID of the file with the specified name
        $sql = "SELECT `id` FROM " . FILE_TABLE . " WHERE `name` = '$escapedFileName'";
        // error_log("SQL Query: " . $sql);

        $res = DatabaseHelper::executeQuery($sql);
        if ($res && DatabaseHelper::numRows($res)) {
            $row = DatabaseHelper::fetchRow($res);
            // error_log("Found logo ID: " . $row['id']);
            return $row['id'];
        } else {
            error_log("Logo ID not found for file name: " . $fileName);
        }
        return null;
    }

    public static function getUploadedBackdropId($fileName)
    {
        // Log the file name being searched
        // error_log("Searching for backdrop file name: " . $fileName);

        // Properly escape the file name to prevent SQL injection
        $escapedFileName = addslashes($fileName);

        // Query the database to get the ID of the file with the specified name
        $sql = "SELECT `id` FROM " . FILE_TABLE . " WHERE `name` = '$escapedFileName'";
        // error_log("SQL Query: " . $sql);

        $res = DatabaseHelper::executeQuery($sql);
        if ($res && DatabaseHelper::numRows($res)) {
            $row = DatabaseHelper::fetchRow($res);
            // error_log("Found backdrop ID: " . $row['id']);
            return $row['id'];
        } else {
            error_log("Backdrop ID not found for file name: " . $fileName);
        }
        return null;
    }


    /**
     * Remove the logo by its ID.
     *
     * @param int $logoId The ID of the logo to remove.
     * @param string &$error Error message if deletion fails.
     * @return bool True if deletion successful, false otherwise.
     */
    public static function removeLogoById($logoId, &$error)
    {
        if (!$logoId) {
            $error = 'No logo ID provided.';
            return false;
        }

        // Remove the logo directly from the database
        $sql = "DELETE FROM " . FILE_TABLE . " WHERE `id` = " . intval($logoId);
        if (DatabaseHelper::executeQuery($sql)) {
            // error_log('Logo with ID ' . $logoId . ' deleted successfully.');
            return true;
        } else {
            $error = 'Failed to delete logo with ID: ' . $logoId;
            return false;
        }
    }

    /**
     * Remove the backdrop by its ID.
     *
     * @param int $backdropId The ID of the backdrop to remove.
     * @param string &$error Error message if deletion fails.
     * @return bool True if deletion successful, false otherwise.
     */
    public static function removeBackdropById($backdropId, &$error)
    {
        if (!$backdropId) {
            $error = 'No backdrop ID provided.';
            return false;
        }

        // Remove the backdrop directly from the database
        $sql = "DELETE FROM " . FILE_TABLE . " WHERE `id` = " . intval($backdropId);
        if (DatabaseHelper::executeQuery($sql)) {
            error_log('Backdrop with ID ' . $backdropId . ' deleted successfully.');
            return true;
        } else {
            $error = 'Failed to delete backdrop with ID: ' . $backdropId;
            return false;
        }
    }

    /**
     * Get the ID of the uploaded file based on the file name.
     *
     * @param string $fileName The name of the file.
     * @return int|null The file ID or null if not found.
     */
    public static function getUploadedFileId($fileName)
    {
        // Log the file name being searched
        // error_log("Searching for file name: " . $fileName);

        // Properly escape the file name to prevent SQL injection
        $escapedFileName = addslashes($fileName);

        // Query the database to get the ID of the file with the specified name
        $sql = "SELECT `id` FROM " . FILE_TABLE . " WHERE `name` = '$escapedFileName'";
        // error_log("SQL Query: " . $sql);

        $res = DatabaseHelper::executeQuery($sql);
        if ($res && DatabaseHelper::numRows($res)) {
            $row = DatabaseHelper::fetchRow($res);
            // error_log("Found file ID: " . $row['id']);
            return $row['id'];
        } else {
            error_log("File ID not found for file name: " . $fileName);
        }
        return null;
    }

    /**
     * Remove the file by its ID.
     *
     * @param int $fileId The ID of the file to remove.
     * @param string &$error Error message if deletion fails.
     * @return bool True if deletion successful, false otherwise.
     */
    public static function removeFileById($fileId, &$error)
    {
        if (!$fileId) {
            $error = 'No file ID provided.';
            return false;
        }

        // Remove the file directly from the database
        $sql = "DELETE FROM " . FILE_TABLE . " WHERE `id` = " . intval($fileId);
        if (DatabaseHelper::executeQuery($sql)) {
            // error_log('File with ID ' . $fileId . ' deleted successfully.');
            return true;
        } else {
            $error = 'Failed to delete file with ID: ' . $fileId;
            return false;
        }
    }

    /**
     * Get the ID of the uploaded image based on the file name.
     *
     * @param string $fileName The name of the image file.
     * @return int|null The image ID or null if not found.
     */
    public static function removeConfigElementByKey($key) {
        $sql = "DELETE FROM " . CONFIG_TABLE . " WHERE `key` = '" . addslashes($key) . "'";
        
        if (db_query($sql)) {
            // error_log("Elemento com chave '$key' removido com sucesso da tabela " . CONFIG_TABLE . ".");
            return true;
        } else {
            error_log("Erro ao remover elemento com chave '$key' da tabela " . CONFIG_TABLE . ".");
            return false;
        }
    }

    
    /**
     * Deletes all rows from the FORM_TABLE where the title is FIELD_LISTA_CABINES . ' Properties'.
     *
     * @return bool Returns true if the rows were successfully deleted, false otherwise.
     */
    public static function deleteFieldListaCabinesProperties()
    {
        // Construct the title to be deleted
        $titleToDelete = FIELD_LISTA_CABINES . ' Properties';

        // Construct the SQL query to delete the rows
        $sql = "DELETE FROM " . FORM_TABLE . " WHERE title = '" . addslashes($titleToDelete) . "'";

        // Execute the query
        if (DatabaseHelper::executeQuery($sql)) {
            // error_log("Rows with title '$titleToDelete' successfully deleted from table " . FORM_TABLE);
            return true;
        } else {
            error_log("Error deleting rows with title '$titleToDelete' from table " . FORM_TABLE);
            return false;
        }
    }


}

?>
