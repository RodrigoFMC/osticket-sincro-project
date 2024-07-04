<?php
define('PRJ_LEIM_SINCRO_PLUGIN_DIR', __DIR__ . '/');
define('PRJ_LEIM_SINCRO_LOG_FILE', PRJ_LEIM_SINCRO_PLUGIN_DIR . 'logs/plugin_logs.log');
require_once 'Constants.php';


class RadarDetailsConfig extends PluginConfig {

    /**
     * Retrieves the configuration options for the plugin.
     *
     * @return array The configuration options as an associative array.
     */
    public function getOptions() {
        // Get form options using PluginDataBaseManager
        $form_options = PluginDataBaseManager::getFormOptions();
        $form_options = ['' => 'None'] + $form_options;

        return array(
            'selected_form_id' => new ChoiceField([
                'id' => 'selected_form_id',
                'label' => 'Select Form',
                'choices' => $form_options,
                'configuration' => array(
                    'prompt' => 'Choose one form to add fields to or None to not select any'
                )
            ]),
            'save_on_deactivate' => new BooleanField([
                'id' => 'save_on_deactivate',
                'label' => 'Save Data on Deactivation',
                'configuration' => array(
                    'desc' => 'Enable this to save form data to a file when the plugin is deactivated.'
                )
            ]),
        );
    }

    /**
     * Checks if the necessary write permissions are available.
     *
     * @param array $errors An array to store any validation errors.
     * @return bool True if the write permissions are available, false otherwise.
     */
    private function checkWritePermissions(&$errors) {
        $test_file = INCLUDE_DIR . 'test_write_permissions.tmp';
        $test_content = "test";

        // Try to create the test file
        if (file_put_contents($test_file, $test_content) === false) {
            $errors['err'] = 'Write permissions are not available in the include directory. Please ensure the web server has write access to the directory.';
            return false;
        }
    
        // Try to read the test file
        if (file_get_contents($test_file) !== $test_content) {
            $errors['err'] = 'Write permissions are not fully functional in the include directory. Please ensure the web server can read and write to the directory.';
            unlink($test_file);
            return false;
        }
    
        // Try to delete the test file
        if (!unlink($test_file)) {
            $errors['err'] = 'The plugin cannot delete files in the include directory. Please ensure the web server has delete permissions for the directory.';
            return false;
        }
    
        return true;
    }
    

    /**
     * Performs pre-save validation and updates the configuration.
     *
     * @param array $config The configuration values to be saved.
     * @param array $errors An array to store any validation errors.
     * @return bool True if the configuration is valid and saved successfully, false otherwise.
     */
    public function pre_save(&$config, &$errors) {
        global $msg;

        // Check if write permissions are available
        if (!$this->checkWritePermissions($errors)) {
            return false;
        }

        $last_selected = $this->get('last_selected_form_id', '');

        // Forçar ambos os IDs a serem tratados como strings para evitar problemas
        $last_selected_str = (string) $last_selected;
        $selected_form_id_str = (string) $config['selected_form_id'];

        // $this->logger("Last selected form ID: " . ($last_selected_str ?: "none"));
        // $this->logger("Current selected form ID: " . $selected_form_id_str);

        if (!empty($last_selected_str) && $selected_form_id_str !== $last_selected_str) {
            $this->logger("Attempt to change form ID from $last_selected_str to $selected_form_id_str");
            $errors['err'] = "Cannot change the form selection once set. Please create a new instance if you want to select a different form.";
            return false;
        }

        if (empty($config['selected_form_id'])) {
            $this->logger("No form selected.");
            $errors['err'] = 'You must select a form.';
            return false;
        }

        if (empty($last_selected_str) || $selected_form_id_str === $last_selected_str) {
            $this->set('last_selected_form_id', $config['selected_form_id']);
            $msg = 'Configuration updated successfully.';
            return true;
        }

        // $this->logger("No changes made to the configuration.");
        return false;
    }

    function logger($message) {
        $timestamp = date('Y-m-d H:i:s');
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($backtrace[1]) ? $backtrace[1] : null;

        $file = $caller ? basename($caller['file']) : "unknown file";
        $line = $caller ? $caller['line'] : "unknown line";
        $logMessage = sprintf("[%s] %s [File: %s] [Line: %s]", $timestamp, $message, $file, $line);

        error_log($logMessage . "\n", 3, PRJ_LEIM_SINCRO_LOG_FILE);
        error_log($logMessage . "\n");

    }

    /**
     * Performs post-save validation and updates the configuration.
     *
     * @param array $config The configuration values to be saved.
     * @param array $errors An array to store any validation errors.
     * @return bool True if the configuration is valid and saved successfully, false otherwise.
     */
    public function post_save(&$config, &$errors) {
        global $msg;

        if (isset($config['save_on_deactivate'])) {
            $this->set('save_on_deactivate', $config['save_on_deactivate']);
            $msg = 'Save on deactivate setting updated.';
        }

        return true;
    }

}

