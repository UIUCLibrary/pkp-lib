<?php

/**
 * @file classes/filter/EmailFilterSetting.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class EmailFilterSetting
 * @ingroup classes_filter
 *
 * @brief Class that describes a configurable filter setting which
 *  must be an email.
 */

namespace PKP\filter;

use PKP\form\validation\FormValidator;

class EmailFilterSetting extends FilterSetting
{
    /**
     * Constructor
     *
     * @param $name string
     * @param $displayName string
     * @param $validationMessage string
     * @param $required boolean
     */
    public function __construct($name, $displayName, $validationMessage, $required = FormValidator::FORM_VALIDATOR_REQUIRED_VALUE)
    {
        parent::__construct($name, $displayName, $validationMessage, $required);
    }

    //
    // Implement abstract template methods from FilterSetting
    //
    /**
     * @see FilterSetting::getCheck()
     */
    public function &getCheck(&$form)
    {
        $check = new \PKP\form\validation\FormValidatorEmail($form, $this->getName(), $this->getRequired(), $this->getValidationMessage());
        return $check;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\PKP\filter\EmailFilterSetting', '\EmailFilterSetting');
}
