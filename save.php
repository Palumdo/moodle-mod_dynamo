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
 * save the evaluation of students
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $USER;

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$d  = optional_param('d', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('dynamo', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $dynamo         = $DB->get_record('dynamo', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($d) {
    $dynamo         = $DB->get_record('dynamo', array('id' => $d), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $dynamo->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('dynamo', $dynamo->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', dynamo));
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

$group      = dynamo_get_group($dynamo->groupementid,$USER->id);

if($group == null) {
    redirect(new moodle_url('/my'));
    die();
}  

$groupusers = dynamo_get_group_users($group->id);
$PAGE->set_url('/mod/dynamo/save.php', array('id' => $cm->id));
$PAGE->set_title(format_string($dynamo->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
echo $OUTPUT->header();


//*********************
// server side data validation
//*********************
foreach ($groupusers as $user) {
    $error = 0;
    if($dynamo->autoeval == 0 &&  $user->id == $USER->id) { // no auto evaluation check...
    } else {
      if($_POST[$user->id.'_1'] <1 ||  $_POST[$user->id.'_1'] > 5) $error++;
      if($_POST[$user->id.'_2'] <1 ||  $_POST[$user->id.'_2'] > 5) $error++;
      if($_POST[$user->id.'_3'] <1 ||  $_POST[$user->id.'_3'] > 5) $error++;
      if($_POST[$user->id.'_4'] <1 ||  $_POST[$user->id.'_4'] > 5) $error++;
      if($_POST[$user->id.'_5'] <1 ||  $_POST[$user->id.'_5'] > 5) $error++;
      if($dynamo->critoptname != '' && ($_POST[$user->id.'_6'] <1 ||  $_POST[$user->id.'_6'] > 5))  $error++;
      if(trim($_POST['comment1']) == '') $error++;
      if(trim($_POST['comment2']) == '') $error++; 
    
      if($error > 0) {
        echo("<div style='width:100%;height:32px;border:1px solid red;background-color:#ffffd2;text-align:center;vertical-align:middle;border-radius:15px;display:block;font-weight:bold;padding-top:15px;'>");
        echo(get_string('dynamosavedcorrupted', 'mod_dynamo'));
        echo("</div>");
        die(0);
      }
    }
}
// The all group is also evaluated
if($dynamo->groupeval == 1) {
    $error = 0;

    if($_POST[$group->id.'_g1'] <1 || $_POST[$group->id.'_g1'] > 5) $error++;
    if($_POST[$group->id.'_g2'] <1 || $_POST[$group->id.'_g2'] > 5) $error++;
    if($_POST[$group->id.'_g3'] <1 || $_POST[$group->id.'_g3'] > 5) $error++;
    if($_POST[$group->id.'_g4'] <1 || $_POST[$group->id.'_g4'] > 5) $error++;
    if($_POST[$group->id.'_g5'] <1 || $_POST[$group->id.'_g5'] > 5) $error++;
    // display message if they've an error
    if($error > 0) {
        echo("<div style='width:100%;height:32px;border:1px solid red;background-color:#ffffd2;text-align:center;vertical-align:middle;border-radius:15px;display:block;font-weight:bold;padding-top:15px;'>");
        echo(get_string('dynamosavedcorrupted', 'mod_dynamo'));
        echo("</div>");
        die(0);
    }
}    

//*********************
// save peer evaluation
//*********************
foreach ($groupusers as $user) {
    if($dynamo->autoeval == 0 &&  $user->id == $USER->id) { // no auto evaluation save...
    } else {
      $dynamoeval = new stdClass();
      $dynamoeval->builder      = $cm->instance;
      $dynamoeval->evalbyid     = $USER->id;
      $dynamoeval->userid       = $user->id;
      $dynamoeval->crit1        = $_POST[$user->id.'_1'];
      $dynamoeval->crit2        = $_POST[$user->id.'_2'];
      $dynamoeval->crit3        = $_POST[$user->id.'_3'];
      $dynamoeval->crit4        = $_POST[$user->id.'_4'];
      $dynamoeval->crit5        = $_POST[$user->id.'_5'];
      if($dynamo->critoptname != '') $dynamoeval->crit6 = $_POST[$user->id.'_6'];
      else $dynamoeval->crit6   = 0;
      $dynamoeval->critgrp      = 0;
      $dynamoeval->comment1     = $_POST['comment1']; 
      $dynamoeval->comment2     = $_POST['comment2']; 
      $dynamoeval->timemodified = time();
      
      
       if (!$id = $DB->get_record('dynamo_eval', array('builder' => $cm->instance, 'evalbyid' =>$USER->id , 'userid' =>$user->id ))) {
        $id = $DB->insert_record('dynamo_eval', $dynamoeval);    
       } else {
         $dynamoeval->id = $id->id;
         $DB->update_record('dynamo_eval', $dynamoeval);
       }
    }
}      
// save groupe evaluation
if($dynamo->groupeval == 1) {
    $dynamoeval = new stdClass();
    $dynamoeval->builder      = $cm->instance;
    $dynamoeval->evalbyid     = $USER->id;
    $dynamoeval->userid       = $group->id;
    $dynamoeval->crit1        = $_POST[$group->id.'_g1'];
    $dynamoeval->crit2        = $_POST[$group->id.'_g2'];
    $dynamoeval->crit3        = $_POST[$group->id.'_g3'];
    $dynamoeval->crit4        = $_POST[$group->id.'_g4'];
    $dynamoeval->crit5        = $_POST[$group->id.'_g5'];
    if($dynamo->critoptname != '') $dynamoeval->crit6 = $_POST[$group->id.'_g6'];
    else $dynamoeval->crit6 = 0;
    $dynamoeval->critgrp      = 1;
    $dynamoeval->comment1     = $_POST['comment1']; 
    $dynamoeval->comment2     = $_POST['comment2']; 
    $dynamoeval->timemodified = time();
  
    if (!$id = $DB->get_record('dynamo_eval', array('builder' => $cm->instance, 'evalbyid' =>$USER->id , 'userid' =>$group->id ))) {
        $id = $DB->insert_record('dynamo_eval', $dynamoeval);    
    } else {
        $dynamoeval->id = $id->id;
        $DB->update_record('dynamo_eval', $dynamoeval);
    }
}  

// display message if all is saved successfully
echo("<div style='width:100%;height:32px;border:1px solid green;background-color:#ffffd2;text-align:center;vertical-align:middle;border-radius:15px;display:block;font-weight:bold;padding-top:15px;'>");
echo(get_string('dynamosavedsuccessfully', 'mod_dynamo'));
echo("</div>");
echo('<script>setInterval(function(){location.href = "/course/view.php?id='.$cm->course.'";},5000);</script>');
echo $OUTPUT->footer();
?>