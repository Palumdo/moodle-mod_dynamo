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
$ctxt = context_module::instance($cm->id);
if (!has_capability('mod/dynamo:create', $ctxt)) {
  redirect(new moodle_url('/my'));
  die;
}    

$reportname = "dynamo_export";
$workbook = new MoodleExcelWorkbook('-');

$workbook->send($reportname);
$worksheet = array();
$worksheet[0] = $workbook->add_worksheet(get_string('dynamoexportxlstab1', 'mod_dynamo'));
$worksheet[1] = $workbook->add_worksheet(get_string('dynamoexportxlstab2', 'mod_dynamo'));
$worksheet[2] = $workbook->add_worksheet(get_string('dynamoexportxlstab3', 'mod_dynamo'));

// Page 1
$worksheet[0]->write(0, 0, $dynamo->name);
$worksheet[0]->write(1, 0, date('d/m/Y', $dynamo->timecreated));
$col = 1;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle01', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle02', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle03', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle04', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle05', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle06', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle07', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle08', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle09', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle10', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle11', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle12', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle13', 'mod_dynamo'));
$col++;
$worksheet[0]->write(3, $col, get_string('dynamoexportxlsTitle14', 'mod_dynamo'));
$col++;

$groups = dynamo_get_groups($dynamo->groupingid);
$i=0;
$format = $workbook->add_format(array("bold" => 1, "text_wrap" => true));

$worksheet[0]->set_column(0, 0, '30');
$worksheet[0]->set_column(1, $col, '20');
$line = 0;
foreach($groups as $grp) {
    $grpusrs = dynamo_get_group_users($grp->id);
    $totalp = 0;
    $totals = 0;
    $crit1s = 0;
    $crit1p = 0;
    $crit2s = 0;
    $crit2p = 0;
    $crit3s = 0;
    $crit3p = 0;
    $crit4s = 0;
    $crit4p = 0;
    $crit5s = 0;
    $crit5p = 0;
    $crit6s = 0;
    $crit6p = 0;


    $col = 0;
    $worksheet[0]->write(3 + ($i * 5), $col, $grp->name, $format);
    $worksheet[0]->write(4 + ($i * 5), $col, get_string('dynamoexportxlsTitle19', 'mod_dynamo'));
    $worksheet[0]->write(5 + ($i * 5), $col, get_string('dynamoheadcohesion', 'mod_dynamo'));
    $worksheet[0]->write(6 + ($i * 5), $col, get_string('dynamoheadremarque', 'mod_dynamo'));
    
    foreach($grpusrs as $usr) {
        $data = dynamo_compute_advanced($usr->id, $dynamo); 
        $autoeval = dynamo_get_autoeval($usr->id, $dynamo);
        $niwf = dynamo_get_niwf($dynamo, $grpusrs, $usr->id)[0];
        $conf = dynamo_get_conf($dynamo, $grpusrs, $usr->id);
        $comments = dynamo_get_comment($usr->id, $dynamo);
        
        $crit1s += $autoeval->crit1;
        $crit1p += round($data->autocritsum->total1 / $data->nbeval, 2);
        $crit2s += $autoeval->crit2;
        $crit2p += round($data->autocritsum->total2 / $data->nbeval, 2);
        $crit3s += $autoeval->crit3;
        $crit3p += round($data->autocritsum->total3 / $data->nbeval, 2);
        $crit4s += $autoeval->crit4;
        $crit4p += round($data->autocritsum->total4 / $data->nbeval, 2);
        $crit5s += $autoeval->crit5;
        $crit5p += round($data->autocritsum->total5 / $data->nbeval, 2);
        $crit6s += $autoeval->crit6;
        $crit6p += round($data->autocritsum->total6 / $data->nbeval, 2);

        $total = round(($data->autocritsum->total1 
                        + $data->autocritsum->total2 
                        + $data->autocritsum->total3 
                        + $data->autocritsum->total4 
                        + $data->autocritsum->total5 
                        + $data->autocritsum->total6) / $data->nbeval, 2);

        $totalp += $total;
        $totals += $data->autosum;
        // Page 2
        $col = 0;
        $line++;
        $worksheet[1]->write($line, $col, $usr->lastname);
        $col++;
        $worksheet[1]->write($line, $col, $usr->firstname);
        $col++;
        $worksheet[1]->write($line, $col, $usr->email);
        $col++;
        $worksheet[1]->write($line, $col, $usr->idnumber);
        $col++;
        $worksheet[1]->write($line, $col, $grp->name);
        $col++;
        $worksheet[1]->write($line, $col, $total);
        $col++;
        $worksheet[1]->write($line, $col, $data->autosum);
        $col++;
        $worksheet[1]->write($line, $col, round($niwf, 2));
        $col++;
        $worksheet[1]->write($line, $col, round($conf, 2));
        $col++;
        $worksheet[1]->write($line, $col, $comments->comment1);
        $col++;
        $worksheet[1]->write($line, $col, $comments->comment2);
        $col++;
        $worksheet[1]->write($line, $col, round($data->autocritsum->total1 / $data->nbeval, 2));
        $col++;
        $worksheet[1]->write($line, $col, $autoeval->crit1);
        $col++;
        $worksheet[1]->write($line, $col, round($data->autocritsum->total2 / $data->nbeval, 2));
        $col++;
        $worksheet[1]->write($line, $col, $autoeval->crit2);
        $col++;
        $worksheet[1]->write($line, $col, round($data->autocritsum->total3 / $data->nbeval, 2));
        $col++;
        $worksheet[1]->write($line, $col, $autoeval->crit3);
        $col++;
        $worksheet[1]->write($line, $col, round($data->autocritsum->total4 / $data->nbeval, 2));
        $col++;
        $worksheet[1]->write($line, $col, $autoeval->crit4);
        $col++;
        $worksheet[1]->write($line, $col, round($data->autocritsum->total5 / $data->nbeval, 2));
        $col++;
        $worksheet[1]->write($line, $col, $autoeval->crit5);
        $col++;
        $worksheet[1]->write($line, $col, round($data->autocritsum->total6 / $data->nbeval, 2));
        $col++;
        $worksheet[1]->write($line, $col, $autoeval->crit6);
        $col++;
    }
    // Page 1
    $col = 1;
    $worksheet[0]->write(4 + ($i * 5), $col, round($totalp / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($totals / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit1p / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit1s / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit2p / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit2s / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit3p / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit3s / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit4p / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit4s / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit5p / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit5s / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit6p / count($grpusrs), 2));
    $col++;
    $worksheet[0]->write(4 + ($i * 5), $col, round($crit6s / count($grpusrs), 2));

    $col = 1;
    $oconsistency = dynamo_get_consistency($dynamo, $grpusrs, false);
    $val = [0, 0, 0, 1, 3, 0, 3];
    $notperfect = ($val[$oconsistency->type] * count($grpusrs));
    $climat = dynamo_get_group_climat($dynamo, $grpusrs, $grp->id, $notperfect)[1];
    $climattxt = get_string('dynamoaclimate'.$climat, 'mod_dynamo');

    $worksheet[0]->write(5 + ($i * 5), $col, dynamo_get_group_type_txt($oconsistency->type));
    $worksheet[0]->write(6 + ($i * 5), $col, $climattxt);

    $i++;
}

// Page 2
$col = 0;
$worksheet[1]->write(0, $col, get_string('dynamoheadfirstname', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoheadlastname', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoheademail', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, 'NOMA');
$col++;
$worksheet[1]->write(0, $col, get_string('group'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle01', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle02', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle15', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle16', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle17', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle18', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle03', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle04', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle05', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle06', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle07', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle08', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle09', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle10', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle11', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle12', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle13', 'mod_dynamo'));
$col++;
$worksheet[1]->write(0, $col, get_string('dynamoexportxlsTitle14', 'mod_dynamo'));

// Page 3
$col = 0;
$worksheet[2]->write(0, $col, get_string('group'));
$col++;
$worksheet[2]->write(0, $col, get_string('date'));
$col++;
$worksheet[2]->write(0, $col, get_string('dynamoheadevalfirstname', 'mod_dynamo'));
$col++;
$worksheet[2]->write(0, $col, get_string('dynamoheadevallastname', 'mod_dynamo'));
$col++;
$worksheet[2]->write(0, $col, 'NOMA');
$col++;
$worksheet[2]->write(0, $col, get_string('email'));
$col++;
$worksheet[2]->write(0, $col, get_string('dynamoheadfirstname', 'mod_dynamo'));
$col++;
$worksheet[2]->write(0, $col, get_string('dynamoheadlastname', 'mod_dynamo'));
$col++;
$worksheet[2]->write(0, $col, get_string('dynamoparticipation', 'mod_dynamo'));
$col++;
$worksheet[2]->write(0, $col, get_string('dynamoresponsabilite', 'mod_dynamo'));
$col++;
$worksheet[2]->write(0, $col, get_string('dynamoscientifique', 'mod_dynamo'));
$col++;
$worksheet[2]->write(0, $col, get_string('dynamotechnique', 'mod_dynamo'));
$col++;
$worksheet[2]->write(0, $col, get_string('dynamoattitude', 'mod_dynamo'));
$col++;
$worksheet[2]->write(0, $col, $dynamo->critoptname);
$col++;
$worksheet[2]->write(0, $col, get_string('dynamocommentcontr', 'mod_dynamo'));
$col++;
$worksheet[2]->write(0, $col, get_string('dynamocommentfonction', 'mod_dynamo'));
$row = 1;

$users = dynamo_get_grouping_users($dynamo->groupingid);
foreach($users as $user) {
    $grp = dynamo_get_group_from_user($dynamo->groupingid, $user->id);
    $groupusers = dynamo_get_group_users($grp->id);
    foreach($groupusers as $usereva) {
        $worksheet[2]->write($row, 0, $grp->name);
        $worksheet[2]->write($row, 2, $user->firstname);
        $worksheet[2]->write($row, 3, $user->lastname);
        $worksheet[2]->write($row, 4, $user->idnumber);
        $worksheet[2]->write($row, 5, $user->email);
        $worksheet[2]->write($row, 6, $usereva->firstname);
        $worksheet[2]->write($row, 7, $usereva->lastname);
        $dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' => $user->id , 'userid' => $usereva->id ));
        if ($dynamoeval) {
            $worksheet[2]->write($row, 1,  date('m/d/Y',$dynamoeval->timemodified));
            $worksheet[2]->write($row, 8, $dynamoeval->crit1);
            $worksheet[2]->write($row, 9, $dynamoeval->crit2);
            $worksheet[2]->write($row, 10, $dynamoeval->crit3);
            $worksheet[2]->write($row, 11, $dynamoeval->crit4);
            $worksheet[2]->write($row, 12, $dynamoeval->crit5);
            $worksheet[2]->write($row, 13, $dynamoeval->crit6);
        }    
        $comments = dynamo_get_comment($user->id, $dynamo);
        $worksheet[2]->write($row, 14, $comments->comment1);
        $worksheet[2]->write($row, 15, $comments->comment2);
        $worksheet[2]->write_formula($row, 16, '= SUM(I'.($row+1).':N'.($row+1).')');

        $row++;    
    }
}

$workbook->close();
die;