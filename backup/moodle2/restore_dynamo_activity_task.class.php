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
 * The task that provides a complete restore of mod_dynamo is defined here.
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

require_once($CFG->dirroot.'//mod/dynamo/backup/moodle2/restore_dynamo_stepslib.php');

/**
 * Restore task for mod_dynamo.
 */
class restore_dynamo_activity_task extends restore_activity_task {

    /**
     * Defines particular settings that this activity can have.
     */
    protected function define_my_settings() {
    }

    /**
     * Defines particular steps that this activity can have.
     *
     * @return base_step.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_dynamo_activity_structure_step('dynamo_structure', 'dynamo.xml'));
    }

    /**
     * Defines the contents in the activity that must be processed by the link decoder.
     *
     * @return array.
     */
    static public function define_decode_contents() {
        $contents = array();

        // Define the contents.

        return $contents;
    }

    /**
     * Defines the decoding rules for links belonging to the activity to be executed by the link decoder.
     *
     * @return array.
     */
    static public function define_decode_rules() {
        $rules = array();

        // Define the rules.
      //  $rules[] = new restore_decode_rule('DYNAMOVIEWBYID', '/mod/dynamo/view.php?id=$1', 'course_module');

        return $rules;
    }

    /**
     * Defines the restore log rules that will be applied by the
     * {@link restore_logs_processor} when restoring mod_dynamo logs. It
     * must return one array of {@link restore_log_rule} objects.
     *
     * @return array.
     */
    static public function define_restore_log_rules() {
        $rules = array();

        // Define the rules.
        $rules[] = new restore_log_rule('dynamo', 'add', 'view.php?id={course_module}', '{dynamo}');
        $rules[] = new restore_log_rule('dynamo', 'update', 'view.php?id={course_module}', '{dynamo}');
        $rules[] = new restore_log_rule('dynamo', 'view', 'view.php?id={course_module}', '{dynamo}');

        return $rules;
    }
    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    public static function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('dynamo', 'view all', 'index.php?id={course}', null);

        return $rules;
    }    
}
