<?php

require_once(INCLUDE_DIR . '/class.forms.php');
require_once INCLUDE_DIR . 'class.ajax.php';
require_once 'Constants.php';
require_once 'PluginDataBaseManager.php'; 

/**
 * AjaxOptionsController class handles AJAX requests to fetch options data.
 */
class AjaxOptionsController extends AjaxController {
    /**
     * @var array $allowedTables List of tables allowed for querying.
     */
    protected $allowedTables = [SINCRO_CABINET_TABLE];

    /**
     * @var array $allowedColumns List of columns allowed for querying.
     */
    protected $allowedColumns = ['district', 'address', 'ap_af'];

    /**
     * Handles the AJAX request to fetch options based on table name, column name, and filter value.
     *
     * @param string $tableName The name of the table to query.
     * @param string $columnName The name of the column to query.
     * @param string $filterValue The filter value to apply to the query.
     */
    public function getOptions($tableName, $columnName, $filterValue) {
        // Validate the input parameters
        if (!$this->validateTableName($tableName) || !$this->validateColumnName($columnName)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input provided']);
            return;
        }

        // Fetch the data using the PluginDataBaseManager
        $results = PluginDataBaseManager::fetchData($tableName, $columnName, $filterValue);
        echo json_encode($results);
    }

    /**
     * Validates the table name against the allowed tables list.
     *
     * @param string $tableName The name of the table to validate.
     * @return bool True if the table name is valid, false otherwise.
     */
    private function validateTableName($tableName) {
        $allowedTablesLower = array_map('strtolower', $this->allowedTables);
        return in_array(strtolower($tableName), $allowedTablesLower);
    }
    

    /**
     * Validates the column name against the allowed columns list.
     *
     * @param string $columnName The name of the column to validate.
     * @return bool True if the column name is valid, false otherwise.
     */
    private function validateColumnName($columnName) {
        return in_array($columnName, $this->allowedColumns);
    }
}
?>
