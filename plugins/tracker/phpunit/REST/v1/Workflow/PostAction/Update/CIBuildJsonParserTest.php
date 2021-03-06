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

namespace Tuleap\Tracker\REST\v1\Workflow\PostAction\Update;

require_once __DIR__ . '/../../../../../bootstrap.php';

use PHPUnit\Framework\TestCase;
use Tuleap\Tracker\Workflow\PostAction\Update\Internal\CIBuild;

class CIBuildJsonParserTest extends TestCase
{
    /**
     * @var CIBuildJsonParser
     */
    private $parser;

    /**
     * @before
     */
    public function createParser()
    {
        $this->parser = new CIBuildJsonParser();
    }

    public function testAcceptReturnsTrueWhenTypeMatches()
    {
        $this->assertTrue($this->parser->accept(["type" => "run_job"]));
    }

    public function testAcceptReturnsFalseWhenTypeDoesNotMatch()
    {
        $this->assertFalse($this->parser->accept(["type" => "set_date_value"]));
    }

    public function testAcceptReturnsFalseWithoutType()
    {
        $this->assertFalse($this->parser->accept([]));
    }

    public function testParseReturnsNewCIBuildBasedOnGivenJson()
    {
        $ci_build = $this->parser->parse([
            "id" => 2,
            "type" => "run_job",
            "job_url" => "http://example.test",
        ]);
        $this->assertEquals(new CIBuild(2, "http://example.test"), $ci_build);
    }

    public function testParseWhenIdNotProvided()
    {
        $ci_build = $this->parser->parse([
            "type" => "run_job",
            "job_url" => "http://example.test",
        ]);
        $this->assertEquals(new CIBuild(null, "http://example.test"), $ci_build);
    }

    /**
     * @expectedException \Tuleap\REST\I18NRestException
     * @expectedExceptionCode 400
     */
    public function testParseThrowsWhenIdIsNotInt()
    {
        $this->parser->parse([
            "id" => "not int",
            "type" => "run_job",
            "job_url" => "http://example.test",
        ]);
    }

    /**
     * @expectedException \Tuleap\REST\I18NRestException
     * @expectedExceptionCode 400
     */
    public function testParseThrowsWhenNoJobUrlProvided()
    {
        $this->parser->parse(["type" => "run_job"]);
    }

    /**
     * @expectedException \Tuleap\REST\I18NRestException
     * @expectedExceptionCode 400
     */
    public function testParseThrowsWhenJobUrlIsNull()
    {
        $this->parser->parse([
            "type" => "run_job",
            "job_url" => null
        ]);
    }

    /**
     * @expectedException \Tuleap\REST\I18NRestException
     * @expectedExceptionCode 400
     */
    public function testParseThrowsWhenJobUrlIsNotString()
    {
        $this->parser->parse([
            "type" => "run_job",
            "job_url" => 3
        ]);
    }
}
