<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
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

//Our involvement ratio has been computed with reference to the following paper that shows NIWF to be one of the best factors to measure peer assesments :
//https://www.tandfonline.com/eprint/ee2eHDqmr2aTEb9t4dB8/full

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

global $OUTPUT;

$id           = optional_param('id', 0, PARAM_INT);         // The course_module ID, or...
$instance     = optional_param('instance', 0, PARAM_INT);   // teamup instance ID.
$courseid     = optional_param('course', 0, PARAM_INT);   // teamup instance ID.

if ($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'dynamo');
    $dynamo = $DB->get_record('dynamo', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    exit();
}


require_login($course, true, $cm);
$ctxt = context_module::instance($cm->id);

$mode = '';

if (has_capability('mod/dynamo:create', $ctxt)) {
    $mode = 'teacher';
}

if($mode == '') {
    redirect(new moodle_url('/my'));
    die();
}  


header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header('Content-Disposition: attachment; filename=Rapport-'.$courseid.'_'.$id.'-'.date("d-m-Y").'.xls');
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);


$groups = dynamo_get_groups($dynamo->groupementid);
$evals  = dynamo_get_grid($dynamo);

foreach ($groups as $grp) { // loop to all groups of grouping

    $grpusrs = dynamo_get_group_users($grp->id);
    echo ('<table class="table">
                <thead>
                    <tr><th style="font-size:16px;font-weight:bold;">'.$grp->name.'</th>');
    foreach ($grpusrs as $grpusr) { // loop to all students of  groups
        echo('        <th>'.$grpusr->firstname.' '.$grpusr->lastname.'</th>');
    }
    echo('          <th>'.get_string('dynamoier', 'mod_dynamo').'</th>');

    echo ('       </tr>
              </thead>
              <tbody>');
    $i = 0;
    $j = 0;
    foreach ($grpusrs as $grpusr) { // loop to all students of  groups
        $totals = 0;
        echo('        <tr>
                        <td>'.$grpusr->firstname.' '.$grpusr->lastname.'</td>');
        $j=0;
        foreach ($grpusrs as $grpusrev) {
            if($grpusrev->id != $grpusr->id) {
                $total = dynamo_get_total($evals, $grpusrev->id ,$grpusr->id);
                $totals += $total;
                echo('        <td>'.$total.'</td>');
            } else {
                $total = dynamo_get_total($evals, $grpusrev->id ,$grpusr->id);
                echo('        <td>'.$total.'</td>');
            }
            j++;
        }  
        echo('        <td>'.$totals.'</td>');
        echo('        </tr>');
        $i++;
    }
  
    echo('          <tr>');
    echo('            <td>'.get_string('dynamoniwf', 'mod_dynamo').'</td>');

    foreach ($grpusrs as $grpusr) {
      $niwf = dynamo_get_niwf($dynamo, $grpusrs, $grpusr->id);
      echo('        <td>'.number_format($niwf[0],2,',', ' ').'</td>');
    }  
    echo('          </tr>');
    echo('     </tbody>
           </table>');
}
?>    
