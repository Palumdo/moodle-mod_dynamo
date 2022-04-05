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
 * This page is for the teacher. It will display all groups information on summary.
 *
 * The aim it's to detect quickly groups with trouble
 * It's the global view tab.
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Our involvement ratio has been computed with reference to the following paper that shows NIWF to be one of the best factors.
// To measure peer assesments :.
// https://www.tandfonline.com/eprint/ee2eHDqmr2aTEb9t4dB8/full.

defined('MOODLE_INTERNAL') || die();

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);
if (!has_capability('mod/dynamo:create', $modulecontext)) {
    redirect(new moodle_url('/my'));
    die;
}

$stat = dynamo_get_grouping_stat($dynamo);
$groups = dynamo_get_groups($dynamo->groupingid);

// Sub tabulation for teacher to see student results in three levels.
echo'<div class="dynamocontent">';
echo '<ul class="dynnav dynnavtabs" style="margin-top:10px;">
        <li class="active"><a href="view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=2&results=1">'
            .get_string('dynamoresults1', 'mod_dynamo').'</a></li>
        <li><a href="view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=2&results=2">'
            .get_string('dynamoresults2', 'mod_dynamo').'</a></li>
        <li><a href="view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=2&results=3">'
            .get_string('dynamoresults3', 'mod_dynamo').'</a></li>
    </ul>';

echo ('<h3>'.get_string('dynamostudenttitle', 'mod_dynamo').' : '
    .$cm->name.'</h3><input id="activityid" type="hidden" value="'.$id.'">');
if ($stat->grouping->description != '') {
    echo ('<div>'.$stat->grouping->name.' : '.$stat->grouping->description.'</div>');
}
echo ('<div id="pleasewait">'.get_string('dynamopleasewait', 'mod_dynamo').'</div>');

// Custom chckboxes that look like switch to hide group with no problems or group where student answers are missing and switch view.
// Table to div.
echo ('<div id="button-list-teacher" style="width:100%;margin:15px;display:none;">
        <div class="box-switch"><div class="box-switch-label">'.get_string('dynamoremovegroupnoprobs',  'mod_dynamo').'</div>
          <label class="switch">
            <input type="checkbox" value="on" onclick="hidenoprob();">
            <span class="slider"></span>
          </label>
        </div>
        <div class="box-switch"><div class="box-switch-label">'.get_string('dynamoremovegroupnotcomplete',  'mod_dynamo').'</div>
          <label class="switch">
            <input type="checkbox" onclick="hidenotcomplete();">
            <span class="slider"></span>
          </label>
        </div>

        <div class="box-switch" style="text-align:left;max-width:300px;width:300px;"><div style="padding:15px;">
         '.get_string('dynamogroupcount', 'mod_dynamo').' : '.$stat->nb_group.'<br>
         '.get_string('dynamostudentcount', 'mod_dynamo').' : '.$stat->nb_participant.'<br>
         '.get_string('dynamostudentnoanswerscount', 'mod_dynamo').' : <a href="/mod/dynamo/view.php?id=
         '.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&report=1&tab=3&results=1">'.$stat->nb_no_answer.'</a></div>
        </div>
        <div style="float: left; margin: 3px;">
            <button class="btn btn-default" onclick="removeColors();$(this).css(\'display\',\'none\');
                $(\'#dynamorefresh\').css(\'display\',\'\');">'.get_string('dynamoremovecolors', 'mod_dynamo').'
            </button>
            <br>
            <a id="dynamorefresh"
                onclick="location.reload();" title="Retour à la normale"
                style="padding:5px 15px 5px 15px;background:#d3d9df;cursor:pointer;display:none;border-radius:3px;">
                <i class="fas fa-redo-alt"></i>
            </a>
        </div>
      </div>');
echo('<div id="table-overview"><table class="tablelvlx">
        <thead>
          <tr>
            <th style="background-color:'.$faccolor.'">&nbsp;</th>
            <th>'.get_string('dynamoheadparticiaption', 'mod_dynamo').'</th>
            <th>'.get_string('dynamoheadimplication', 'mod_dynamo').'</th>
            <th>'.get_string('dynamoheadconfidence', 'mod_dynamo').'</th>
            <th>'.get_string('dynamoheadconsistency', 'mod_dynamo').'</th>
            <th>'.get_string('dynamoheadcohesion', 'mod_dynamo').'</th>
            <th>'.get_string('dynamoheadconflit', 'mod_dynamo').'</th>
            <th style="border-left:3px solid grey;text-align:center;cursor:pointer;">'
                .get_string('dynamoheadremarque', 'mod_dynamo').' <i class="fas fa-sort"></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
     ');
     $notperfect = 0;
foreach ($groups as $grp) { // Loop to all groups of grouping.
    $grpusrs = dynamo_get_group_users($grp->id);
    $val = [0, 0, 0, 1, 3, 0 , 3];
    $groupstat = dynamo_get_group_stat($dynamo, $grpusrs, $grp->id, $notperfect);
    // Add icon type conflit group.
    $cohesion = dynamo_get_cohesion_group_type($groupstat->type, $grp->id, $groupstat->cohesion);
    $notperfect += ($val[$groupstat->type] * count($grpusrs));

    echo('<tr style="cursor:pointer;" onclick="location.href=\'view.php?id='.$id.'&groupid='.$grp->id.'&tab=2&results=2\'" title="'
        .get_string('dynamoresults2', 'mod_dynamo').'">
              <td class="camera">'.print_group_picture($grp, $course->id, false, true, false)
                .' <a class="groupurl" href=\'view.php?id='.$id.'&groupid='.$grp->id.'&tab=2&results=2\'>'.$grp->name
                .'</a><div class="toolpit">&nbsp;<i class="fas fa-camera"></i><span class="toolpittext toolpit-corr">'
                .$groupstat->tooltips.'</span></div></td>
              <td>'.$groupstat->participation.'</td>
              <td>'.$groupstat->implication.'</td>
              <td>'.$groupstat->confiance.'</td>
              <td>'.$groupstat->consistency.'</td>
              <td>'.$cohesion.'</td>
              <td>'.$groupstat->conflit.'</td>
              <td class="camera-border">'.$groupstat->remark.'</td>
              <td class="td-num">⏲️</td>
         </tr>');
         echo'</div>';
    // Usefull for more than 50 groups or hundreds of students !.
    ob_flush();
    flush();
}

echo('
        </tbody>
    </table>
</div>');

echo('<script src="js/teacher.js"></script>');
