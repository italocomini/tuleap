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

require_once dirname(__FILE__) . '/../../Test_Tracker_FormElement_Builder.php';
require_once dirname(__FILE__) . '/../../../include/Tracker/CrossSearch/ViewBuilder.class.php';
require_once dirname(__FILE__) . '/../../../include/Tracker/CrossSearch/SemanticValueFactory.class.php';
require_once dirname(__FILE__) . '/../../../include/Tracker/TrackerFactory.class.php';
require_once 'common/include/Codendi_Request.class.php';
require_once 'Test_CriteriaBuilder.php';
require_once dirname(__FILE__) . '/../../../include/Tracker/CrossSearch/SemanticStatusReportField.class.php';

Mock::generate('Tracker_FormElementFactory');
Mock::generate('Tracker_CrossSearch_Search');
Mock::generate('Tracker_CrossSearch_SearchContentView');
Mock::generate('TrackerFactory');
Mock::generate('Project');
Mock::generate('Tracker_Report');
Mock::generate('Tracker_CrossSearch_SemanticValueFactory');

class Fake_Tracker_CrossSearch_SearchContentView extends Tracker_CrossSearch_SearchContentView {
}

class Tracker_CrossSearch_ViewBuilderTest extends TuleapTestCase {

    public function setUp() {
        parent::setUp();
        $this->formElementFactory = new MockTracker_FormElementFactory();
    }

    public function itBuildCustomContentView() {
        $formElementFactory = new MockTracker_FormElementFactory();
        $formElementFactory->setReturnValue('getProjectSharedFields', array());
        $tracker_factory    = new MockTrackerFactory();
        $tracker_factory->setReturnValue('getTrackersByGroupId', array());
        $project            = new MockProject();
        $request_criteria   = aCrossSearchCriteria()->build();
        $search             = new MockTracker_CrossSearch_Search();
        $search->setReturnValue('getHierarchicallySortedArtifacts', new TreeNode());
        $this->semantic_factory = new MockTracker_CrossSearch_SemanticValueFactory();
        $builder   = new Tracker_CrossSearch_ViewBuilder($formElementFactory, $tracker_factory, $search, $this->semantic_factory);
        $classname = 'Tracker_CrossSearch_SearchContentView';
        $view      = $builder->buildContentView($project, $request_criteria);
        $this->assertIsA($view, $classname);
    }
    
}

class Tracker_CrossSearch_ViewBuilder_BuildViewTest extends TuleapTestCase {
    public function itThrowsAnExceptionIfTheServiceTrackerIsntActivated() {
        $project = new MockProject();
        $builder = new Tracker_CrossSearch_ViewBuilder(new MockTracker_FormElementFactory(), new MockTrackerFactory(), new MockTracker_CrossSearch_Search(), new MockTracker_CrossSearch_SemanticValueFactory());
        
        $this->expectException('Tracker_CrossSearch_ServiceNotUsedException');
        $cross_search_criteria = aCrossSearchCriteria()
                                ->forOpenItems()
                                ->build();

        $builder->buildView($project, $cross_search_criteria);
    }
}


?>
