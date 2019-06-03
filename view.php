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
 * This is the manager of what will be displayed based on your rights
 * student will see the survey
 * teacher the results and reports
 * (still inspired by teambuilder mod)
 * RGraph library is used because radar graphics is not in moodle (for now)
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo 
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
$PAGE->requires->css('/mod/dynamo/css/all.css');  // fontawesome  5.8.2
$PAGE->requires->css('/mod/dynamo/css/style.css');

echo('<link href="https://fonts.googleapis.com/css?family=Indie+Flower" rel="stylesheet"> ');

// Course_module ID, or
$id         = optional_param('id',      0, PARAM_INT);
// ... module instance id.
// Default tab is the Result -> global view
$d          = optional_param('d',       0, PARAM_INT);
$tab        = optional_param('tab',     2, PARAM_INT); // global view
$report     = optional_param('report',  1, PARAM_INT); // first report
$results    = optional_param('results', 0, PARAM_INT);
$zoom       = optional_param('zoom',    0, PARAM_INT);
$groupid    = optional_param('groupid', 0, PARAM_INT);

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

// UCLouvain faculty colors only usefull at UCL. Other will have a default blue color) 
// can be easily customisable for other faculty. Faculty short name are in course category.
$afcolor = [];
$afcolor['[MEDE]']    = '#88005d';
$afcolor['[FASB]']    = '#88005d';
$afcolor['[FSP]']     = '#88005d';
$afcolor['[FSM]']     = '#88005d';
$afcolor['[TECO]']    = '#b50030';
$afcolor['[EPL]']     = '#032f5d';
$afcolor['[LOCI]']    = '#b50030';
$afcolor['[AGRO]']    = '#008193';
$afcolor['[SC]']      = '#4a96cd';
$afcolor['[DRT]']     = '#e20026';
$afcolor['[FIAL]']    = '#f29400';
$afcolor['[PSP]']     = '#e06c08';
$afcolor['[ESPO]']    = '#76ad1c';
$afcolor['[LSM]']     = '#90810d';
$afcolor['[ILV]']     = '#032f5d';
$afcolor['[ECOPOL]']  = '#032f5d';

$rFaculty = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);
preg_match("/\[[a-zA-Z]+]/", $rFaculty->name, $matches, PREG_OFFSET_CAPTURE, 0);
$faculty  = $matches[0][0];

$facColor = $afcolor[$faculty];
if($facColor == '') {$facColor = '#032f5d';}  


// Base security
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

$group  = dynamo_get_group($dynamo->groupingid,$USER->id);

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

if($mode == 'student') {
    $comment    = dynamo_get_comment($USER->id, $dynamo);
    require_once(__DIR__.'/student.php');
}

if($mode == 'teacher') {
    $usrid = optional_param('usrid', 0, PARAM_INT);

    require_once(__DIR__.'/tabs.php');
    switch($tab) {
        case 1:
            // get the comment of the current inspected user if no user was seen
            // comment will be empty. It's just for a preview... Dummy text can be OK !
            // or empty...
            // preview of what student see for the teacher (no save button !)
            $comment = dynamo_get_comment($usrid, $dynamo);
            require_once(__DIR__.'/student.php');
            break;
        case 2:
            switch($results) {
                case 0:
                case 1:
                    require_once(__DIR__.'/teacher.php');
                    break;
                case 2:
                    require_once(__DIR__.'/teacherlvl0.php');
                    break;
                case 3:
                    require_once(__DIR__.'/teacherlvl1.php');
                    break;
            } 
            break;
        case 3:
            require_once(__DIR__.'/report.php');
            break;

        case 4:
            require_once(__DIR__.'/help.php');
            break;
    }
}

echo $OUTPUT->footer();
