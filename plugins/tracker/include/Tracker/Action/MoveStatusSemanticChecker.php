<?php
/**
 * Copyright (c) Enalean, 2018. All Rights Reserved.
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
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Tracker\Action;

use Tracker;
use Tracker_FormElementFactory;

class MoveStatusSemanticChecker
{
    const STATUS_SEMANTIC_LABEL = 'status';

    public function __construct(Tracker_FormElementFactory $form_element_factory)
    {
        $this->form_element_factory = $form_element_factory;
    }

    /**
     * @return bool
     */
    public function areBothSemanticsDefined(Tracker $source_tracker, Tracker $target_tracker)
    {
        return $source_tracker->hasSemanticsStatus() && $target_tracker->hasSemanticsStatus();
    }

    /**
     * @return bool
     */
    public function doesBothTrackerStatusFieldHaveTheSameType(Tracker $source_tracker, Tracker $target_tracker)
    {
        $source_tracker_status_field = $source_tracker->getStatusField();
        $target_tracker_status_field = $target_tracker->getStatusField();

        return $this->form_element_factory->getType($source_tracker_status_field) ===
            $this->form_element_factory->getType($target_tracker_status_field);
    }

    public function canSemanticBeMoved(Tracker $source_tracker, Tracker $target_tracker)
    {
        return $this->areBothSemanticsDefined($source_tracker, $target_tracker) &&
            $this->doesBothTrackerStatusFieldHaveTheSameType($source_tracker, $target_tracker);
    }
}