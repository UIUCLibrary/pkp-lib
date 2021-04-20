<?php
/**
 * @defgroup form_validation Form Validation
 */

/**
 * @file classes/form/validation/FormValidator.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class FormValidator
 * @ingroup form_validation
 *
 * @brief Class to represent a form validation check.
 */

// The two allowed states for the type field
define('FORM_VALIDATOR_OPTIONAL_VALUE', 'optional');
define('FORM_VALIDATOR_REQUIRED_VALUE', 'required');

class FormValidator
{
    /** @var Form The Form associated with the check */
    public $_form;

    /** @var string The name of the field */
    public $_field;

    /** @var string The type of check ("required" or "optional") */
    public $_type;

    /** @var string The error message associated with a validation failure */
    public $_message;

    /** @var Validator The validator used to validate the field */
    public $_validator;

    /**
     * Constructor.
     *
     * @param $form Form the associated form
     * @param $field string the name of the associated field
     * @param $type string the type of check, either "required" or "optional"
     * @param $message string the error message for validation failures (i18n key)
     * @param $validator Validator the validator used to validate this form field (optional)
     */
    public function __construct(&$form, $field, $type, $message, $validator = null)
    {
        $this->_form = & $form;
        $this->_field = $field;
        $this->_type = $type;
        $this->_message = $message;
        $this->_validator = & $validator;

        $form->cssValidation[$field] = [];
        if ($type == FORM_VALIDATOR_REQUIRED_VALUE) {
            array_push($form->cssValidation[$field], 'required');
        }
    }


    //
    // Setters and Getters
    //
    /**
     * Get the field associated with the check.
     *
     * @return string
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * Get the error message associated with a failed validation check.
     *
     * @return string
     */
    public function getMessage()
    {
        return __($this->_message);
    }

    /**
     * Get the form associated with the check
     *
     * @return Form
     */
    public function &getForm()
    {
        return $this->_form;
    }

    /**
     * Get the validator associated with the check
     *
     * @return Validator
     */
    public function &getValidator()
    {
        return $this->_validator;
    }

    /**
     * Get the type of the validated field ('optional' or 'required')
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }


    //
    // Public methods
    //
    /**
     * Check if field value is valid.
     * Default check is that field is either optional or not empty.
     *
     * @return boolean
     */
    public function isValid()
    {
        if ($this->isEmptyAndOptional()) {
            return true;
        }

        $validator = & $this->getValidator();
        if (is_null($validator)) {
            // Default check: field must not be empty.
            $fieldValue = $this->getFieldValue();
            if (is_scalar($fieldValue)) {
                return $fieldValue !== '';
            } else {
                return $fieldValue !== [];
            }
        } else {
            // Delegate to the validator for the field value check.
            return $validator->isValid($this->getFieldValue());
        }
    }

    //
    // Protected helper methods
    //
    /**
     * Get field value
     */
    public function getFieldValue()
    {
        $form = & $this->getForm();
        $fieldValue = $form->getData($this->getField());
        if (is_null($fieldValue) || is_scalar($fieldValue)) {
            $fieldValue = trim((string)$fieldValue);
        }
        return $fieldValue;
    }

    /**
     * Check if field value is empty and optional.
     *
     * @return boolean
     */
    public function isEmptyAndOptional()
    {
        if ($this->getType() != FORM_VALIDATOR_OPTIONAL_VALUE) {
            return false;
        }

        $fieldValue = $this->getFieldValue();
        if (is_scalar($fieldValue)) {
            return $fieldValue == '';
        } else {
            return empty($fieldValue);
        }
    }
}
