<?php

/**
 * @file classes/user/InterestManager.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class InterestManager
 * @ingroup user
 *
 * @see InterestDAO
 * @brief Handle user interest functions.
 */

namespace PKP\user;

use PKP\db\DAORegistry;

class InterestManager
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Get all interests for all users in the system
     *
     * @param $filter string
     *
     * @return array
     */
    public function getAllInterests($filter = null)
    {
        $interestDao = DAORegistry::getDAO('InterestDAO'); /** @var InterestDAO $interestDao */
        $interests = $interestDao->getAllInterests($filter);

        $interestReturner = [];
        while ($interest = $interests->next()) {
            $interestReturner[] = $interest->getInterest();
        }

        return $interestReturner;
    }

    /**
     * Get user reviewing interests. (Cached in memory for batch fetches.)
     *
     * @param $user User
     *
     * @return array
     */
    public function getInterestsForUser($user)
    {
        static $interestsCache = [];
        $interests = [];

        $interestDao = DAORegistry::getDAO('InterestDAO'); /** @var InterestDAO $interestDao */
        $interestEntryDao = DAORegistry::getDAO('InterestEntryDAO'); /** @var InterestEntryDAO $interestEntryDao */
        $controlledVocab = $interestDao->build();
        foreach ($interestDao->getUserInterestIds($user->getId()) as $interestEntryId) {
            if (!isset($interestsCache[$interestEntryId])) {
                $interestsCache[$interestEntryId] = $interestEntryDao->getById(
                    $interestEntryId,
                    $controlledVocab->getId()
                );
            }
            if (isset($interestsCache[$interestEntryId])) {
                $interests[] = $interestsCache[$interestEntryId]->getInterest();
            }
        }

        return $interests;
    }

    /**
     * Returns a comma separated string of a user's interests
     *
     * @param $user User
     *
     * @return string
     */
    public function getInterestsString($user)
    {
        $interests = $this->getInterestsForUser($user);

        return implode(', ', $interests);
    }

    /**
     * Set a user's interests
     *
     * @param $user User
     * @param $interests mixed
     */
    public function setInterestsForUser($user, $interests)
    {
        $interestDao = DAORegistry::getDAO('InterestDAO'); /** @var InterestDAO $interestDao */
        $interests = is_array($interests) ? $interests : (empty($interests) ? null : explode(',', $interests));
        $interestDao->setUserInterests($interests, $user->getId());
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\PKP\user\InterestManager', '\InterestManager');
}
