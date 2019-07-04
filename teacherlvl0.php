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
 * This page is a view for the teacher
 * it display all the information about a group
 * basically table with values (niwf sum) and radar graphics !
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Our involvement ratio has been computed with reference to the following paper that shows
// NIWF to be one of the best factors to measure peer assesments :
// Https://www.tandfonline.com/eprint/ee2eHDqmr2aTEb9t4dB8/full.
defined('MOODLE_INTERNAL') || die();
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);
if (!has_capability('mod/dynamo:create', $modulecontext)) {
    redirect(new moodle_url('/my'));
    die;
}
// Predifine rgb colors for student line in radar charts.
$acolors = [
    '(112,128,144)', '(123,104,238)', '(0,255,0)', '(0,0,255)', '(255, 215, 180)', '(230, 25, 75)', '(0, 130, 200)'
    , '(70, 240, 240)', '(255,20,147)', '(60, 180, 75)', '(72,209,204)', '(255, 225, 25)', '(245, 130, 48)', '(145, 30, 180)'
    , '(240, 50, 230)', '(127,255,212)', '(0,191,255)', '(0,206,209)', '(210, 245, 60)', '(250, 190, 190)', '(0, 128, 128)'
    , '(230, 190, 255)', '(170, 110, 40)', '(255, 250, 200)', '(128, 0, 0)', '(170, 255, 195)', '(128, 128, 0)', '(0, 0, 128)'
    , '(128, 128, 128)', '(0,0,128)', '(0,0,205)', '(0,0,255)', '(0,100,0)', '(0,128,0)', '(0,128,128)', '(0,139,139)'
    , '(0,191,255)', '(0,250,154)', '(0,255,0)', '(0,255,127)', '(100,149,237)', '(102,205,170)', '(105,105,105)', '(106,90,205)'
    , '(127,255,212)', '(128,0,0)', '(128,0,128)', '(128,128,0)', '(128,128,128)', '(119,136,153)', '(127,255,0)'];

// Get the groups of a grouping.
$groups = dynamo_get_groups($dynamo->groupingid);

$jscript = '<script>
  window.onload = function ()
  {';

// Display three tabs for display results.
echo '<ul class="dynnav dynnavtabs" style="margin-top:10px;">
          <li><a href="view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=2&results=1">'
            .get_string('dynamoresults1', 'mod_dynamo').'</a></li>
          <li class="active"><a href="view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=2&results=2">'
            .get_string('dynamoresults2', 'mod_dynamo').'</a></li>
          <li><a href="view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=2&results=3">'
            .get_string('dynamoresults3', 'mod_dynamo').'</a></li>
     </ul>';

echo ('<h3>'.get_string('dynamostudenttitle', 'mod_dynamo').' : '.$cm->name.'</h3>');
    echo('<input id="activityid" type="hidden" value="'.$id.'">');
    echo('<input id="usrid" type="hidden" value="'.$usrid.'">');
// List of groups.
echo('<select onchange="reloadGroupme(this);">');
echo('  <option></option>');

$grp = 0;
foreach ($groups as $sgrp) { // Loop to all groups of grouping.
    $selected = "";
    if ($groupid == $sgrp->id) {
        $grp = $sgrp;
        $selected = ' selected';
    }
    echo('    <option id="'.$sgrp->id.'"'.$selected.'>'.$sgrp->name.'</option>');
}
echo('</select>');

if ($grp != 0) {
    $grpusrs = dynamo_get_group_users($grp->id);
    $oconsistency = dynamo_get_consistency($dynamo, $grpusrs);

    $val = [0, 0, 0, 1, 3, 0, 3];
    $notperfect = ($val[$oconsistency->type] * count($grpusrs));
    $climat = dynamo_get_group_climat($dynamo, $grpusrs, $notperfect)[0];
    echo('<h4 class="dynagroupingtitle">'.$climat.' '.print_group_picture($grp, $course->id, false, true, false)
        .' <span class="ico-white">'.$grp->name.'</span></h4>');
    echo('<div class="" id="'.$grp->id.'" style="display:;">');
    echo('<div style="margin-bottom:5px;">'.$grp->description.'</div>');

    echo ('    <div class="table-container">
                   <table class="tablelvl0">
                       <thead>
                       <tr>
                           <th class="dbackground" style="background-color:'.$faccolor.'">
                               <div>
                                   <span class="dbottom">'.get_string('dynamoevaluator', 'mod_dynamo').'</span>
                                   <span class="dtop">'.get_string('dynamoevaluated', 'mod_dynamo').'</span>
                                   <div class="dline"></div>
                               </div>
                           </th>');
    foreach ($grpusrs as $grpusr) { // Loop to all students of  groups to put their name in title and display the avatar.
        $avatar = new user_picture($grpusr);
        $avatar->courseid = $course->id;
        $avatar->link = true;
        $avatar->size = 50;
        echo('                <th style="text-align:center;">'.$OUTPUT->render($avatar).' <a class="urlanchor" title="'
                .get_string('dynamogotodetail', 'mod_dynamo').'" href="#stud'.$grpusr->id.'">'.$grpusr->firstname.' '
                .$grpusr->lastname.'<i class="far fa-arrow-alt-circle-down"></i></a></th>');
    }
    echo('               <th style="text-align:center;">'.get_string('dynamoier', 'mod_dynamo').'</th>'); // Add the total column.
    echo('            </tr>
               </thead>
           <tbody>');
    $i = 0;
    $nbstudent = 0;
    foreach ($grpusrs as $grpusr) { // Loop to all students of  groups.
        echo('<tr onclick="document.location=\'view.php?id='.$cm->id.'&usrid='.$grpusr->id.'&groupid='.$grp->id
            .'&tab=2&results=3\'" style="cursor:pointer;" title="'.get_string('dynamoresults3', 'mod_dynamo').'">
            <td>'.$grpusr->firstname.' '.$grpusr->lastname.'</td>');
        $agridlib = dynamo_get_matrix($dynamo, $grpusrs); // Get the points matrix include sum and niwf.
        for ($j = 0; $j < count($agridlib[$i]); $j++) {
            if ($i != $j) {
                echo('                <td style="text-align:center;">'.$agridlib[$i][$j].'</td>');
            } else {
                echo('                <td style="text-align:center;color:#666">('.$agridlib[$i][$j].')</td>');
            }
        }
        echo('</tr>');
        if ($agridlib[$i][$j - 1] > 0) {
            $nbstudent++;
        }
        $i++;
    }
    // NIWFS.
    echo('<tr>');
    echo('  <td style="background-color:dimgray;text-align:center;">'.get_string('dynamoniwf', 'mod_dynamo').'
                <a href="#" data-toggle="toolpit" dyna-data-title="'.get_string('dynamohelpniwf', 'mod_dynamo').'">&nbsp;
                <i class="fas fa-info-circle ico-black"></i></a>
            </td>');

    $i = count($agridlib) - 1;
    for ($j = 0; $j < count($agridlib[$i]); $j++) {
        $niwf = $agridlib[$i][$j];
        echo('<td style="background-color:white;text-align:center;color:'.dynamo_get_color_niwf($niwf).'">'
            .number_format($niwf, 2, ',', ' ').'<br>'.(number_format(($niwf / $nbstudent) * 100, 2, ',', ' ')).'&#37;</td>');
    }
    echo('</tr>');
    // Display the niwf formula in HTML.
    echo('        </tbody>
              </table>
              <table id="table-comment">
                  <tbody>');
    // This javascript is used to put in colors keywords in comments.
    $jsadd = "";
    $jsadd .= '$(this).html($(this).html().split(",").join(" ,"));';
    $jsadd .= '$(this).html($(this).html().split(";").join(" ;"));';
    $jsadd .= '$(this).html($(this).html().split(".").join(" ."));';
    $jsadd .= '$(this).html($(this).html().split("!").join(" !"));';
    $jsadd .= '$(this).html($(this).html().split("?").join(" ?"));';

    $akeywords = explode('|', get_string('dynamokeywords', 'mod_dynamo'));
    foreach ($akeywords as $keyword) {
        $jsadd .= 'var keyword = "'.$keyword.'";';
        // Doesn't work when the keyword start with a non ASCII characters.
        $jsadd .= '$(this).html($(this).html().replace(
            new RegExp("\\\b"+keyword+"", "ig"),"<span class=\'incomkey\'>'.$keyword.'"));';
    }
    $jsadd .= 'var atext = $(this).html().split(" ");
               atext.forEach(function (item, index) {
                   if (atext[index].indexOf("incomkey") != -1) {
                       atext[index] = atext[index] + "</span>";
                   }
               });
               $(this).html(atext.join(" "));
             ';
    // Find in comments specific words (define in language file at this value "dynamokeywords" or firstname and lastname of users.
    foreach ($grpusrs as $grpusr) {
        $comment = dynamo_get_comment($grpusr->id, $dynamo);

        echo('<tr><td>'.$grpusr->firstname.' '.$grpusr->lastname.'</td>');
        echo('<td><div class="eval_comments_table">');
        echo('<b>'.get_string('dynamocommentcontr', 'mod_dynamo').'</b><br>');
        echo('<span class="tdcomment">'.$comment->comment1.'</span><br>');
        echo('<b>'.get_string('dynamocommentfonction', 'mod_dynamo').'</b><br>');
        echo('<span class="tdcomment">'.$comment->comment2.'</span><br><br>');
        echo('</div></td></tr>');
        $jsadd .= 'var firstname = "'.$grpusr->firstname.'";';
        $jsadd .= 'var lastname = "'.$grpusr->lastname.'";';
        $jsadd .= '$(this).html($(this).html().replace(
            new RegExp("\\\b"+firstname+"\\\b", "ig"),"<span class=\'incomname\'>'.$grpusr->firstname.'</span>"));';
        $jsadd .= '$(this).html($(this).html().replace(
            new RegExp("\\\b"+lastname+"\\\b", "ig"),"<span class=\'incomname\'>'.$grpusr->lastname.'</span>"));';
    }
    $jsadd .= '$(this).html($(this).html().split(" ,").join(","));';
    $jsadd .= '$(this).html($(this).html().split(" ;").join(";"));';
    $jsadd .= '$(this).html($(this).html().split(" .").join("."));';
    $jsadd .= '$(this).html($(this).html().split(" !").join("!"));';
    $jsadd .= '$(this).html($(this).html().split(" ?").join("?"));';

    echo('        </tbody>
              </table>');

     $jscript .= '$( "#table-comment .tdcomment" ).each(function() {
                 '.$jsadd.'
                 });';

    echo('    </div>'); // Standard deviation = ecart type.
    echo('</div>'); // End grouping.

    // Label of radar chart.
    $labels = '[\''.get_string('dynamoparticipation', 'mod_dynamo').'\',\''
                .get_string('dynamoresponsabilite', 'mod_dynamo').'\',\''
                .get_string('dynamoscientifique', 'mod_dynamo').'\',\''
                .get_string('dynamotechnique', 'mod_dynamo').'\',\''
                .get_string('dynamoattitude', 'mod_dynamo').'\'';
    if ($display6 != 'none') {
        $labels .= ',\''.$dynamo->critoptname.'\'';
    }
    $labels .= ']';

    // Group graph.
    $keys = '[';
    $datagrp = '[';
    $strokestyle = '[';
    $keycolors = '[';
    echo ('<div class="graph-block"><canvas id="cvs_'.$grp->id.'" width="960" height="360">[No canvas support]</canvas></div>');
    // End group chart.
    $stdcnt = 0;

    foreach ($grpusrs as $grpusr) {
        $userid = $grpusr->id;
        $data = dynamo_compute_advanced($userid, $dynamo);
        $niwf = dynamo_get_niwf($dynamo, $grpusrs, $userid);
        $conf = dynamo_get_conf($dynamo, $grpusrs, $userid);
        $avatar = new user_picture($grpusr);
        $avatar->courseid = $course->id;
        $avatar->link = true;
        $avatar->size = 50;

        $canvas = '<div class="graph-block"><canvas id="cvs_'.$userid.
            '" width="720" height="360">[No canvas support]</canvas></div>';
        echo ('<div id="stud'.$grpusr->id.'">&nbsp;</div>');
        echo ('<h4 class="group_detail_title" style="background-color:'.$faccolor.'" title="'
            .get_string('dynamogotoparticipant', 'mod_dynamo').'"
                onclick="document.location=\'view.php?id='.$cm->id.'&usrid='.$grpusr->id.'&groupid='.$groupid.'&tab=2&results=3\'">
                '.$OUTPUT->render($avatar).' '.$grpusr->firstname.' '.$grpusr->lastname.'</h4>');
        $dynamoautoeval = dynamo_get_autoeval($userid, $dynamo);
        // Data for the spider/radar graph.
        $autoevalstr  = '['.$dynamoautoeval->crit1.','.$dynamoautoeval->crit2.','.$dynamoautoeval->crit3.'
            ,'.$dynamoautoeval->crit4.','.$dynamoautoeval->crit5;
        if ($display6 != 'none') {
            $autoevalstr .= ','.$dynamoautoeval->crit6;
        }
        $autoevalstr .= ']';

        if ($data->nbeval != 0) {
            $pairevalstr = '['.round($data->autocritsum->total1 / $data->nbeval, 2).','
                              .round($data->autocritsum->total2 / $data->nbeval, 2).','
                              .round($data->autocritsum->total3 / $data->nbeval, 2).','
                              .round($data->autocritsum->total4 / $data->nbeval, 2).','
                              .round($data->autocritsum->total5 / $data->nbeval, 2);
            if ($display6 != 'none') {
                $pairevalstr .= ','.round($data->autocritsum->total6 / $data->nbeval, 2);
            }
            $pairevalstr .= ']';
        } else {
            $pairevalstr = '[0,0,0,0,0';
            if ($display6 != 'none') {
                $pairevalstr .= ',0';
            }
            $pairevalstr .= ']';
        }
        // End data.
        echo('<table class="table" style="text-align:center;">');
        echo('    <thead>');
        echo('        <tr>');
        echo('            <th></th>');
        echo('            <th>'.get_string('dynamoparticipation', 'mod_dynamo').'</th>');
        echo('            <th>'.get_string('dynamoresponsabilite', 'mod_dynamo').'</th>');
        echo('            <th>'.get_string('dynamoscientifique', 'mod_dynamo').'</th>');
        echo('            <th>'.get_string('dynamotechnique', 'mod_dynamo').'</th>');
        echo('            <th>'.get_string('dynamoattitude', 'mod_dynamo').'</th>');
        if ($display6 != 'none') {
            echo('            <th>'.$dynamo->critoptname.'</th>');
        }
        echo('        </tr>');
        echo('    </thead>');
        echo('    <tbody>');
        echo('        <tr>');
        echo('            <td>'.get_string('dynamoautoeval', 'mod_dynamo').'</td>');
        echo('            <td>'.$dynamoautoeval->crit1.'</td>');
        echo('            <td>'.$dynamoautoeval->crit2.'</td>');
        echo('            <td>'.$dynamoautoeval->crit3.'</td>');
        echo('            <td>'.$dynamoautoeval->crit4.'</td>');
        echo('            <td>'.$dynamoautoeval->crit5.'</td>');
        if ($display6 != 'none') {
            echo('            <td>'.$dynamoautoeval->crit6.'</td>');
        }
        echo('        </tr>');

        echo('        <tr>');
        echo('            <td>'.get_string('dynamoevalgroup', 'mod_dynamo').'</td>');
        if ($data->nbeval != 0) {
            echo('            <td>'.round($data->autocritsum->total1 / $data->nbeval, 2).'</td>');
            echo('            <td>'.round($data->autocritsum->total2 / $data->nbeval, 2).'</td>');
            echo('            <td>'.round($data->autocritsum->total3 / $data->nbeval, 2).'</td>');
            echo('            <td>'.round($data->autocritsum->total4 / $data->nbeval, 2).'</td>');
            echo('            <td>'.round($data->autocritsum->total5 / $data->nbeval, 2).'</td>');
            if ($display6 != 'none') {
                echo('     <td>'.round($data->autocritsum->total6 / $data->nbeval, 2).'</td>');
            }
        } else {
            echo('            <td>0</td>');
            echo('            <td>0</td>');
            echo('            <td>0</td>');
            echo('            <td>0</td>');
            echo('            <td>0</td>');
            if ($display6 != 'none') {
                echo('     <td>0</td>');
            }
        }

        $allgroupeval = "";
        if ($dynamo->groupeval == 1) {
            $allgroupeval = dynamo_get_group_eval_avg($dynamo, $grpusrs, $grp->id);
            echo ('        <tr>');
            echo ('            <td >'.get_string('dynamogroupevalby', 'mod_dynamo').'</td>');
            echo ('            <td >'.$allgroupeval->crit1.'</td>');
            echo ('            <td >'.$allgroupeval->crit2.'</td>');
            echo ('            <td >'.$allgroupeval->crit3.'</td>');
            echo ('            <td >'.$allgroupeval->crit4.'</td>');
            echo ('            <td >'.$allgroupeval->crit5.'</td>');
            echo ('            <td  style="display:'.$display6.'">'.$allgroupeval->crit6.'</td>');
            echo ('        </tr>');
        }
        echo('       </tr>');

        echo('    </tbody>');
        echo('</table>');
        echo('<div style="line-height:2.0em;"><b>'.get_string('dynamoniwf', 'mod_dynamo')
            .'</b> :<span style="padding:3px;border-radius:3px;color:white;background-color:'.dynamo_get_color_niwf($niwf[0]).'">'
            .number_format($niwf[0], 2, ',', ' ').'</span> <a href="#" data-toggle="toolpit" dyna-data-title="'.$niwf[1].'">&nbsp;
                <i class="fas fa-info-circle ico-blue"></i></a></div>');
        echo('<div style="line-height:2.0em;"><b>'.get_string('dynamoconf', 'mod_dynamo')
            .'</b> :<span style="padding:3px;border-radius:3px;color:white;background-color:'.dynamo_get_color_conf($conf).'">'
            .number_format($conf, 2, ',', ' ').'</span></div>');
        echo($canvas);

        $allgroupevalstr = "";
        if ($allgroupeval != "") {
            $allgroupevalstr = '['.$allgroupeval->crit1.','.$allgroupeval->crit2.','.$allgroupeval->crit3.','
                                    .$allgroupeval->crit4.','.$allgroupeval->crit5;
            if ($display6 != 'none')  {
                $allgroupevalstr .= ','.$allgroupeval->crit6;
            }
            $allgroupevalstr .= ']';
        }

        $jscript = dynamo_get_graph_radar($jscript, $userid, $pairevalstr, $autoevalstr, $allgroupevalstr, $labels
                    , $grpusr->firstname, $grpusr->lastname);

        // Radart group data.
        $keys .= '"'.htmlspecialchars($grpusr->firstname, ENT_QUOTES)." ".htmlspecialchars($grpusr->lastname, ENT_QUOTES).'",';
        $datagrp .= str_replace (",,,,","0,0,0,0,0", $autoevalstr).',';

        $color = $acolors[$stdcnt++];
        if ($stdcnt >= count($acolors)) {
            $stdcnt = 0;
        }
        $keycolors .= '\'rgb'.$color.'\',';
        $color = str_replace(')', '', $color);
        $strokestyle .= '\'rgba'.$color.',0.8)\',';
        // End group data.
    }
}
// This radar graphic display all auto evaluation of students.
if ($datagrp != '') {
    // Remove last character (,).
    $datagrp = substr($datagrp, 0, -1);
    $keys = substr($keys, 0, -1);
    $strokestyle = substr($strokestyle, 0, -1);
    $keycolors = substr($keycolors, 0, -1);

    if ($allgroupevalstr != "") {
        $datagrp .= ','.$allgroupevalstr;
        $keys .= ',"'.get_string('dynamogroupevalby', 'mod_dynamo').'"';

        $color = $acolors[$stdcnt++];
        $keycolors .= ','.'\'rgb'.$color.'\',';
        $color = str_replace(')', '', $color);
        $strokestyle .= ','.'\'rgba'.$color.',0.8)\',';
    }

    $datagrp .= ']';
    $keys .= ']';
    $strokestyle .= ']';
    $keycolors .= ']';

    $title = get_string('dynamoradar01title4', 'mod_dynamo');
    $jscript = dynamo_get_graph_radar_all($jscript, $grp->id, $datagrp, $title, $labels, $strokestyle, $keys, $keycolors);
}

$jscript = $jscript.'
      };
    </script>';
echo($jscript);