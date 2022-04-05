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
 * The mod_dynamo viewed event.
 *
 * @package    mod_dynamo
 * @copyright  2019 UCLouvain
 * @author     Dominique Palumbo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_dynamo\event;

/**
 * The mod_dynamo viewed event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int dynamoid: the id of the dynamo.
 * }
 *
 * @package    mod_dynamo
 * @since      Moodle 3.5
 * @copyright  2019 UCLouvain
 * @author     Dominique Palumbo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @Modifications
 */
class course_module_viewed extends \core\event\course_module_viewed {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'dynamo';
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/dynamo/view.php', array('a' => $this->objectid));
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        return array($this->courseid, 'dynamo', 'view', 'view.php?a=' . $this->objectid,
            $this->objectid, $this->contextinstanceid);
    }

}
