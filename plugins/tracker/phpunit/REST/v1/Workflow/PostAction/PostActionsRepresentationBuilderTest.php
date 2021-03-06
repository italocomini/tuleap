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

namespace Tuleap\Tracker\REST\v1\Workflow\PostAction;

use Jenkins_Client;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tracker_FormElement_Field;
use Transition;
use Transition_PostAction_CIBuild;
use Transition_PostAction_Field_Date;
use Transition_PostAction_Field_Float;
use Transition_PostAction_Field_Int;

class PostActionsRepresentationBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testBuildReturnsRunJobRepresentationBasedOnGivenCiBuildPostAction()
    {
        $run_job = $this->buildAPostActionCIBuild(1, 'http://job.example.com');
        $builder = new PostActionsRepresentationBuilder([$run_job]);

        $representation = $builder->build();

        $this->assertSame(1, $representation[0]->id);
        $this->assertSame('run_job', $representation[0]->type);
        $this->assertSame('http://job.example.com', $representation[0]->job_url);
    }

    private function buildAPostActionCIBuild($id, $job_url): Transition_PostAction_CIBuild
    {
        $transition = Mockery::mock(Transition::class);
        $client     = Mockery::mock(Jenkins_Client::class);
        return new Transition_PostAction_CIBuild($transition, $id, $job_url, $client);
    }

    public function testBuildReturnsRunJobRepresentationBasedOnGivenFieldDateAction()
    {
        $transition     = Mockery::mock(Transition::class);
        $field          = $this->buildFieldWithId(8);
        $set_date_field = new Transition_PostAction_Field_Date($transition, 1, $field, Transition_PostAction_Field_Date::CLEAR_DATE);
        $builder        = new PostActionsRepresentationBuilder([$set_date_field]);

        $representation = $builder->build();

        $this->assertSame(1, $representation[0]->id);
        $this->assertSame('set_field_value', $representation[0]->type);
        $this->assertSame(8, $representation[0]->field_id);
        $this->assertSame('date', $representation[0]->field_type);
        $this->assertSame('', $representation[0]->value);
    }

    public function testBuildReturnsRunJobRepresentationBasedOnGivenFieldIntAction()
    {
        $transition    = Mockery::mock(Transition::class);
        $field         = $this->buildFieldWithId(8);
        $set_int_field = new Transition_PostAction_Field_Int($transition, 1, $field, 23);
        $builder       = new PostActionsRepresentationBuilder([$set_int_field]);

        $representation = $builder->build();

        $this->assertSame(1, $representation[0]->id);
        $this->assertSame('set_field_value', $representation[0]->type);
        $this->assertSame(8, $representation[0]->field_id);
        $this->assertSame('int', $representation[0]->field_type);
        $this->assertSame(23, $representation[0]->value);
    }

    public function testBuildReturnsRunJobRepresentationBasedOnGivenFieldFloatAction()
    {
        $transition      = Mockery::mock(Transition::class);
        $field           = $this->buildFieldWithId(8);
        $set_float_field = new Transition_PostAction_Field_Float($transition, 1, $field, 3.4);
        $builder         = new PostActionsRepresentationBuilder([$set_float_field]);

        $representation = $builder->build();

        $this->assertSame(1, $representation[0]->id);
        $this->assertSame('set_field_value', $representation[0]->type);
        $this->assertSame(8, $representation[0]->field_id);
        $this->assertSame('float', $representation[0]->field_type);
        $this->assertSame(3.4, $representation[0]->value);
    }

    public function testBuildReturnsAsManyRepresentationsAsGivenActions()
    {
        $post_actions = [
            $this->buildAPostAction(),
            $this->buildAPostAction(),
            $this->buildAPostAction()
        ];

        $builder = new PostActionsRepresentationBuilder($post_actions);

        $this->assertCount(3, $builder->build());
    }

    private function buildAPostAction(): Transition_PostAction_Field_Float
    {
        $transition = Mockery::mock(Transition::class);
        $field      = $this->buildFieldWithId(8);
        return new Transition_PostAction_Field_Float($transition, 1, $field, 3.4);
    }

    private function buildFieldWithId($id): Tracker_FormElement_Field
    {
        $field = Mockery::mock(Tracker_FormElement_Field::class);
        $field->shouldReceive('getId')->andReturn($id);
        return $field;
    }
}
