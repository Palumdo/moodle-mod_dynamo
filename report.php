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
 * This create report about students and their group whith data and graphics
 *
 * report 1 : Give the list of participant that no answer in the survey with there emails.
 *
 * report 2 : The yearbook give the pictures of the students and their NIWF (participation level)
 *
 * report 3 : Groups - all students of a group a quick view for the teacher of a group
 *
 * report 4 : individual - it's the most complete report that can be printed for student and group manager(teacher)
 *
 * report 5 : it's a graphic that give a quick view on relative self-assurance of students
 *
 * report 6 : it's an excel with all the data. So teacher can use their own formula and analyses
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_login($course, true, $cm);
$courseid = $course->id;
$modulecontext = context_module::instance($cm->id);
if (!has_capability('mod/dynamo:create', $modulecontext)) {
    redirect(new moodle_url('/my'));
    die;
}
$groups = dynamo_get_groups($dynamo->groupingid);
$canvas = '';
$jscript = '
  <script>
    window.onload = function dynamo_() {';

$class = ['', '', '', '', '', '', ''];
$class[$report] = ' class="active"';
echo '<ul class="dynnav dynnavtabs">
        <li'.$class[1].'><a href="#" onclick="reloadme(1);">'.get_string('dynamoreport01', 'mod_dynamo').'</a></li>
        <li'.$class[5].'><a href="#" onclick="reloadme(5);">'.get_string('dynamoreport05', 'mod_dynamo').'</a></li>
        <li'.$class[2].'><a href="#" onclick="reloadme(2);">'.get_string('dynamoreport02', 'mod_dynamo').'</a></li>
        <li'.$class[3].'><a href="#" onclick="reloadme(3);">'.get_string('dynamoreport03', 'mod_dynamo').'</a></li>
        <li'.$class[4].'><a href="#" onclick="reloadme(4);">'.get_string('dynamoreport04', 'mod_dynamo').'</a></li>
        <li'.$class[6].'><a href="#" onclick="reloadme(6);">'.get_string('dynamoreport06', 'mod_dynamo').'</a></li>
     </ul>';

echo ('<h3 id="top">'.get_string('dynamoreports', 'mod_dynamo').' : ('.$cm->name.')</h3>');
echo ('<input id="activityid"   type="hidden" value="'.$id.'">');
echo ('<input id="groupid"      type="hidden" value="'.$groupid.'">');
echo ('<input id="usrid"        type="hidden" value="'.$usrid.'">');

switch($report) {
    case 1:
        $result = dynamo_get_report_001($dynamo);
        dynamo_rep_list_no_participant($result, $cm->name);
        break;

    case 2:
        $jscript = dynamo_rep_list_all_group($dynamo, $jscript, $display6, $courseid);
        break;

    case 3:
        $jscript = dynamo_rep_list_all_participant($dynamo, $jscript, $display6, $courseid);
        break;

    case 4:
        $jscript = dynamo_rep_all_confidence($dynamo, $jscript, $display6, $zoom);
        break;

    case 5:
        dynamo_rep_yearbook($dynamo, $id);
        break;

    case 6:
        dynamo_rep_excel($cm);
        break;
}

$jscript = $jscript.'
    }; // End of onload...
  </script>';

echo ($jscript);
