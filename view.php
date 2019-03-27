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
 * Prints an instance of dynamo.
 *
 * @package     dynamo
 * @copyright   2018 UCLouvain
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $USER;

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/mod/dynamo/js/RGraph/libraries/RGraph.common.core.js');
$PAGE->requires->js('/mod/dynamo/js/RGraph/libraries/RGraph.common.dynamic.js');
$PAGE->requires->js('/mod/dynamo/js/RGraph/libraries/RGraph.common.key.js');
$PAGE->requires->js('/mod/dynamo/js/RGraph/libraries/RGraph.drawing.rect.js');
$PAGE->requires->js('/mod/dynamo/js/RGraph/libraries/RGraph.radar.js');
$PAGE->requires->js('/mod/dynamo/js/RGraph/libraries/RGraph.bar.js');
$PAGE->requires->js('/mod/dynamo/js/local.js');
$PAGE->requires->css('/mod/dynamo/css/all.css');
$PAGE->requires->css('/mod/dynamo/css/style.css');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$d  = optional_param('d', 0, PARAM_INT);

$tab = optional_param('tab', 2, PARAM_INT);
$report = optional_param('report', 0, PARAM_INT);

// groupid
$groupid  = optional_param('groupid', 0, PARAM_INT);

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

$mode = '';


if (has_capability('mod/dynamo:create', $modulecontext)) {
    $mode = 'teacher';
} else {
    require_capability('mod/dynamo:respond', $modulecontext);
    $mode = 'student';
}

if($mode == '') {
  redirect(new moodle_url('/my'));
  die();
}  

$group      = dynamo_get_group($dynamo->groupementid,$USER->id);

if($mode == 'student' && $group == null) {
  redirect(new moodle_url('/my'));
  die();
}  

$groupusers = dynamo_get_group_users($group->id);

$display6   = '';
if($dynamo->critoptname == '') $display6 = 'none';


$PAGE->set_url('/mod/dynamo/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($dynamo->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
echo $OUTPUT->header();

if($mode == 'student'  /*or $group != null */ ) {
  $comment    = dynamo_get_comment($USER->id, $dynamo);
  require_once(__DIR__.'/student.php');
}

if($mode == 'teacher') {
  $usrid = optional_param('usrid', 0, PARAM_INT);

  require_once(__DIR__.'/tabs.php');
  if($tab == 1) {
    $comment    = dynamo_get_comment($usrid, $dynamo);
    require_once(__DIR__.'/preview.php');
  }
  if($tab == 2) {
    require_once(__DIR__.'/teacher.php');
  }
  if($tab == 3) {
    require_once(__DIR__.'/teacherlvl0.php');
  }
  if($tab == 4) {
    require_once(__DIR__.'/report.php');
  }
  if($tab == 5) {
    require_once(__DIR__.'/teacherlvl1.php');
  } 
}

echo $OUTPUT->footer();
