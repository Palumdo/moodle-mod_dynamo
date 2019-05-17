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
 * @package    mod_teamup
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

$ctxt   = context_module::instance($cm->id);
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


/*




require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

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

$sql = " 
SELECT * FROM (
(SELECT  RAND() id, 0 crit, t5.name grouping, t6.name groupname, t3.idnumber, t3.firstname evalfirstname, t3.lastname evallastname, t4.firstname, t4.lastname, FROM_UNIXTIME(t2.timemodified , '%Y-%m-%d %h:%i:%s') date, t2.crit1, t2.crit2, t2.crit3, t2.crit4, t2.crit5, t2.crit6, t2.comment1, t2.comment2
  FROM {dynamo}             t1
      ,{dynamo_eval}        t2
      ,{user}               t3 
      ,{user}               t4
      ,{groupings}          t5
      ,(SELECT tt2.name, tt3.userid
          FROM {groupings_groups} tt1
              ,{groups}           tt2
              ,{groups_members}   tt3
         WHERE tt1.groupingid = (SELECT groupingid
                                   FROM {dynamo} tx
                                  WHERE course = :param1
                                    AND tx.id = (SELECT cm.instance
                                                  FROM {course_modules} cm
                                                  JOIN {course} c ON c.id = cm.course
                                                 WHERE cm.id = :param2
                                                )
                                 )
           AND tt2.id         = tt1.groupid
           AND tt3.groupid    = tt2.id   
                              ) t6   
 WHERE t2.builder     = (SELECT id
                          FROM {dynamo} tx
                         WHERE course = :param3
                           AND tx.id = (SELECT cm.instance
                                          FROM {course_modules} cm
                                          JOIN {course} c ON c.id = cm.course
                                         WHERE cm.id = :param4
                                        )
                        )
   AND t1.id          = t2.builder
   AND t2.critgrp     = 0
   AND t3.id          = t2.evalbyid
   AND t4.id          = t2.userid
   AND t5.id          = t1.groupingid
   AND t6.userid      = t2.evalbyid)
UNION
(SELECT RAND() id,1 crit, t5.name grouping, t6.name groupname, t3.idnumber ,t3.firstname evalfirstname, t3.lastname evallastname, t4.name lastname, t4.name firstname, FROM_UNIXTIME(t2.timemodified , '%Y-%m-%d %h:%i:%s') date, t2.crit1, t2.crit2, t2.crit3, t2.crit4, t2.crit5, t2.crit6, t2.comment1, t2.comment2
  FROM {dynamo}             t1
      ,{dynamo_eval}        t2
      ,{user}               t3 
      ,{groups}             t4
      ,{groupings}          t5
      ,(SELECT tt2.name, tt3.userid
          FROM {groupings_groups} tt1
              ,{groups}           tt2
              ,{groups_members}   tt3
         WHERE tt1.groupingid = (SELECT groupingid
                                   FROM {dynamo} tx
                                  WHERE course = :param5
                                    AND tx.id = (SELECT cm.instance
                                                  FROM {course_modules} cm
                                                  JOIN {course} c ON c.id = cm.course
                                                 WHERE cm.id = :param6
                                                )
                                 )
           AND tt2.id         = tt1.groupid
           AND tt3.groupid    = tt2.id   
                              ) t6   
 WHERE t2.builder     = (SELECT id
                          FROM {dynamo} tx
                         WHERE course = :param7
                           AND tx.id = (SELECT cm.instance
                                          FROM {course_modules} cm
                                          JOIN {course} c ON c.id = cm.course
                                         WHERE cm.id = :param8
                                        )
                        )
   AND t1.id          = t2.builder
   AND t2.critgrp     = 1
   AND t3.id          = t2.evalbyid
   AND t4.id          = t2.userid
   AND t5.id          = t1.groupingid
   AND t6.userid      = t2.evalbyid)
) aaa     
ORDER by grouping, groupname, evalfirstname, crit, firstname
";  

$params = array('param1' => $courseid, 'param2' => $id, 'param3' => $courseid, 'param4' => $id, 'param5' => $courseid, 'param6' => $id, 'param7' => $courseid, 'param8' => $id);
$result = $DB->get_records_sql($sql, $params);
//$result = $DB->get_records_sql($sql);
    
$output = '<table class="table table-bordered">
      <tr>  
        <th>'.get_string('dynamoheadgrouping', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamoheadgroup', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamoheaddate', 'mod_dynamo').'</th>  
        <th>NOMA</th>  
        <th>'.get_string('dynamoheadevalfirstname', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamoheadevallastname', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamoheadfirstname', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamoheadlastname', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamoparticipation', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamoresponsabilite', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamoscientifique', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamotechnique', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamoattitude', 'mod_dynamo').'</th>  
        <th>'.$dynamo->critoptname.'</th>  
        <th>'.get_string('dynamocommentcontr', 'mod_dynamo').'</th>  
        <th>'.get_string('dynamocommentfonction', 'mod_dynamo').'</th>  
      </tr>';

foreach ($result as $row) {
   $output .= '
      <tr>  
        <td>'.$row->grouping.'</td>  
        <td>'.$row->groupname.'</td>  
        <td>'.$row->date.'</td>  
        <td>'.$row->idnumber.'</td> 
        <td>'.$row->evalfirstname.'</td> 
        <td>'.$row->evallastname.'</td>
        <td>'.$row->firstname.'</td> 
        <td>'.$row->lastname.'</td>
        <td>'.$row->crit1.'</td>  
        <td>'.$row->crit2.'</td>  
        <td>'.$row->crit3.'</td>  
        <td>'.$row->crit4.'</td>  
        <td>'.$row->crit5.'</td>  
        <td>'.$row->crit6.'</td>  
        <td>'.$row->comment1.'</td>  
        <td>'.$row->comment2.'</td>  
      </tr>';  
}
$output .= '</table>';



//header('Content-Type: application/xls; charset=utf-8');
//header('Content-Disposition: attachment; filename=Rapport-'.$courseid.'_'.$id.'-'.date("d-m-Y").'.xls');

header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-Disposition: attachment; filename=abc.xls");  //File name extension was wrong
header('Content-Disposition: attachment; filename=Rapport-'.$courseid.'_'.$id.'-'.date("d-m-Y").'.xls');
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

echo $output;    
?>

*/