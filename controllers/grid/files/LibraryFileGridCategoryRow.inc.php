<?php

/**
 * @file controllers/grid/files/LibraryFileGridCategoryRow.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileGridCategoryRow
 * @ingroup controllers_grid_settings_library
 *
 * @brief Library file grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

use APP\file\LibraryFileManager;

class LibraryFileGridCategoryRow extends GridCategoryRow
{
    /** the context for our Library file manager */
    public $_context;

    /**
     * Constructor
     */
    public function __construct($context)
    {
        $this->_context = & $context;
        parent::__construct();
    }

    //
    // Overridden methods from GridCategoryRow
    //
    /**
     * Category rows only have one cell and one label.  This is it.
     * return string
     */
    public function getCategoryLabel()
    {
        $context = $this->getContext();
        $libraryFileManager = new LibraryFileManager($context->getId());
        return __($libraryFileManager->getTitleKeyFromType($this->getData()));
    }

    /**
     * Get the context
     *
     * @return object context
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * @copydoc GridCategoryRow::initialize()
     *
     * @param null|mixed $template
     */
    public function initialize($request, $template = null)
    {
        parent::initialize($request, $template);
        $this->setId($this->getData());
    }
}
