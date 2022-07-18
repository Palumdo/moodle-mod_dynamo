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
 * Validate & save a peer evaluation made by a student
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
$PAGE->requires->css('/mod/dynamo/css/style.css');

global $USER;

require_sesskey();

// Module instance id.
$d = optional_param('d', 0, PARAM_INT);
// Course_module ID.
$id = optional_param('id', 0, PARAM_INT);
if ($id) {
    $cm = get_coursemodule_from_id('dynamo', $id, 0, false, MUST_EXIST);
    $dynamo = $DB->get_record('dynamo', array('id' => $cm->instance), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
} else if ($d) {
    // Get dynamo data first.
    $dynamo = $DB->get_record('dynamo', array('id' => $d), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $dynamo->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('dynamo', $dynamo->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('missingidandcmid', 'dynamo');
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

$coursecontext = context_course::instance($course->id);
$GLOBALS['dynamo_contextid'] = $modulecontext->id;
$GLOBALS['dynamo_courseid'] = $course->id;

$group = dynamo_get_group($dynamo->groupingid, $USER->id);

if ($group == null) {
    redirect(new moodle_url('/my'));
    die();
}

$groupusers = dynamo_get_group_users($group->id);
$PAGE->set_url('/mod/dynamo/save.php', array('id' => $cm->id));
$PAGE->set_title(format_string($dynamo->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
echo $OUTPUT->header();

/**************************************
 Server side data validation.
 Value must between 1 to 5 (all question are mandatory).
 **************************************/
if (confirm_sesskey() && $datarecord = data_submitted()) {
    foreach ($groupusers as $user) {
        $error = 0;
        if ($dynamo->autoeval == 0 && $user->id == $USER->id) { // No auto evaluation check...
            $error = 0;
        } else {
            $crit1 = required_param($user->id.'_1', PARAM_INT);
            $crit2 = required_param($user->id.'_2', PARAM_INT);
            $crit3 = required_param($user->id.'_3', PARAM_INT);
            $crit4 = required_param($user->id.'_4', PARAM_INT);
            $crit5 = required_param($user->id.'_5', PARAM_INT);
            $comment1 = required_param('comment1', PARAM_TEXT);
            $comment2 = required_param('comment2', PARAM_TEXT);
            $crit6 = optional_param($user->id.'_6', 0, PARAM_INT);

            if ($crit1 < 1 || $crit1 > 5) {
                $error++;
            }
            if ($crit2 < 1 || $crit2 > 5) {
                $error++;
            }
            if ($crit3 < 1 || $crit3 > 5) {
                $error++;
            }
            if ($crit4 < 1 || $crit4 > 5) {
                $error++;
            }
            if ($crit5 < 1 || $crit5 > 5) {
                $error++;
            }

            if (trim($comment1) == '') {
                $error++;
            }
            if (trim($comment2) == '') {
                $error++;
            }

            if ($error > 0) {
                echo("<div class='errormsgserver'>");
                echo(get_string('dynamosavedcorrupted', 'mod_dynamo'));
                echo("</div>");
                die(0);
            }
        }
    }
    // The all group is also evaluated.
    if ($dynamo->groupeval == 1) {
        $error = 0;

        $group1 = required_param($group->id.'_g1', PARAM_INT);
        $group2 = required_param($group->id.'_g2', PARAM_INT);
        $group3 = required_param($group->id.'_g3', PARAM_INT);
        $group4 = required_param($group->id.'_g4', PARAM_INT);
        $group5 = required_param($group->id.'_g5', PARAM_INT);
        $group6 = optional_param($group->id.'_g6', 0, PARAM_INT);

        if ($group1 < 1 || $group1 > 5) {
              $error++;
        }
        if ($group2 < 1 || $group2 > 5) {
              $error++;
        }
        if ($group3 < 1 || $group3 > 5) {
              $error++;
        }
        if ($group4 < 1 || $group4 > 5) {
              $error++;
        }
        if ($group5 < 1 || $group5 > 5) {
              $error++;
        }
        // Display message if they've an error.
        if ($error > 0) {
            echo("<div class='errormsgserver'>");
            echo(get_string('dynamosavedcorrupted', 'mod_dynamo'));
            echo("</div>");
            die(0);
        }
    }

    /*********************
     Save peer evaluation.
    *********************/
    foreach ($groupusers as $user) {
        if ($dynamo->autoeval == 0 && $user->id == $USER->id) {
            // No auto evaluation save.
            $error = 0;
        } else {
            $dynamoeval = new stdClass();
            $dynamoeval->builder = $cm->instance;
            $dynamoeval->evalbyid = $USER->id;
            $dynamoeval->userid = $user->id;
            $dynamoeval->crit1 = $crit1;
            $dynamoeval->crit2 = $crit2;
            $dynamoeval->crit3 = $crit3;
            $dynamoeval->crit4 = $crit4;
            $dynamoeval->crit5 = $crit5;
            if ($dynamo->critoptname != '') {
                $dynamoeval->crit6 = $crit6;
            } else {
                $dynamoeval->crit6 = 0;
            }
            $comment1 = required_param('comment1', PARAM_TEXT);
            $comment2 = required_param('comment2', PARAM_TEXT);

            $dynamoeval->critgrp = 0;
            $dynamoeval->comment1 = format_string($comment1);
            $dynamoeval->comment2 = format_string($comment2);
            $dynamoeval->timemodified = time();

            if (!$id = $DB->get_record('dynamo_eval', array('builder' => $cm->instance, 'evalbyid' => $USER->id
                    , 'userid' => $user->id ))) {
                $id = $DB->insert_record('dynamo_eval', $dynamoeval);
            } else {
                $dynamoeval->id = $id->id;
                $DB->update_record('dynamo_eval', $dynamoeval);
            }
        }
    }
    // Save groupe evaluation.
    if ($dynamo->groupeval == 1) {
        $dynamoeval = new stdClass();
        $dynamoeval->builder = $cm->instance;
        $dynamoeval->evalbyid = $USER->id;
        $dynamoeval->userid = $group->id;
        $dynamoeval->crit1 = $group1;
        $dynamoeval->crit2 = $group2;
        $dynamoeval->crit3 = $group3;
        $dynamoeval->crit4 = $group4;
        $dynamoeval->crit5 = $group5;
        if ($dynamo->critoptname != '') {
            $dynamoeval->crit6 = $group6;
        } else {
            $dynamoeval->crit6 = 0;
        }
        $dynamoeval->critgrp = 1;
        $comment1 = required_param('comment1', PARAM_TEXT);
        $comment2 = required_param('comment2', PARAM_TEXT);

        $dynamoeval->comment1 = format_string($comment1);
        $dynamoeval->comment2 = format_string($comment2);
        $dynamoeval->timemodified = time();

        if (!$id = $DB->get_record('dynamo_eval',
                array('builder' => $cm->instance, 'evalbyid' => $USER->id , 'userid' => $group->id ))) {
            $id = $DB->insert_record('dynamo_eval', $dynamoeval);
        } else {
            $dynamoeval->id = $id->id;
            $DB->update_record('dynamo_eval', $dynamoeval);
        }
    }
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);

    // Display message if all is saved successfully.
    echo("<div class='successmsgserver'>");
    echo(get_string('dynamosavedsuccessfully', 'mod_dynamo'));
    echo("</div>");
    echo('<script>setInterval(function(){location.href = "/course/view.php?id='.$cm->course.'";},5000);</script>');
} else {
    echo("<div class='errormsgserver'>");
    echo(get_string('dynamosavedcorrupted', 'mod_dynamo'));
    echo("</div>");
    die(0);
}
echo $OUTPUT->footer();
