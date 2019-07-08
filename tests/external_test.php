<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Provides {@link block_todo_external_testcase} class.
 *
 * @package     block_todo
 * @category    test
 * @copyright   2018 David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
global $CFG;
/**
 * Tests for the external API of the plugin.
 *
 * @copyright 2018 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_dynamo_external_testcase extends advanced_testcase {
    /** @var stdClass */
    protected $user;
    /** @var array */
    protected $anotheruser;
    /**
     * Set up for every test
     */
    public function setUp() {
        global $DB;
        $this->resetAfterTest();
        $this->user = self::getDataGenerator()->create_user();
        self::setUser($this->user);
        $this->anotheruser = self::getDataGenerator()->create_user();
    }
    /**
     * Test that users who can't create the survey, can't create it.
     *
     * @expectedException required_capability_exception
     */
    public function test_add_instance_no_permission() {
        $context = context_user::instance($this->user->id);
        $userroles = get_archetype_roles('user');
        $authrole = array_pop($userroles);
        unassign_capability('mod/dynamo:myaddinstance', $authrole->id);
        mod_dynamo\lib::dynamo_add_instance('This should throw required_capability_exception');
    }
}