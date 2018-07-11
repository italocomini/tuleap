<?php
/**
 * Copyright (c) Enalean, 2012-2018. All Rights Reserved.
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

use ParagonIE\EasyDB\EasyStatement;

class Git_LogDao extends \Tuleap\DB\DataAccessObject
{
    public function getLastPushForRepository($repositoryId)
    {
        $sql = 'SELECT log.*
                FROM plugin_git_log log 
                WHERE repository_id = ?
                ORDER BY push_date DESC
                LIMIT 1';
        return $this->getDB()->row($sql, $repositoryId);
    }

    public function getLastPushForRepositories($repository_ids)
    {
        $ids_condition = EasyStatement::open()->in('?*', $repository_ids);
        $sql = "SELECT repository_id, push_date
                FROM plugin_git_log
                WHERE repository_id IN ($ids_condition)";
        return $this->getDB()->safeQuery($sql, $ids_condition->values());
    }

    /**
     * Return the last pushes of a given repository grouped by week
     *
     * @param Integer $repositoryId Id of the repository
     * @param Integer $week         Number of the week
     * @param Integer $year         Year corresponding to the week
     *
     */
    public function getRepositoryPushesByWeek($repositoryId, $week, $year)
    {
        $sql          = 'SELECT COUNT(*) AS pushes,
                             repository_id AS repo,
                             WEEK(FROM_UNIXTIME(push_date), 3) AS week,
                             YEAR(FROM_UNIXTIME(push_date)) AS year,
                             SUM(commits_number) AS commits
                         FROM plugin_git_log
                         WHERE repository_id = ?
                           AND WEEK(FROM_UNIXTIME(push_date), 3) = ?
                           AND YEAR(FROM_UNIXTIME(push_date))= ?
                         GROUP BY year, week, repo';

        return $this->getDB()->run($sql, $repositoryId, $week, $year);
    }

    /**
     * Obtain last git pushes performed by the given user
     *
     * @param Integer $userId Id of the user
     * @param Integer $repoId Id of the git repository
     * @param Integer $offset Offset of the search
     * @param Integer $date   Date from which we start collecting logs
     *
     * @return array
     */
    public function getLastPushesByUser($userId, $repoId, $offset, $date)
    {
        $repository_id_filter = \ParagonIE\EasyDB\EasyStatement::open();
        if ($repoId) {
            $repository_id_filter->andWith('AND l.repository_id = ?', $repoId);
        }

        $limit = 10;
        if ($offset) {
            $limit = $offset;
        }

        $sql = "SELECT g.group_name, r.repository_name, l.push_date, SUM(l.commits_number) AS commits_number
                FROM plugin_git_log l
                JOIN plugin_git r ON l.repository_id = r.repository_id
                JOIN groups g ON g.group_id = r.project_id
                WHERE l.user_id = ?
                  AND r.repository_deletion_date  = '0000-00-00 00:00:00'
                  AND g.status = 'A'
                  AND l.push_date > ?
                  $repository_id_filter
                GROUP BY l.push_date
                ORDER BY g.group_name, r.repository_name, l.push_date DESC
                LIMIT ?";

        $params   = [$userId, $date];
        $params   = array_merge($params, $repository_id_filter->values());
        $params[] = $limit;

        return $this->getDB()->safeQuery($sql, $params);
    }

    /**
     * Obtain repositories containing git pushes by a user in the last given period
     *
     * @param Integer $userId Id of the user
     * @param Integer $date   Date from which we start collecting repostories with pushes
     *
     * @return DataAccessResult
     */
    public function getLastPushesRepositories($userId, $date)
    {
        $sql = "SELECT DISTINCT(r.repository_id), g.group_name, r.repository_name, r.repository_namespace, g.group_id
                FROM plugin_git_log l
                JOIN plugin_git r ON l.repository_id = r.repository_id
                JOIN groups g ON g.group_id = r.project_id
                WHERE l.user_id = ?
                  AND r.repository_deletion_date  = '0000-00-00 00:00:00'
                  AND g.status = 'A'
                  AND l.push_date > ?
                ORDER BY g.group_id, r.repository_id, l.push_date DESC";

        return $this->getDB()->run($sql, $userId, $date);
    }

    public function hasRepositoriesUpdatedAfterGivenDate($project_id, $date)
    {
        $sql = "SELECT COUNT(*)
                FROM plugin_git_log l
                INNER JOIN plugin_git r USING(repository_id)
                WHERE r.project_id = ?
                  AND r.repository_deletion_date  = '0000-00-00 00:00:00'
                  AND l.push_date > ?";

        return $this->getDB()->single($sql, [$project_id, $date]) > 0;
    }

    /**
     * Count all Git pushes for the given period
     *
     * @param String  $startDate Period start date
     * @param String  $endDate   Period end date
     * @param Integer $projectId Id of the project we want to retrieve its git stats
     *
     * @return array
     */
    public function totalPushes($startDate, $endDate, $projectId = null)
    {
        $projectFilter = \ParagonIE\EasyDB\EasyStatement::open();

        if ($projectId !== null) {
            $projectFilter->andWith('AND project_id = ?', $projectId);
        }
        $sql = "SELECT DATE_FORMAT(FROM_UNIXTIME(push_date), '%M') AS month,
                    YEAR(FROM_UNIXTIME(push_date)) AS year, 
                    COUNT(*) AS pushes_count,
                    COUNT(DISTINCT(repository_id)) AS repositories, 
                    SUM(commits_number) AS commits_count, 
                    COUNT(DISTINCT(user_id)) AS users
                FROM plugin_git_log JOIN plugin_git USING(repository_id)
                WHERE push_date BETWEEN UNIX_TIMESTAMP(?) AND UNIX_TIMESTAMP(?)
                  $projectFilter
                GROUP BY year, month
                ORDER BY year, STR_TO_DATE(month,'%M')";

        $params = [$startDate, $endDate];
        $params = array_merge($params, $projectFilter->values());
        return $this->getDB()->safeQuery($sql, $params);
    }

    public function searchLatestPushesInProject($project_id, $nb_max)
    {
        $sql = "SELECT log.*
                FROM plugin_git_log AS log
                    INNER JOIN plugin_git AS repo ON (
                        log.repository_id = repo.repository_id
                        AND repo.project_id = ?
                        AND repo.repository_scope = 'P'
                        AND repo.repository_deletion_date IS NULL
                    )
                ORDER BY log.push_date DESC
                LIMIT ?";

        return $this->getDB()->run($sql, $project_id, $nb_max);
    }
}
