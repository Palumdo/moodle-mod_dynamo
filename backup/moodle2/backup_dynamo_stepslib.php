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
 * @package     mod_dynamo
 * @category    backup
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete structure for backup, with file and id annotations.
 */
class backup_dynamo_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        // Define each element separated.
        $dynamo = new backup_nested_element('dynamo', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'open', 'close', 'groupid', 'allowupdate'
            , 'crit1', 'crit2', 'crit3','crit4', 'crit5','critopt','critoptname', 'groupingid', 'autoeval'
            , 'groupeval', 'timemodified', 'timecreated', 'comment1','comment2',
        ));

        $evals = new backup_nested_element('evals');
        $eval = new backup_nested_element('eval', array('id'), array(
            'builder', 'evalbyid', 'userid', 'crit1', 'crit2', 'crit3', 'crit4', 'crit5', 'crit6', 'critgrp'
            , 'comment1', 'comment2', 'timemodified',
        ));

        // Build the tree with these elements with $root as the root of the backup tree.
        $dynamo->add_child($evals);
        $evals->add_child($eval);
        // Define the source tables for the elements.
        $dynamo->set_source_table('dynamo', array('id' => backup::VAR_ACTIVITYID));
        $eval->set_source_table('dynamo_eval', array('builder' => backup::VAR_PARENTID));

        // Define id annotations.
        $dynamo->annotate_ids('group', 'groupid');
        $dynamo->annotate_ids('grouping', 'groupingid');
        $eval->annotate_ids('user', 'userid');
        $eval->annotate_ids('user', 'evalbyid');

        // Define file annotations.
        $dynamo->annotate_files('mod_dynamo', 'intro', null);

        return $this->prepare_activity_structure($dynamo);
    }
}