<?php

/**
 * @file classes/submission/SubmissionKeyword.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionKeyword
 * @ingroup submission
 *
 * @see SubmissionKeywordEntryDAO
 *
 * @brief Basic class describing a submission keyword
 */

class SubmissionKeyword extends \PKP\controlledVocab\ControlledVocabEntry
{
    //
    // Get/set methods
    //

    /**
     * Get the keyword
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->getData('submissionKeyword');
    }

    /**
     * Set the keyword text
     *
     * @param keyword string
     * @param locale string
     */
    public function setKeyword($keyword, $locale)
    {
        $this->setData('submissionKeyword', $keyword, $locale);
    }

    public function getLocaleMetadataFieldNames()
    {
        return ['submissionKeyword'];
    }
}
