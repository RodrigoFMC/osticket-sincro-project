<?php

define('OST_LIST_TABLE', TABLE_PREFIX . 'list');
define('OST_LIST_ITEMS_TABLE', TABLE_PREFIX . 'list_items');

/**
 * Represents a Campo object.
 */
class Campo {
    
    /**
     * The type of the field.
     * @var string
     */
    private $fieldType;

    /**
     * The label of the field.
     * @var string
     */
    private $fieldLabel;

    /**
     * The variable name of the field.
     * @var string
     */
    private $fieldVariable;

    /**
     * The default value of the field.
     * @var string
     */
    private $fieldDefaultValue;

    /**
     * The flags of the field.
     * @var int
     */
    private $fieldFlags;

    /**
     * The configuration of the field.
     * @var array
     */
    private $fieldConfiguration;

    /**
     * The form field table name.
     * @var string
     */
    private $fieldFormField = TABLE_PREFIX . 'form_field';

    /**
     * The form entry values table name.
     * @var string
     */
    private $fieldFormEntryValues = TABLE_PREFIX . 'form_entry_values';

    /**
     * The hint of the field.
     * @var string
     */
    private $fieldHint;

    /**
     * Creates a new Campo object.
     * 
     * @param string $fieldType The type of the field.
     * @param string $fieldLabel The label of the field.
     * @param string $fieldVariable The variable name of the field.
     * @param string $fieldDefaultValue The default value of the field.
     * @param int $fieldFlags The flags of the field.
     * @param array $fieldConfiguration The configuration of the field.
     * @param string $fieldHint The hint of the field.
     */
    public function __construct($fieldType, $fieldLabel, $fieldVariable, $fieldDefaultValue, $fieldFlags, $fieldConfiguration, $fieldHint) {
        $this->fieldType = $fieldType;
        $this->fieldLabel = $fieldLabel;
        $this->fieldVariable = $fieldVariable;
        $this->fieldDefaultValue = $fieldDefaultValue;
        $this->fieldFlags = $fieldFlags;
        $this->fieldConfiguration = $fieldConfiguration;
        $this->fieldHint = $fieldHint;
    }

    /**
     * Gets the type of the field.
     * 
     * @return string The type of the field.
     */
    public function getFieldType() {
        return $this->fieldType;
    }

    /**
     * Gets the label of the field.
     * 
     * @return string The label of the field.
     */
    public function getFieldLabel() {
        return $this->fieldLabel;
    }

    /**
     * Gets the variable name of the field.
     * 
     * @return string The variable name of the field.
     */
    public function getFieldVariable() {
        return $this->fieldVariable;
    }

    /**
     * Gets the default value of the field.
     * 
     * @return string The default value of the field.
     */
    public function getFieldDefaultValue() {
        return $this->fieldDefaultValue;
    }

    /**
     * Gets the flags of the field.
     * 
     * @return int The flags of the field.
     */
    public function getFieldFlags() {
        return $this->fieldFlags;
    }

    /**
     * Gets the configuration of the field.
     * 
     * @return array The configuration of the field.
     */
    public function getFieldConfiguration() {
        return $this->fieldConfiguration;
    }

    /**
     * Gets the form field table name.
     * 
     * @return string The form field table name.
     */
    public function getFieldFormField() {
        return $this->fieldFormField;
    }

    /**
     * Gets the form entry values table name.
     * 
     * @return string The form entry values table name.
     */
    public function getFieldFormEntryValues() {
        return $this->fieldFormEntryValues;
    }

    /**
     * Gets the hint of the field.
     * 
     * @return string The hint of the field.
     */
    public function getFieldHint() {
        return $this->fieldHint;
    }

    /**
     * Sets the configuration of the field.
     * 
     * @param array $fieldConfiguration The configuration of the field.
     */
    public function setFieldConfiguration($fieldConfiguration) {
        $this->fieldConfiguration = $fieldConfiguration;
    }

}
