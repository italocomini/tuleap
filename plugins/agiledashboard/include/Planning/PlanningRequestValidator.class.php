<?php
/**
 * Copyright (c) Enalean, 2012. All Rights Reserved.
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

require_once 'PlanningFactory.class.php';
require_once 'common/valid/ValidFactory.class.php';
require_once 'common/include/Codendi_Request.class.php';
require_once 'PlanningParameters.class.php';

/**
 * Validates planning creation requests.
 */
class Planning_RequestValidator {
    
    /**
     * @var PlanningFactory
     */
    private $factory;
    
    /**
     * Creates a new validator instance.
     * 
     * @param PlanningFactory $factory Used to retrieve existing planning trackers for validation purpose.
     */
    public function __construct(PlanningFactory $factory) {
        $this->factory = $factory;
    }
    
    /**
     * Returns true when the $request contains sufficent data to create a valid
     * Planning.
     * 
     * Existing planning update validation is not implemented yet.
     * 
     * @param Codendi_Request $request
     * 
     * @return bool
     */
    public function isValid(Codendi_Request $request) {
        $group_id            = $request->get('group_id');
        $planning_parameters = $request->get('planning');
        
        if (! $planning_parameters) {
            $planning_parameters = array();
        }
        
        $planning_parameters = PlanningParameters::fromArray($planning_parameters);
        
        return $this->nameIsPresent($planning_parameters)
            && $this->backlogTrackerIdIsPresentAndIsAPositiveIntegers($planning_parameters)
            && $this->planningTrackerIdIsPresentAndIsAPositiveInteger($planning_parameters)
            && $this->planningTrackerIsNotAlreadyUsedAsAPlanningTrackerInTheProject($group_id, $planning_parameters);
    }
    
    /**
     * Checks whether name is present in the parameters.
     * 
     * @param PlanningParameters $planning_parameters The validated parameters.
     * 
     * @return bool
     */
    private function nameIsPresent(PlanningParameters $planning_parameters) {
        $name = new Valid_String();
        $name->required();
        
        return $name->validate($planning_parameters->name);
    }
    
    /**
     * Checks whether backlog tracker id is present in the parameters, and is
     * a valid positive integer.
     * 
     * @param PlanningParameters $planning_parameters The validated parameters.
     * 
     * @return bool
     */
    private function backlogTrackerIdIsPresentAndIsAPositiveIntegers(PlanningParameters $planning_parameters) {
        $backlog_tracker_id = new Valid_UInt();
        $backlog_tracker_id->required();
        
        return $backlog_tracker_id->validate($planning_parameters->backlog_tracker_id);
    }
    
    /**
     * Checks whether a planning tracker id is present in the parameters, and is
     * a valid positive integer.
     * 
     * @param PlanningParameters $planning_parameters The validated parameters.
     * 
     * @return bool
     */
    private function planningTrackerIdIsPresentAndIsAPositiveInteger(PlanningParameters $planning_parameters) {
        $planning_tracker_id = new Valid_UInt();
        $planning_tracker_id->required();
        
        return $planning_tracker_id->validate($planning_parameters->planning_tracker_id);
    }
    
    /**
     * Checks whether the planning tracker id in the request points to a tracker
     * that is not already used as a planning tracker in the project identified
     * by the request group_id.
     * 
     * @param int                $group_id The group id to check the existing planning trackers against.
     * @param PlanningParameters $request  The validated parameters.
     * 
     * @return bool
     */
    private function planningTrackerIsNotAlreadyUsedAsAPlanningTrackerInTheProject($group_id, PlanningParameters $planning_parameters) {
        $planning_tracker_id          = $planning_parameters->planning_tracker_id;
        $project_planning_tracker_ids = $this->factory->getPlanningTrackerIdsByGroupId($group_id);
        
        return ! in_array($planning_tracker_id, $project_planning_tracker_ids);
    }
}
?>
