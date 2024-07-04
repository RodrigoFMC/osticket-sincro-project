<?php

/*********************************************************************
    RadarDetailsPlugin.php

    FILEPATH: /c:/xampp/htdocs/osTicket/upload/include/plugins/PRJ_LEIM_SINCRO/RadarDetailsPlugin.php
 
    This file contains the implementation of the RadarDetailsPlugin class, which is a plugin for the osTicket system.
    The plugin provides functionality related to radar details and includes methods for bootstrapping, enabling/disabling the plugin,
    registering dispatches for staff and clients, handling instance-specific actions, and more.


    class.dispatcher.php ajax.php(scp e upload) ajax.forms.php header.inc.php class.forms.php class.file.php
    ticket-open.inc.php open.inc.php
    TODO escrever documento que permite perceber como o plugin pode ser utilizado com o prj 15
    TODO preenchimento atraves da api temos que ver porque ele precisa do id do item da lista 
    link de teste ajax: http://localhost/osTicket/upload/scp/ajax.php/ajax-options/getOptions/ost_SINCRO_cabinet/address/Porto

 *********************************************************************/

require_once INCLUDE_DIR . 'class.plugin.php';
require_once INCLUDE_DIR . 'class.signal.php';
require_once INCLUDE_DIR . 'class.app.php';
require_once INCLUDE_DIR . 'class.dynamic_forms.php';
require_once INCLUDE_DIR . 'class.osticket.php';
require_once 'Config.php';
require_once 'Campo.php';
require_once 'PluginDataBaseManager.php';
require_once 'DatabaseHelper.php';
require_once 'Constants.php';

class RadarDetailsPlugin extends Plugin
{
    /**
     * The name of the plugin.
     */
    public const PLUGIN_NAME = PLUGIN_NAME;

    /**
     * The configuration class for the plugin.
     *
     * @var string
     */
    var $config_class = 'RadarDetailsConfig';

    /**
     * The object that holds the custom fields for the plugin.
     *
     * @var mixed
     */
    private $campos_obj;

    /**
     * Initializes the plugin by registering dispatches for staff and clients, and performing instance-specific actions.
     */
    function bootstrap()
    {
        // $this->logger(MSG_PLUGIN_BOOTSTRAP);
        Signal::connect('ajax.scp', [$this, 'registerDispatchsStaff']);
        Signal::connect('ajax.client', [$this, 'registerDispatchsClient']);
        $this->makeInstanceSpecificActions();
        $this->checkInactiveInstances();
    }

    /**
     * Performs instance-specific actions for the plugin, such as restoring plugin info from backup and adding necessary fields.
     *
     * @return bool True if the actions were performed successfully, false otherwise.
     */
    function makeInstanceSpecificActions()
    {
        if (parent::isActive()) {
            // $this->logger("Instance specific actions...");
            if ($this->getConfig()->get('save_on_deactivate') && $this->firstRun()) {
                // $this->logger(MSG_RESTORING_INFO);
                PluginDataBaseManager::restorePluginInfoFromBackup($this->createCamposObj());
                $this->configureFirstRun();
            }
            PluginDataBaseManager::adicionarCamposNecessarios($this->getSelectedFormId(), $this->createCamposObj());
        }
        return true;
    }

    /**
     * Checks for inactive instances and handles them by cleaning up instance data.
     */
    private function checkInactiveInstances()
    {
        $instances = $this->getInstances();
        foreach ($instances as $instance) {
            if (!$instance->isEnabled()) {
                $this->handleDeactivatedInstance($instance);
            }
        }
    }

    /**
     * Handles a deactivated instance by cleaning up its data.
     *
     * @param mixed $instance The instance to handle.
     */
    private function handleDeactivatedInstance($instance)
    {
        // $this->logger("Instância desativada: " . $instance->getId());
        $this->cleanUpInstanceData($instance);
    }

    /**
     * Cleans up the data of a deactivated instance.
     *
     * @param mixed $instance The instance to clean up.
     */
    private function cleanUpInstanceData($instance)
    {
        $form_id = $this->getSelectedFormIdByInstance($instance);
        if ($instance->getConfig()->get('save_on_deactivate')) {
            PluginDataBaseManager::keepPluginInfo($this->createCamposObj());
            // $this->logger(MSG_SAVING_DATA . $form_id);
        }
        $result = PluginDataBaseManager::removerCamposNecessarios($form_id, $this->createCamposObj());
        if ($result) {
            // $this->logger("Campos removidos com sucesso para o form_id: " . $form_id);
        } else {
            $this->logger("Falha ao remover campos para o form_id: " . $form_id);
        }
    }

    /**
     * Checks if the plugin is active.
     *
     * @return bool True if the plugin is active, false otherwise.
     */
    function isActive()
    {
        // $this->logger('Checking if plugin is active.');
        $currentlyActive = parent::isActive();
        $previouslyActive = (int)$this->getCurrentActiveStatePlugin();
        $currentlyActive = (int)$currentlyActive;

        if ($previouslyActive !== $currentlyActive) {
            // $this->logger('Activation state changed.');
            $this->handleActivationChange($currentlyActive);
            $this->setCurrentActiveStatePlugin($currentlyActive);
        }

        return $currentlyActive;
    }

    /**
     * Handles a change in the activation state of the plugin.
     *
     * @param bool $currentlyActive The current activation state of the plugin.
     */
    private function handleActivationChange($currentlyActive)
    {
        // error_log("Plugin está a ser " . ($currentlyActive ? "ativado" : "desativado") . ".");
        if ($currentlyActive) {
            $this->enablePlugin();
        } else {
            $this->disablePlugin();
        }
    }

    /**
     * Enables the plugin by executing necessary actions.
     *
     * @return bool True if the plugin was enabled successfully, false otherwise.
     */
    function enablePlugin()
    {
        if (parent::isActive()) {
            // $this->logger("Plugin is being installed plugin id " . $this->getConfig()->getId());
            PluginDataBaseManager::executeSqlFile(__DIR__ . '/scripts/01-CreateSchema.sql');
            PluginDataBaseManager::executeSqlFile(__DIR__ . '/scripts/02-Populate.sql');
            PluginDataBaseManager::criarEPopularListaCabines();
            $this->injectCodeIntoFormClass();
            $this->injectCodeIntoFiles();
            $this->uploadCustomFiles();
            $this->updateCompanyInformation();
        }
        return true;
    }

    /**
     * Disables the plugin by executing necessary actions.
     *
     * @return bool True if the plugin was disabled successfully, false otherwise.
     */
    function disablePlugin()
    {
        // $this->logger(MSG_PLUGIN_DISABLED . $this->getConfig()->getId());
        $instances = $this->getInstances();
        foreach ($instances as $instance) {
            $this->cleanUpInstanceData($instance);
        }
        PluginDataBaseManager::executeSqlFile(__DIR__ . '/scripts/03-DropSchema.sql');
        PluginDataBaseManager::removerListaCabines();
        $this->removeInjectedCodeFromFormClass();
        $this->removeInjectedCodeFromFiles();
        $this->removeUploadedFiles();
        $this->clearCompanyInformation();
        $this->resetFirstRun();
        PluginDataBaseManager::deleteFieldListaCabinesProperties();
        return true;
    }

    /**
     * Registers dispatches for staff.
     *
     * @param mixed $dispatcher The dispatcher object.
     * @param mixed $data The data associated with the dispatch.
     */
    function registerDispatchsStaff($dispatcher, $data)
    {
        // $this->logger("Starting registerDispatchs for Staff.");

        $optionsRoute = url('^/ajax-options/', patterns(
            'plugins/PRJ_LEIM_SINCRO/AjaxOptionsController.php:AjaxOptionsController',
            url_get('^getOptions/(?P<tableName>[^/]+)/(?P<columnName>[^/]+)/(?P<filterValue>[^/]+)$', 'getOptions')
        ));

        $dispatcher->append($optionsRoute);
        // $this->logger(MSG_ROUTE_REGISTERED);
    }

    /**
     * Registers dispatches for clients.
     *
     * @param mixed $dispatcher The dispatcher object.
     * @param mixed $data The data associated with the dispatch.
     */
    function registerDispatchsClient($dispatcher, $data)
    {
        // $this->logger("Starting registerDispatchs for Client.");

        $optionsRoute = url('^/ajax-options/', patterns(
            'plugins/PRJ_LEIM_SINCRO/AjaxOptionsController.php:AjaxOptionsController',
            url_get('^getOptions/(?P<tableName>[^/]+)/(?P<columnName>[^/]+)/(?P<filterValue>[^/]+)$', 'getOptions')
        ));

        $dispatcher->append($optionsRoute);
        // $this->logger(MSG_ROUTE_REGISTERED);
    }

    /**
     * Checks if it is the first run of the plugin.
     *
     * @return bool True if it is the first run, false otherwise.
     */
    function firstRun()
    {
        // $this->logger("first run id " . $this->getConfig()->getInstance()->getNamespace());
        $initialized = !$this->getConfig()->get(INITIALIZED, false);
        // error_log("firstRun retornou " . ($initialized ? "true" : "false") . ".");
        return $initialized;
    }

    /**
     * Configures the first run of the plugin.
     *
     * @return bool True if the first run was configured successfully, false otherwise.
     */
    function configureFirstRun()
    {
        // $this->logger(MSG_FIRST_RUN . $this->getConfig()->getInstance()->getId() . ".");
        $this->getConfig()->update(INITIALIZED, true);
        // $this->logger("First run configured for instance ID: " . $this->getConfig()->getInstance()->getId() . ".");
        return true;
    }

    /**
     * Resets the first run of the plugin.
     *
     * @return bool True if the first run was reset successfully, false otherwise.
     */
    function resetFirstRun()
    {
        $instances = $this->getInstances();
        foreach ($instances as $instance) {
            $ost = new OsticketConfig($instance->getNamespace());
            $ost->update(INITIALIZED, false);
        }
        return true;
    }

    /**
     * Creates the object that holds the custom fields for the plugin.
     *
     * @return mixed The object that holds the custom fields.
     */
    function createCamposObj()
    {
        $campos = [
            ['list-' . PluginDataBaseManager::obterIdListaRadares(), FIELD_LISTA_CABINES, NAME_LISTA_CABINES, '', 13057, [], FIELD_SELECT_CABINE],
            ['break', FIELD_SELECT_DEVICES, NAME_BREAK_AVARIAS, '', 13057, [], ''],
            ['bool', FIELD_CABINE, NAME_CABINE, '', 13057, ['desc' => FIELD_CABINE], FIELD_HINT],
            ['bool', FIELD_ROUTER, NAME_ROUTER, '', 13057, ['desc' => FIELD_ROUTER], FIELD_HINT],
            ['bool', FIELD_CINEMOMETRO, NAME_CINEMOMETRO, '', 13057, ['desc' => FIELD_CINEMOMETRO], FIELD_HINT],
            ['bool', FIELD_UPS, NAME_UPS, '', 13057, ['desc' => FIELD_UPS], FIELD_HINT],
            ['bool', FIELD_CAIXA, NAME_CAIXA, '', 13057, ['desc' => FIELD_CAIXA], FIELD_HINT],
            ['bool', FIELD_OUTRO, NAME_OUTRO, '', 13057, ['desc' => FIELD_OUTRO], FIELD_HINT],
            ['memo', FIELD_DESCRICAO, NAME_DESCRICAO, '', 13057, ['desc' => FIELD_DESCRICAO], FIELD_DESCREVE_AVARIA]
        ];

        $camposObj = [];
        foreach ($campos as $campo) {
            $c = new Campo($campo[0], $campo[1], $campo[2], $campo[3], $campo[4], $campo[5], $campo[6]);
            $camposObj[] = $c;
        }
        $this->campos_obj = $camposObj;
        return $camposObj;
    }

    /**
     * Gets the ID of the selected form.
     *
     * @return mixed The ID of the selected form.
     */
    function getSelectedFormId()
    {
        $config = $this->getConfig();
        return $config->get('last_selected_form_id');
    }

    /**
     * Gets the ID of the selected form for a specific instance.
     *
     * @param mixed $instance The instance for which to get the selected form ID.
     * @return mixed The ID of the selected form for the instance.
     */
    function getSelectedFormIdByInstance($instance)
    {
        $ost = new OsticketConfig($instance->getNamespace());
        return $ost->get('last_selected_form_id', null);
    }

    /**
     * Gets the current activation state of the plugin.
     *
     * @return mixed The current activation state of the plugin.
     */
    function getCurrentActiveStatePlugin()
    {
        $ost = new OsticketConfig();
        return $ost->get(SESSION_PREVIOUS_STATE_PLUGIN_PREFIX, null);
    }

    /**
     * Sets the current activation state of the plugin.
     *
     * @param mixed $state The current activation state of the plugin.
     */
    function setCurrentActiveStatePlugin($state)
    {
        $ost = new OsticketConfig();
        $ost->update(SESSION_PREVIOUS_STATE_PLUGIN_PREFIX, $state);
    }

    /**
     * Injects custom code into the osTicket form class.
     */
    function injectCodeIntoFormClass()
    {
        $filepath = INCLUDE_DIR . 'class.forms.php';
        $tag_start = CUSTOM_CODE_START;
        $tag_end = CUSTOM_CODE_END;
        $custom_code = "if (\$default === '" . NAME_LISTA_CABINES . "') return \$default;";

        if (!file_exists($filepath)) {
            $this->logger(MSG_FILE_NOT_FOUND . $filepath);
            return;
        }

        $contents = file_get_contents($filepath);
        $pattern = '/(function getFormName\(\)\s*{[^}]*\$default\s*=\s*\$this->get\(\'name\'\)\s*\?:\s*\$this->get\(\'id\'\)\s*;)/';

        if (strpos($contents, $tag_start) !== false) {
            // $this->logger(MSG_CUSTOM_CODE_ALREADY_INJECTED . $filepath);
            return;
        }

        $replacement = "$1\n        $tag_start\n        $custom_code\n        $tag_end";
        $contents = preg_replace($pattern, $replacement, $contents);

        file_put_contents($filepath, $contents);

        // $this->logger(MSG_CUSTOM_CODE_INJECTED . $filepath);
    }

    /**
     * Removes the injected custom code from the osTicket form class.
     */
    function removeInjectedCodeFromFormClass()
    {
        $filepath = INCLUDE_DIR . 'class.forms.php';
        $tag_start = CUSTOM_CODE_START;
        $tag_end = CUSTOM_CODE_END;

        if (!file_exists($filepath)) {
            $this->logger(MSG_FILE_NOT_FOUND . $filepath);
            return;
        }

        $contents = file_get_contents($filepath);

        if (strpos($contents, $tag_start) === false) {
            $this->logger(MSG_CUSTOM_CODE_NOT_FOUND . $filepath);
            return;
        }

        // Remove the custom code within the getFormName function
        $pattern = '/(\$default\s*=\s*\$this->get\(\'name\'\)\s*\?:\s*\$this->get\(\'id\'\)\s*;)(\r?\n\s*' . preg_quote($tag_start, '/') . '.*?' . preg_quote($tag_end, '/') . ')(\r?\n)/s';
        $replacement = '$1$3';
        $contents = preg_replace($pattern, $replacement, $contents);

        file_put_contents($filepath, $contents);

        // $this->logger(MSG_CUSTOM_CODE_REMOVED . $filepath);
    }

    /**
     * Injects custom code into a specific file of the osTicket system.
     */
    function injectCustomCode($filePath, $customCodePath, $tagStart, $tagEnd)
    {
        if (!file_exists($customCodePath)) {
            $this->logger(MSG_FILE_NOT_FOUND . $customCodePath);
            return;
        }

        $custom_js = file_get_contents($customCodePath);
        $custom_code = "\n        {$tagStart}\n        <script>{$custom_js}</script>\n        {$tagEnd}\n";

        if (!file_exists($filePath)) {
            $this->logger(MSG_FILE_NOT_FOUND . $filePath);
            return;
        }

        $contents = file_get_contents($filePath);

        if (strpos($contents, $tagStart) !== false) {
            // $this->logger(MSG_CUSTOM_CODE_ALREADY_INJECTED . $filePath);
            return;
        }

        $contents .= $custom_code;

        file_put_contents($filePath, $contents);
        // $this->logger(MSG_CUSTOM_CODE_INJECTED . $filePath);
    }

    /**
     * Removes the injected custom code from a specific file of the osTicket system.
     */
    function removeInjectedCode($filePath, $tagStart, $tagEnd)
    {
        if (!file_exists($filePath)) {
            $this->logger(MSG_FILE_NOT_FOUND . $filePath);
            return;
        }

        $contents = file_get_contents($filePath);

        if (strpos($contents, $tagStart) === false) {
            $this->logger(MSG_CUSTOM_CODE_NOT_FOUND . $filePath);
            return;
        }

        $pattern = "#{$tagStart}(.*?){$tagEnd}#s";
        $contents = preg_replace($pattern, '', $contents);
        file_put_contents($filePath, $contents);
        // $this->logger(MSG_CUSTOM_CODE_REMOVED . $filePath);
    }

    /**
     * Uploads custom files to the osTicket system.
     */
    function injectCodeIntoFiles()
    {
        $this->injectCustomCode(STAFFINC_DIR . 'ticket-open.inc.php', CUSTOM_JS_STAFF_PATH, '<!-- CUSTOM_CODE_START -->', '<!-- CUSTOM_CODE_END -->');
        $this->injectCustomCode(CLIENTINC_DIR . 'open.inc.php', CUSTOM_JS_CLIENT_PATH, '<!-- CUSTOM_CODE_START -->', '<!-- CUSTOM_CODE_END -->');
    }

    /**
     * Removes the injected custom code from the specified files.
     */
    function removeInjectedCodeFromFiles()
    {
        $this->removeInjectedCode(STAFFINC_DIR . 'ticket-open.inc.php', '<!-- CUSTOM_CODE_START -->', '<!-- CUSTOM_CODE_END -->');
        $this->removeInjectedCode(CLIENTINC_DIR . 'open.inc.php', '<!-- CUSTOM_CODE_START -->', '<!-- CUSTOM_CODE_END -->');
    }

    /**
     * Uploads custom files to the osTicket system.
     */
    function uploadCustomFiles()
    {
        $this->uploadCustomFile('ansr-logo.png', 'L', 'client_logo_id', 'staff_logo_id');
        $this->uploadCustomFile('ansr-backdrop.jpeg', 'B', 'staff_backdrop_id');
    }

    /**
     * Removes the uploaded custom files from the osTicket system.
     */
    function removeUploadedFiles()
    {
        $this->removeUploadedFile('ansr-logo.png', 'client_logo_id', 'staff_logo_id');
        $this->removeUploadedFile('ansr-backdrop.jpeg', 'staff_backdrop_id');
    }

    /**
     * Logs a message to the log file.
     *
     * @param string $message The message to log.
     */
    function logger($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($backtrace[1]) ? $backtrace[1] : null;

        $logMessage = sprintf("[%s] %s", $timestamp, $message);
        if ($caller) {
            $file = isset($caller['file']) ? basename($caller['file']) : "unknown file";
            $line = isset($caller['line']) ? $caller['line'] : "unknown line";
            $logMessage .= sprintf(" [File: %s] [Line: %s]", $file, $line);
        }

        error_log($logMessage . "\n", 3, LOG_FILE);
        error_log($logMessage . "\n");

    }

    /**
     * Updates the company information in the osTicket system.
     */
    function updateCompanyInformation()
    {
        $companyName = "ANSR";
        $website = "http://www.ansr.pt/";
        $phoneNumber = "21 423 6800";
        $address = "Av. do Casal de Cabanas 1, 2734-507";

        if (!PluginDataBaseManager::updateCompanyInformation($companyName, $website, $phoneNumber, $address)) {
            $this->logger("Failed to update company information.");
        } else {
            // $this->logger("Company information updated successfully.");
        }
    }

    /**
     * Clears the company information in the osTicket system.
     */
    function clearCompanyInformation()
    {
        if (!PluginDataBaseManager::clearCompanyInformation()) {
            $this->logger("Failed to clear company information.");
        } else {
            // $this->logger("Company information cleared successfully.");
        }
    }

    /**
     * Custom upload function for uploading files to the osTicket system.
     * Inspired by the AttachmentFile::upload() method from the class.file.php file.
     * Modified to allow custom file types.
     *
     * @param array $file The file to upload.
     * @param string $type The type of the file.
     * @param string $error The error message if the upload fails.
     * @return mixed The uploaded file object if successful, false otherwise.
     */
    private function customUpload($file, $type, &$error)
    {
        $allowedTypes = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG];
        $source_path = $file['tmp_name'];
        list($source_width, $source_height, $source_type) = getimagesize($source_path);

        if (!in_array($source_type, $allowedTypes)) {
            $error = 'Invalid image file type. Only GIF, JPEG, and PNG are allowed.';
            return false;
        }

        list($key, $sig) = AttachmentFile::_getKeyAndHash($file['tmp_name'], true);

        $info = array(
            'type' => $file['type'],
            'filetype' => $type,
            'size' => $file['size'],
            'name' => $file['name'],
            'key' => $key,
            'signature' => $sig,
            'tmp_name' => $file['tmp_name'],
        );

        return AttachmentFile::create($info, $type, true);
    }

    /**
     * Removes an uploaded file from the osTicket system.
     *
     * @param string $fileName The name of the file to remove.
     * @param mixed ...$configKeys The configuration keys to update after removing the file.
     */
    function removeUploadedFile($fileName, ...$configKeys)
    {
        $fileId = PluginDataBaseManager::getUploadedFileId($fileName);
        if ($fileId) {
            $error = false;
            if (!PluginDataBaseManager::removeFileById($fileId, $error)) {
                $this->logger('Failed to remove ' . $fileName . ': ' . $error);
            } else {
                // $this->logger('File ' . $fileName . ' removed successfully.');
                $ostConfig = new OsticketConfig();
                foreach ($configKeys as $key) {
                    $ostConfig->set($key, 0);
                }
            }
        } else {
            $this->logger('No file ID found to remove for ' . $fileName);
        }
    }

    /**
     * Uploads a custom file to the osTicket system.
     *
     * @param string $fileName The name of the file to upload.
     * @param string $fileType The type of the file to upload.
     * @param mixed ...$configKeys The configuration keys to update after uploading the file.
     */
    function uploadCustomFile($fileName, $fileType, ...$configKeys)
    {
        $filePath = __DIR__ . '/imgs/' . $fileName;
        if (file_exists($filePath)) {
            $error = false;
            $file = array(
                'name' => $fileName,
                'type' => mime_content_type($filePath),
                'tmp_name' => $filePath,
                'error' => 0,
                'size' => filesize($filePath),
            );

            if (!$this->customUpload($file, $fileType, $error)) {
                $this->logger('Failed to upload ' . $fileName . ': ' . $error);
            } else {
                // $this->logger(ucfirst($fileType) . ' uploaded successfully.');
                $fileId = PluginDataBaseManager::getUploadedFileId($fileName);
                if ($fileId) {
                    $ostConfig = new OsticketConfig();
                    foreach ($configKeys as $key) {
                        $ostConfig->set($key, $fileId);
                    }
                }
            }
        } else {
            $this->logger(ucfirst($fileType) . ' file does not exist.');
        }
    }
}
