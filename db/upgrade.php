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
 * Upgrade steps for dynamo.
 *
 * @package    mod_dynamo
 * @copyright  2019 UCLouvain
 * @author     Dominique Palumbo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_dynamo_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019030200) {
        $table = new xmldb_table('dynamo');
        $field = new xmldb_field('comment1', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'timecreated');

      // Conditionally launch add field tablenbline.
      if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
      }

      $field = new xmldb_field('comment2', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'timecreated');
      if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
      }
      
      // dynamo savepoint reached.
      upgrade_plugin_savepoint(true, 2019030200, 'mod', 'dynamo');        
    }

    return true;
}