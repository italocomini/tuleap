<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
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
 *
 */

namespace Tuleap\Tracker\Workflow\PostAction\Update\Internal;

require_once(__DIR__ . '/../TransitionFactory.php');

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Tuleap\Tracker\Workflow\PostAction\Update\PostActionCollection;
use Tuleap\Tracker\Workflow\PostAction\Update\TransitionFactory;

class SetIntValueUpdaterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var SetIntValueUpdater
     */
    private $updater;
    /**
     *
     * @var MockInterface
     */
    private $set_int_value_repository;

    /**
     * @before
     */
    public function createUpdater()
    {
        $this->set_int_value_repository = Mockery::mock(SetIntValueRepository::class);
        $this->set_int_value_repository
            ->shouldReceive('deleteAllByTransitionIfIdNotIn')
            ->byDefault();
        $this->set_int_value_repository
            ->shouldReceive('update')
            ->byDefault();

        $this->updater = new SetIntValueUpdater($this->set_int_value_repository);
    }

    public function testUpdateAddsNewSetIntValueActions()
    {
        $transition = TransitionFactory::buildATransition();
        $this->mockFindAllIdsByTransition($transition, [1]);

        $added_action = new SetIntValue(null, 43, 1);
        $actions      = new PostActionCollection($added_action);

        $this->set_int_value_repository
            ->shouldReceive('create')
            ->with($transition, $added_action)
            ->andReturns();

        $this->updater->updateByTransition($actions, $transition);
    }

    public function testUpdateUpdatesSetIntValueActionsWhichAlreadyExists()
    {
        $transition = TransitionFactory::buildATransition();
        $this->mockFindAllIdsByTransition($transition, [1]);

        $updated_action = new SetIntValue(1, 43, 1);
        $actions        = new PostActionCollection($updated_action);

        $this->set_int_value_repository
            ->shouldReceive('update')
            ->with($updated_action)
            ->andReturns();

        $this->updater->updateByTransition($actions, $transition);
    }

    public function testUpdateDeletesRemovedSetIntValueActions()
    {
        $transition = TransitionFactory::buildATransition();

        $this->mockFindAllIdsByTransition($transition, [2, 3]);

        $action  = new SetIntValue(2, 43, 1);
        $actions = new PostActionCollection($action);

        $this->set_int_value_repository
            ->shouldReceive('deleteAllByTransitionIfIdNotIn')
            ->with($transition, [2])
            ->andReturns();

        $this->updater->updateByTransition($actions, $transition);
    }

    private function mockFindAllIdsByTransition(
        $transition,
        array $ids
    ) {
        $existing_ids = new PostActionIdCollection(...$ids);
        $this->set_int_value_repository
            ->shouldReceive('findAllIdsByTransition')
            ->withArgs([$transition])
            ->andReturn($existing_ids);
    }
}
