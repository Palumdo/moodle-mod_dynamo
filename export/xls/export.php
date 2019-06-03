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
 * Controller for creating excel export
 * the Excel export have all student of the course and their groups
 *
 * @package    mod_dynamo
 * @copyright  UCLouvain
 * @author     Palumbo Dominique 
**/

global $CFG, $SESSION, $DB;

require_once(__DIR__.'/../../../../config.php');
require_once($CFG->dirroot.'/lib/excellib.class.php');
require_once $CFG->dirroot.'/user/profile/lib.php';

$id = optional_param('id', 0, PARAM_INT); // The course_module ID, or...

if ($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'dynamo');
    $dynamo = $DB->get_record('dynamo', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    die;
}    

require_login($course, true, $cm);
$ctxt   = context_module::instance($cm->id);
if (!has_capability('mod/dynamo:create', $ctxt)) {
  redirect(new moodle_url('/my'));
  die;
}    


$reportname = "dynamo_export";
$workbook = new MoodleExcelWorkbook('-');

$workbook->send($reportname);
$worksheet = array();
$worksheet[0] = $workbook->add_worksheet('flat');

$col = 0;
$worksheet[0]->write(0, $col, get_string('group'));
$col++;
$worksheet[0]->write(0, $col, get_string('date'));
$col++;

$worksheet[0]->write(0, $col, get_string('dynamoheadevalfirstname', 'mod_dynamo'));
$col++;
$worksheet[0]->write(0, $col, get_string('dynamoheadevallastname', 'mod_dynamo'));
$col++;
$worksheet[0]->write(0, $col, 'NOMA');
$col++;
$worksheet[0]->write(0, $col, get_string('email'));
$col++;
$worksheet[0]->write(0, $col, get_string('dynamoheadfirstname', 'mod_dynamo'));
$col++;
$worksheet[0]->write(0, $col, get_string('dynamoheadlastname', 'mod_dynamo'));
$col++;
$worksheet[0]->write(0, $col, get_string('dynamoparticipation', 'mod_dynamo'));
$col++;
$worksheet[0]->write(0, $col, get_string('dynamoresponsabilite', 'mod_dynamo'));
$col++;
$worksheet[0]->write(0, $col, get_string('dynamoscientifique', 'mod_dynamo'));
$col++;
$worksheet[0]->write(0, $col, get_string('dynamotechnique', 'mod_dynamo'));
$col++;
$worksheet[0]->write(0, $col, get_string('dynamoattitude', 'mod_dynamo'));
$col++;
$worksheet[0]->write(0, $col, $dynamo->critoptname);
$col++;
$worksheet[0]->write(0, $col, get_string('dynamocommentcontr', 'mod_dynamo'));
$col++;
$worksheet[0]->write(0, $col, get_string('dynamocommentfonction', 'mod_dynamo'));
$row = 1;

$users  = dynamo_get_grouping_users($dynamo->groupingid);
foreach($users as $user) {
    $grp        = dynamo_get_group_from_user($dynamo->groupingid, $user->id);
    $groupusers = dynamo_get_group_users($grp->id);
    foreach($groupusers as $usereva) {
        $worksheet[0]->write($row, 0, $grp->name);
        $worksheet[0]->write($row, 2, $user->firstname);
        $worksheet[0]->write($row, 3, $user->lastname);
        $worksheet[0]->write($row, 4, $user->idnumber);
        $worksheet[0]->write($row, 5, $user->email);
        $worksheet[0]->write($row, 6, $usereva->firstname);
        $worksheet[0]->write($row, 7, $usereva->lastname);
        $dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' => $user->id , 'userid' => $usereva->id ));
        if ($dynamoeval) {
            $worksheet[0]->write($row, 1,  date('m/d/Y',$dynamoeval->timemodified));
            $worksheet[0]->write($row, 8, $dynamoeval->crit1);
            $worksheet[0]->write($row, 9, $dynamoeval->crit2);
            $worksheet[0]->write($row, 10, $dynamoeval->crit3);
            $worksheet[0]->write($row, 11, $dynamoeval->crit4);
            $worksheet[0]->write($row, 12, $dynamoeval->crit5);
            $worksheet[0]->write($row, 13, $dynamoeval->crit6);
        }    
        $comments = dynamo_get_comment($user->id, $dynamo);
        $worksheet[0]->write($row, 14, $comments->comment1);
        $worksheet[0]->write($row, 15, $comments->comment2);
        $row++;    
    }
}

$worksheet[1] = $workbook->add_worksheet('computed');

$workbook->close();
die;