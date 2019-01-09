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

namespace Tuleap\Tracker\FormElement\Field\Burndown;

use DateTime;
use PFUser;
use Tracker_Artifact;
use Tracker_Chart_Data_Burndown;
use Tracker_FormElement_Field_ComputedDao;
use Tuleap\Tracker\FormElement\ChartConfigurationFieldRetriever;

class BurndownRemainingEffortAdderForREST
{
    /**
     * @var ChartConfigurationFieldRetriever
     */
    private $field_retriever;

    /**
     * @var Tracker_FormElement_Field_ComputedDao
     */
    private $computed_cache_dao;

    public function __construct(
        ChartConfigurationFieldRetriever $field_retriever,
        Tracker_FormElement_Field_ComputedDao $computed_cache_dao
    ) {
        $this->field_retriever    = $field_retriever;
        $this->computed_cache_dao = $computed_cache_dao;
    }

    public function addRemainingEffortDataForREST(
        Tracker_Chart_Data_Burndown $burndown_data,
        Tracker_Artifact $artifact,
        PFUser $user
    ) {
        $field = $this->field_retriever->getBurndownRemainingEffortField($artifact, $user);

        if (! $field) {
            return;
        }

        $cached_days_result = $this->computed_cache_dao->searchCachedDays($artifact->getId(), $field->getId());

        if (! $cached_days_result) {
            return;
        }

        foreach ($cached_days_result as $key => $cached_day) {
            $time = new DateTime();
            $time->setTimestamp($cached_day['timestamp']);
            $burndown_data->addEffortAtDateTime($time, $cached_day['value']);
            $burndown_data->addEffortAt($key, $cached_day['value']);
        }

        if ($burndown_data->getTimePeriod()->isTodayWithinTimePeriod()) {
            $remaining_effort = $field->getComputedValue($user, $artifact, null);
            $burndown_data->addEffortAtDateTime($this->getMidnightDate(new DateTime()), $remaining_effort);
            $burndown_data->addEffortAt(count($cached_days_result) + 1, $remaining_effort);
        }
    }

    /**
     * @return DateTime
     */
    private function getMidnightDate(DateTime $date)
    {
        $date->setTime(0, 0, 0);

        return $date;
    }
}
