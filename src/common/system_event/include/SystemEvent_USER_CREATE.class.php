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
class SystemEvent_USER_CREATE extends SystemEvent {
    
    /**
     * Verbalize the parameters so they are readable and much user friendly in 
     * notifications
     * 
     * @return string
     */
    public function verbalizeParameters() {
        $txt = '';
        $txt .= 'user: #'. $this->getIdFromParam($this->parameters);
        return $txt;
    }
    
    /** 
     * Process stored event
     */
    function process() {
        // Check parameters
        $user_id=$this->getIdFromParam($this->parameters);

        if ($user_id == 0) {
            return $this->setErrorBadParam();
        }

        // Need to add new user alias
        BackendAliases::instance()->setNeedUpdateMailAliases();

        // Create user home directory
        if (!BackendSystem::instance()->createUserHome($user_id)) {
            $this->error("Could not create user home");
            return false;
        }
        
        // Need to update system user cache
        BackendSystem::instance()->setNeedRefreshUserCache();

        $this->done();
        return true;
    }

}

?>
