<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 *
 * 
 */


/**
* System Event classes
*
*/
class SystemEvent_CVS_IS_PRIVATE extends SystemEvent {
    
    /**
     * Verbalize the parameters so they are readable and much user friendly in 
     * notifications
     * 
     * @return string
     */
    public function verbalizeParameters() {
        $txt = '';
        list($group_id, $cvs_is_private) = $this->getParametersAsArray();
        $txt .= 'project: #'. $group_id .', cvs is private: '. ($cvs_is_private ? 'true' : 'false');
        return $txt;
    }
    
    /** 
     * Process stored event
     */
    function process() {
        list($group_id, $cvs_is_private) = $this->getParametersAsArray();
        
        if ($project = $this->getProject($group_id)) {
            if ($project->usesCVS()) {
                if (!BackendCVS::instance()->setCVSPrivacy($project, $cvs_is_private)) {
                    $this->error("Could not set cvs is private for group $group_id");
                    return false;
                }
            }
            
            $this->done();
            return true;
        }
        return false;
    }

}

?>
