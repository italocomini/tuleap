<?php
/**
 * Copyright (c) Enalean, 2017-2018. All Rights Reserved.
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

namespace Tuleap\CrossTracker\Report\Query\Advanced\QueryBuilder;

use ParagonIE\EasyDB\EasyStatement;
use Tuleap\DB\DataAccessObject;
use Tuleap\Tracker\Report\Query\IProvideFromAndWhereSQLFragments;

class CrossTrackerExpertQueryReportDao extends DataAccessObject
{
    public function searchArtifactsMatchingQuery(
        IProvideFromAndWhereSQLFragments $from_where,
        array $tracker_ids,
        $limit,
        $offset
    ) {
        $from  = implode(' ', array_unique($from_where->getFromAsArray()));
        $where = $from_where->getWhere();

        $tracker_ids_statement = EasyStatement::open()->in('tracker_artifact.tracker_id IN (?*)', $tracker_ids);

        $sql = "SELECT
                  SQL_CALC_FOUND_ROWS
                  tracker_artifact.tracker_id,
                  tracker_artifact.id,
                  tracker_changeset_value_title.value AS title
                FROM tracker_artifact
                  INNER JOIN tracker ON (tracker_artifact.tracker_id = tracker.id)
                  INNER JOIN groups  ON (groups.group_id = tracker.group_id)
                  LEFT JOIN (
                      tracker_changeset_value AS changeset_value_title
                      INNER JOIN tracker_semantic_title
                        ON (tracker_semantic_title.field_id = changeset_value_title.field_id)
                      INNER JOIN tracker_changeset_value_text AS tracker_changeset_value_title
                          ON (tracker_changeset_value_title.changeset_value_id = changeset_value_title.id)
                    ) ON (
                      tracker_semantic_title.tracker_id = tracker_artifact.tracker_id
                      AND changeset_value_title.changeset_id = tracker_artifact.last_changeset_id
                    )
                    $from
                    WHERE groups.status = 'A'
                      AND tracker.deletion_date IS NULL
                      AND $tracker_ids_statement
                      AND $where
                ORDER BY tracker_artifact.id DESC
                LIMIT ?, ?";

        $parameters   = $tracker_ids_statement->values();
        $parameters[] = $offset;
        $parameters[] = $limit;

        return $this->getDB()->safeQuery($sql, $parameters);
    }
}
