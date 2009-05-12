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
class SystemEvent_PROJECT_DELETE extends SystemEvent {
    
    /**
     * Verbalize the parameters so they are readable and much user friendly in 
     * notifications
     * 
     * @return string
     */
    public function verbalizeParameters() {
        $txt = '';
        $txt .= 'project: #'. $this->getIdFromParam($this->parameters);
        return $txt;
    }
    
    /** 
     * Process stored event
     */
    function process() {
        // Check parameters
        $group_id=$this->getIdFromParam($this->parameters);
        
        if ($project = $this->getProject($group_id)) {
            
            // Should we delete mailing lists? TODO
            //$backend->setNeedUpdateMailAliases();
            
            if (!BackendSystem::instance()->archiveProjectHome($group_id)) {
                $this->error("Could not archive project home");
                return false;
            }
            
            if (!BackendCVS::instance()->archiveProjectCVS($group_id)) {
                $this->error("Could not archive project CVS repository");
                return false;
            }
            if ($project->usesCVS()) {
                BackendCVS::instance()->setCVSRootListNeedUpdate();
            }
            
            if (!BackendSVN::instance()->archiveProjectSVN($group_id)) {
                $this->error("Could not archive project SVN repository");
                return false;
            }
            if ($project->usesSVN()) {
                BackendSVN::instance()->setSVNApacheConfNeedUpdate();
            }
            
            $this->done();
            return true;
        }
        return false;
    }

}

?>
