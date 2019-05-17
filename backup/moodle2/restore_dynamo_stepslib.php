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
 * All the steps to restore mod_dynamo are defined here.
 *
 * @package     mod_dynamo
 * @category    restore
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo  
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For more information about the backup and restore process, please visit:
// https://docs.moodle.org/dev/Backup_2.0_for_developers
// https://docs.moodle.org/dev/Restore_2.0_for_developers

/**
 * Defines the structure step to restore one mod_dynamo activity.
 */
class restore_dynamo_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     */
    protected function define_structure() {
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $dynamo = new restore_path_element('dynamo', '/activity/dynamo');
        $paths[] = $dynamo;

        if ($userinfo) {
          $eval = new restore_path_element('dynamo_eval', '/activity/dynamo/evals/eval');
          $paths[] = $eval;
        }
        
        return $this->prepare_activity_structure($paths);
    }

    
        protected function process_dynamo($data) {
        global $DB;

        $data                 = (object)$data;
        $oldid                = $data->id;
        $data->course         = $this->get_courseid();
        $data->groupid        = $this->get_mappingid('group', $data->groupid);
        $data->groupingid     = $this->get_mappingid('grouping', $data->groupingid);

        // Insert the dynamo record.
        $newid = $DB->insert_record('dynamo', $data);

        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newid);
    }

    protected function process_dynamo_eval($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
/*        
        $data->evalbyid = $this->get_mappingid('user', $data->evalbyid);
        $data->userid   = $this->get_mappingid('user', $data->userid);
*/
        $data->builder  = $this->get_new_parentid('dynamo');
        $newevalid = $DB->insert_record('dynamo_eval', $data);
        
        $this->set_mapping('dynamo_eval', $oldid, $newevalid);
    }

    
    /**
     * Defines post-execution actions.
     */
    protected function after_execute() {
        global $DB;

        $this->add_related_files('mod_dynamo', 'intro', null);
    }
}
