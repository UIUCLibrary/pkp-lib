<?php

/**
 * @file controllers/grid/files/FileNameGridColumn.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class FileNameGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a file name column.
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class FileNameGridColumn extends GridColumn
{
    /** @var boolean */
    public $_includeNotes;

    /** @var int */
    public $_stageId;

    /** @var boolean */
    public $_removeHistoryTab;

    /**
     * Constructor
     *
     * @param $includeNotes boolean
     * @param $stageId int (optional)
     * @param $removeHistoryTab boolean (optional) Open the information center
     * without the history tab.
     */
    public function __construct($includeNotes = true, $stageId = null, $removeHistoryTab = false)
    {
        $this->_includeNotes = $includeNotes;
        $this->_stageId = $stageId;
        $this->_removeHistoryTab = $removeHistoryTab;

        import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
        $cellProvider = new ColumnBasedGridCellProvider();

        parent::__construct(
            'name',
            'common.name',
            null,
            null,
            $cellProvider,
            ['width' => 70, 'alignment' => COLUMN_ALIGNMENT_LEFT, 'anyhtml' => true]
        );
    }


    //
    // Public methods
    //
    /**
     * Method expected by ColumnBasedGridCellProvider
     * to render a cell in this column.
     *
     * @copydoc ColumnBasedGridCellProvider::getTemplateVarsFromRowColumn()
     */
    public function getTemplateVarsFromRow($row)
    {
        $submissionFileData = $row->getData();
        $submissionFile = $submissionFileData['submissionFile'];
        assert(is_a($submissionFile, 'SubmissionFile'));
        $fileExtension = pathinfo($submissionFile->getData('path'), PATHINFO_EXTENSION);
        return ['label' => '<span class="file_extension ' . $fileExtension . '">' . $submissionFile->getId() . '</span>'];
    }


    //
    // Override methods from GridColumn
    //
    /**
     * @copydoc GridColumn::getCellActions()
     */
    public function getCellActions($request, $row, $position = GRID_ACTION_POSITION_DEFAULT)
    {
        $cellActions = parent::getCellActions($request, $row, $position);

        // Retrieve the submission file.
        $submissionFileData = & $row->getData();
        assert(isset($submissionFileData['submissionFile']));
        $submissionFile = $submissionFileData['submissionFile']; /** @var SubmissionFile $submissionFile */

        // Create the cell action to download a file.
        import('lib.pkp.controllers.api.file.linkAction.DownloadFileLinkAction');
        $cellActions[] = new DownloadFileLinkAction($request, $submissionFile, $this->_getStageId());

        return $cellActions;
    }

    //
    // Private methods
    //
    /**
     * Determine whether or not submission note status should be included.
     */
    public function _getIncludeNotes()
    {
        return $this->_includeNotes;
    }

    /**
     * Get stage id, if any.
     *
     * @return mixed int or null
     */
    public function _getStageId()
    {
        return $this->_stageId;
    }
}
