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

require_once(__DIR__ . "/../TransitionFactory.php");

use DataAccessQueryException;
use FakeDataAccessResult;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Transition_PostAction_Field_IntDao;
use Tuleap\DB\DataAccessObject;
use Tuleap\Tracker\Workflow\PostAction\Update\TransitionFactory;

class SetIntValueRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var SetIntValueRepository
     */
    private $set_int_value_repository;

    /**
     * @var MockInterface
     */
    private $set_int_value_dao;

    /**
     * @var MockInterface
     */
    private $pdo_wrapper;

    /**
     * @before
     */
    public function createRepository()
    {
        $this->set_int_value_dao = Mockery::mock(Transition_PostAction_Field_IntDao::class);

        $this->pdo_wrapper = Mockery::mock(DataAccessObject::class);
        $this->pdo_wrapper
            ->shouldReceive('wrapAtomicOperations')
            ->andReturnUsing(function (callable $operation) {
                $operation();
            });

        $this->set_int_value_repository = new SetIntValueRepository(
            $this->set_int_value_dao,
            $this->pdo_wrapper
        );
    }

    public function testCreateCreatesGivenSetIntValueOnGivenTransition()
    {
        $this->set_int_value_dao->shouldReceive('create')
            ->with(1)
            ->andReturn(9);
        $this->set_int_value_dao->shouldReceive('updatePostAction')
            ->with(9, 43, 1);

        $transition    = TransitionFactory::buildATransitionWithId(1);
        $set_int_value = new SetIntValue(null, 43, 1);

        $this->set_int_value_repository->create($transition, $set_int_value);
    }

    /**
     * @expectedException DataAccessQueryException
     */
    public function testCreateThrowsWhenCreationFail()
    {
        $this->set_int_value_dao->shouldReceive('create')
            ->andReturn(false);

        $transition    = TransitionFactory::buildATransition();
        $set_int_value = new SetIntValue(null, 43, 1);

        $this->set_int_value_repository->create($transition, $set_int_value);
    }

    public function testUpdateUpdatesGivenSetIntValue()
    {
        $this->set_int_value_dao
            ->shouldReceive('updatePostAction')
            ->with(9, 43, 1)
            ->andReturn(true);
        $set_int_value = new SetIntValue(9, 43, 1);
        $this->set_int_value_repository->update($set_int_value);
    }

    /**
     * @expectedException DataAccessQueryException
     */
    public function testUpdateThrowsWhenUpdateFail()
    {
        $this->set_int_value_dao
            ->shouldReceive('updatePostAction')
            ->andReturn(false);
        $set_int_value = new SetIntValue(9, 43, 1);
        $this->set_int_value_repository->update($set_int_value);
    }

    public function testDeleteAllByTransitionIfIdNotInDeletesExpectedTransitions()
    {
        $this->set_int_value_dao
            ->shouldReceive('deletePostActionByTransitionIfIdNotIn')
            ->with(1, [1, 2, 3])
            ->andReturn(true);
        $transition = TransitionFactory::buildATransitionWithId(1);
        $this->set_int_value_repository->deleteAllByTransitionIfIdNotIn($transition, [1, 2, 3]);
    }

    /**
     * @expectedException DataAccessQueryException
     */
    public function testDeleteAllByTransitionIfIdNotInThrowsIfDeleteFail()
    {
        $this->set_int_value_dao
            ->shouldReceive('deletePostActionByTransitionIfIdNotIn')
            ->andReturn(false);
        $transition = TransitionFactory::buildATransition();
        $this->set_int_value_repository->deleteAllByTransitionIfIdNotIn($transition, [1, 2, 3]);
    }

    public function testFindAllIdsByTransitionReturnsIdsOfAllActionsOnGivenTransition()
    {
        $this->set_int_value_dao
            ->shouldReceive('findAllIdsByTransitionId')
            ->with(1)
            ->andReturn(new FakeDataAccessResult([
                ['id' => 1],
                ['id' => 2],
                ['id' => 3]
            ]));

        $transition = TransitionFactory::buildATransitionWithId(1);
        $ids        = $this->set_int_value_repository->findAllIdsByTransition($transition);

        $this->assertEquals(new PostActionIdCollection(1, 2, 3), $ids);
    }

    /**
     * @expectedException DataAccessQueryException
     */
    public function testFindAllIdsByTransitionThrowsWhenFindFail()
    {
        $this->set_int_value_dao
            ->shouldReceive('findAllIdsByTransitionId')
            ->andReturn(false);
        $transition = TransitionFactory::buildATransition();
        $this->set_int_value_repository->findAllIdsByTransition($transition);
    }
}
