<?php
/**
 * Copyright (c) Enalean, 2015. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * WARNING !
 * For your sanity, please don't read this.
 */

class PHPWikiPageVersionContentFormatter {

    const PHPWIKI_TEMPLATE = 'rest';

    /** @var PHPWikiPageWrapper */
    private $wrapper;

    public function __construct($project_id) {
        $this->wrapper = new PHPWikiPageWrapper($project_id);
    }

    public function getFormattedContent(PHPWikiPage $wiki_page, PHPWikiPageVersion $version) {
        $request = $this->wrapper->getRequest();
        $request->initializeTheme();

        require_once(PHPWIKI_SRC_PATH.'/lib/Template.php');
        $template = new Template(
            self::PHPWIKI_TEMPLATE,
            $request,
            $this->getTransformedContent($wiki_page, $version, $request)
        );

        ob_start();
        $template->printExpansion();
        return ob_get_clean();
    }

    private function getTransformedContent(
        PHPWikiPage $wiki_page,
        PHPWikiPageVersion $version,
        WikiRequest $request
    ) {
        $dbi      = $request->_dbi;
        $page_db  = $dbi->getPage($wiki_page->getPagename());
        $revision = $page_db->getRevision($version->getVersionId());

        return $revision->getTransformedContent();
    }
}