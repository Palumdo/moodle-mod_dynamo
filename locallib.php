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
 * Internal library of functions for dynamo module.
 *
 * All the dynamo specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_dynamo
 * @copyright 2019 Palumbo Dominique
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This creates new calendar events given close by $dynamo.
 *
 * @param stdClass $dynamo
 * @return void
 */
function dynamo_set_events($dynamo) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/calendar/lib.php');

    // Get CMID if not sent as part of $dynamo.
    if (!isset($dynamo->coursemodule)) {
        $cm = get_coursemodule_from_instance('dynamo', $dynamo->id, $dynamo->course);
        $dynamo->coursemodule = $cm->id;
    }

    // Dynamo start calendar events.
    $event = new stdClass();
    $event->eventtype = DYNAMO_EVENT_TYPE_OPEN;
    // The dynamo_EVENT_TYPE_OPEN event should only be an action event if no close time is specified.
    $event->type = empty($dynamo->close) ? CALENDAR_EVENT_TYPE_ACTION : CALENDAR_EVENT_TYPE_STANDARD;
    if ($event->id = $DB->get_field('event', 'id',
            array('modulename' => 'dynamo', 'instance' => $dynamo->id, 'eventtype' => $event->eventtype))) {
        if ((!empty($dynamo->open)) && ($dynamo->open > 0)) {
            // Calendar event exists so update it.
            $event->name         = get_string('calendarstart', 'dynamo', $dynamo->name);
            $event->description  = format_module_intro('dynamo', $dynamo, $dynamo->coursemodule);
            $event->timestart    = $dynamo->open;
            $event->timesort     = $dynamo->open;
            $event->visible      = instance_is_visible('dynamo', $dynamo);
            $event->timeduration = 0;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event, false);
        } else {
            // Calendar event is on longer needed.
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->delete();
        }
    } else {
        // Event doesn't exist so create one.
        if ((!empty($dynamo->open)) && ($dynamo->open > 0)) {
            $event->name         = get_string('calendarstart', 'dynamo', $dynamo->name);
            $event->description  = format_module_intro('dynamo', $dynamo, $dynamo->coursemodule);
            $event->courseid     = $dynamo->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'dynamo';
            $event->instance     = $dynamo->id;
            $event->timestart    = $dynamo->open;
            $event->timesort     = $dynamo->open;
            $event->visible      = instance_is_visible('dynamo', $dynamo);
            $event->timeduration = 0;
            calendar_event::create($event, false);
        }
    }

    // Dynamo end calendar events.
    $event = new stdClass();
    $event->type = CALENDAR_EVENT_TYPE_ACTION;
    $event->eventtype = DYNAMO_EVENT_TYPE_CLOSE;
    if ($event->id = $DB->get_field('event', 'id',
            array('modulename' => 'dynamo', 'instance' => $dynamo->id, 'eventtype' => $event->eventtype))) {
        if ((!empty($dynamo->close)) && ($dynamo->close > 0)) {
            // Calendar event exists so update it.
            $event->name         = get_string('calendarend', 'dynamo', $dynamo->name);
            $event->description  = format_module_intro('dynamo', $dynamo, $dynamo->coursemodule);
            $event->timestart    = $dynamo->close;
            $event->timesort     = $dynamo->close;
            $event->visible      = instance_is_visible('dynamo', $dynamo);
            $event->timeduration = 0;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event, false);
        } else {
            // Calendar event is on longer needed.
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->delete();
        }
    } else {
        // Event doesn't exist so create one.
        if ((!empty($dynamo->close)) && ($dynamo->close > 0)) {
            $event->name         = get_string('calendarend', 'dynamo', $dynamo->name);
            $event->description  = format_module_intro('dynamo', $dynamo, $dynamo->coursemodule);
            $event->courseid     = $dynamo->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'dynamo';
            $event->instance     = $dynamo->id;
            $event->timestart    = $dynamo->close;
            $event->timesort     = $dynamo->close;
            $event->visible      = instance_is_visible('dynamo', $dynamo);
            $event->timeduration = 0;
            calendar_event::create($event, false);
        }
    }
}
/**
 * Display the table line
 *
 * @param object $result sum and average.
 * @param int $cmid context id.
 * @param object $grpusrsub firstname and lastname.
 * @param string $color color.
 * @param object $dynamoeval criteria values.
 * @param string $display6 6th criteria display or no.
 * @param int $groupid id of the group.
 */
function dynamo_display_table_line($result, $cmid, $grpusrsub, $color, $dynamoeval, $display6, $groupid) {
    echo ('<tr onclick="document.location=\'view.php?id='.$cmid.'&usrid='.$grpusrsub->id.'&groupid='
            .$groupid.'&tab=2&results=3\'" style="cursor:pointer;" title="'.get_string('dynamoresults2', 'mod_dynamo').'">');
    echo (' <td style="color:'.$color.'" class="tdteach">'.fullname($grpusrsub).'</td>');
    echo (' <td class="tdteach">'.$dynamoeval->crit1.'</td>');
    echo (' <td class="tdteach">'.$dynamoeval->crit2.'</td>');
    echo (' <td class="tdteach">'.$dynamoeval->crit3.'</td>');
    echo (' <td class="tdteach">'.$dynamoeval->crit4.'</td>');
    echo (' <td class="tdteach">'.$dynamoeval->crit5.'</td>');
    echo (' <td class="tdteach" style="display:'.$display6.'">'.$dynamoeval->crit6.'</td>');
    echo (' <td class="tdteach">'.$result->sum.'</td>');
    echo (' <td class="tdteach">'.$result->avg.'</td>');
    echo ('</tr>');
}

/**
 * Report 001.
 *
 * @param array $result
 * @param string $name
 */
function dynamo_rep_list_no_participant($result, $name) {
    echo ('<h3 class="report_title">'.get_string('dynamoreport01', 'mod_dynamo').'</h3>');
    echo ('<div class="table-container">');
    echo ('    <table class="table" style="text-align:center;">');
    echo ('        <thead>');
    echo ('            <tr>');
    echo ('                <th>'.get_string('dynamoheadgroup', 'mod_dynamo').'</th>');
    echo ('                <th>'.get_string('dynamoheadfirstname', 'mod_dynamo').'</th>');
    echo ('                <th>'.get_string('dynamoheadlastname', 'mod_dynamo').'</th>');
    echo ('                <th>'.get_string('dynamoheademail', 'mod_dynamo').'</th>');
    echo ('                <th>'.get_string('dynamoheadidnumber', 'mod_dynamo').'</th>');
    echo ('            </tr>');
    echo ('        </thead>');
    echo ('        <tbody>');
    $emails = '';
    foreach ($result as $usr) {
        echo ('        <tr>');
        echo ('            <td>'.$usr->name.'</td>');
        echo ('            <td>'.$usr->firstname.'</td>');
        echo ('            <td>'.$usr->lastname.'</td>');
        echo ('            <td>'.$usr->email.'</td>');
        echo ('            <td>'.$usr->idnumber.'</td>');
        echo ('        </tr>');
        $emails .= $usr->email . ';';
    }
    echo ('        </tbody>');
    echo ('    </table>');
    echo ('<div style="width:100%;word-wrap:break-word;margin-bottom:20px;">'.$emails.'</div>');
    if ($emails == '') {
        echo (get_string('dynamononoparticipant', 'mod_dynamo'));
    }
    echo ('</div>');
}

/**
 * Report 002.
 *
 * @param object $dynamo
 * @param string $jscript
 * @param boolean $display6
 * @param int $courseid
 */
function dynamo_rep_list_all_group($dynamo, $jscript, $display6, $courseid) {
    global $OUTPUT;

    // No goto icon for the first group at top !
    $nojumpclass = "nojump";
    if ($dynamo->groupingid == 0) {
        return;
    }
    echo ('<h3 class="report_title">'.get_string('dynamoreport02', 'mod_dynamo').'</h3>');
    $groups = dynamo_get_groups($dynamo->groupingid);

    echo ('<div class="dontprint">'.get_string('dynamogotogroup', 'mod_dynamo')
        .' : <select name="dropdpown" size="1" id="select-anchor" onchange="gototag(this);">');
    foreach ($groups as $sgrp) {
        echo ('<option id="grp_'.$sgrp->id.'">'.$sgrp->name.'</option>');
    }
    echo ('</select>
            <div style="margin:5px;"><button class="btn btn-default"
              onclick="removeColors();$(this).css(\'display\',\'none\');hideBeforePrint();
                $(\'#dynamorefresh\').css(\'display\',\'\');">'
                .get_string('dynamoremovecolors', 'mod_dynamo')
                .'</button><br><a id="dynamorefresh" onclick="location.reload();" title="'
                .get_string('dynamorefresh', 'mod_dynamo')
                .'" style="padding:5px 15px 5px 15px;background:#d3d9df;cursor:pointer;display:none;border-radius:3px;">
                <i class="fas fa-redo-alt"></i></a></div></div>');

    foreach ($groups as $grp) { // Loop to all groups of grouping.
        $grpusrs = dynamo_get_group_users($grp->id);
        echo ('<h4 class="grp_'.$grp->id.' dynagroupingtitlerep '.$nojumpclass.'" title="'
            .get_string('dynamogotoparticipant', 'mod_dynamo').'"><span class="ico-black"><i class="fas fa-user-cog"></i> '
            .$grp->name.'</span><a style="float:right;color:black;" href="#top"><i class="fas fa-arrow-up"></i></a></h4>');
        $nojumpclass = "";
        echo ('<div class="" id="'.$grp->id.'">');

        echo ('<div class="table-container">
                   <table class="tablelvl0_rep">
                       <thead>
                       <tr>
                           <th class="dbackground">
                               <div>
                                   <span class="dbottom">'.get_string('dynamoevaluator', 'mod_dynamo').'</span>
                                   <span class="dtop">'.get_string('dynamoevaluated', 'mod_dynamo').'</span>
                                   <div class="dline"></div>
                               </div>
                           </th>');
        foreach ($grpusrs as $grpusr) { // Loop to all students of  groups to put their name in title.
            $avatar = new user_picture($grpusr);
            $avatar->courseid = $courseid;
            $avatar->link = true;
            echo ('            <th>'.$OUTPUT->render($avatar).fullname($grpusr).'</th>');
        }
        echo ('            <th>'.get_string('dynamoier', 'mod_dynamo').'</th>'); // Add the total column.

        echo ('        </tr>
                       </thead>
                       <tbody>');
        $i = 0;
        $nbstudent = 0;
        foreach ($grpusrs as $grpusr) { // Loop to all students of  groups.
            echo ('        <tr>
                              <td>'.fullname($grpusr).'</td>');
            $agridlib = dynamo_get_matrix($dynamo, $grpusrs); // Get the points matrix include sum and NIWF.
            for ($j = 0; $j < count($agridlib[$i]); $j++) {
                if ($i != $j) {
                    echo ('            <td>'.$agridlib[$i][$j].'</td>');
                } else {
                    echo ('            <td style="color:#666">('.$agridlib[$i][$j].')</td>');
                }
            }
            echo ('        </tr>');
            if ($agridlib[$i][$j - 1] > 0) {
                $nbstudent++;
            }
            $i++;
        }
        // NIWF.
        echo ('          <tr>');
        echo ('              <td style="background-color:LightGrey;color:black;">'.get_string('dynamoniwf', 'mod_dynamo').'</td>');

        $i = count($agridlib) - 1;
        for ($j = 0; $j < count($agridlib[$i]); $j++) {
            $niwf = $agridlib[$i][$j];
            $color = dynamo_get_color_niwf($niwf);
            if ($nbstudent > 0) {
                echo ('            <td class="change-color" style="color:'.$color.'">'.number_format($niwf, 2, ',', ' ').'<br>'
                    .(number_format(($niwf / $nbstudent) * 100, 2, ',', ' ')).'&#37;</td>');
            }
        }
        echo ('          </tr>');
        echo ('     </tbody>
                    </table>
                </div>'); // Standard deviation = ecart type.
        echo ('</div>'); // End grouping.

        // Label of radar chart.
        $labels = '[\''.get_string('dynamoparticipation', 'mod_dynamo').'\',\''.get_string('dynamoresponsabilite', 'mod_dynamo')
            .'\',\''.get_string('dynamoscientifique', 'mod_dynamo').'\',\''.get_string('dynamotechnique', 'mod_dynamo').'\',\''
            .get_string('dynamoattitude', 'mod_dynamo').'\'';
        if ($display6 != 'none') {
            $labels .= ',\''.$dynamo->critoptname.'\'';
        }
        $labels .= ']';

        foreach ($grpusrs as $grpusr) {
            $usrid = $grpusr->id;
            $data = dynamo_compute_advanced($usrid, $dynamo);
            $niwf = dynamo_get_niwf($dynamo, $grpusrs, $usrid);
            $conf = dynamo_get_conf($dynamo, $grpusrs, $usrid)[0];

            $canvas = '<div class="graph-block"><canvas id="cvs_'.$usrid
                .'" width="720" height="360">[No canvas support]</canvas></div>';
            echo ('<h4 class="group_detail_title_rep">'.fullname($grpusr).'</h4>');
            $dynamoautoeval = dynamo_get_autoeval($usrid, $dynamo);

            // Data for the radar/spider graph.
            $autoevalstr = '['.$dynamoautoeval->crit1.','.$dynamoautoeval->crit2.','.$dynamoautoeval->crit3
                .','.$dynamoautoeval->crit4.','.$dynamoautoeval->crit5;
            if ($display6 != 'none') {
                $autoevalstr .= ','.$dynamoautoeval->crit6;
            }
            $autoevalstr .= ']';

            if ($data->nbeval != 0) {
                $pairevalstr = dynamo_get_peer_eval_str($data);
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

            echo ('<table class="table" style="text-align:center;">');
            echo (' <thead>');
            echo ('   <tr>');
            echo ('     <th style="color:black;background-color:#9cb7d4;"></th>');
            echo ('     <th style="color:black;background-color:#9cb7d4;">'
                .get_string('dynamoparticipation', 'mod_dynamo').'</th>');
            echo ('     <th style="color:black;background-color:#9cb7d4;">'
                .get_string('dynamoresponsabilite', 'mod_dynamo').'</th>');
            echo ('     <th style="color:black;background-color:#9cb7d4;">'
                .get_string('dynamoscientifique', 'mod_dynamo').'</th>');
            echo ('     <th style="color:black;background-color:#9cb7d4;">'
                .get_string('dynamotechnique', 'mod_dynamo').'</th>');
            echo ('     <th style="color:black;background-color:#9cb7d4;">'
                .get_string('dynamoattitude', 'mod_dynamo').'</th>');
            if ($display6 != 'none') {
                echo ('     <th style="color:black;background-color:#9cb7d4;">'.$dynamo->critoptname.'</th>');
            }

            echo ('   </tr>');
            echo (' </thead>');
            echo (' <tbody>');
            echo ('   <tr>');
            echo ('     <td>'.get_string('dynamoautoeval', 'mod_dynamo').'</td>');
            echo ('     <td>'.$dynamoautoeval->crit1.'</td>');
            echo ('     <td>'.$dynamoautoeval->crit2.'</td>');
            echo ('     <td>'.$dynamoautoeval->crit3.'</td>');
            echo ('     <td>'.$dynamoautoeval->crit4.'</td>');
            echo ('     <td>'.$dynamoautoeval->crit5.'</td>');
            if ($display6 != 'none') {
                echo ('     <td>'.$dynamoautoeval->crit6.'</td>');
            }
            echo ('   </tr>');
            echo ('   <tr>');
            echo ('     <td>'.get_string('dynamoevalgroup', 'mod_dynamo').'</td>');
            if ($data->nbeval != 0) {
                echo ('     <td>'.round($data->autocritsum->total1 / $data->nbeval, 2).'</td>');
                echo ('     <td>'.round($data->autocritsum->total2 / $data->nbeval, 2).'</td>');
                echo ('     <td>'.round($data->autocritsum->total3 / $data->nbeval, 2).'</td>');
                echo ('     <td>'.round($data->autocritsum->total4 / $data->nbeval, 2).'</td>');
                echo ('     <td>'.round($data->autocritsum->total5 / $data->nbeval, 2).'</td>');
                if ($display6 != 'none') {
                    echo ('     <td>'.round($data->autocritsum->total6 / $data->nbeval, 2).'</td>');
                }
            } else {
                echo ('     <td>0</td>');
                echo ('     <td>0</td>');
                echo ('     <td>0</td>');
                echo ('     <td>0</td>');
                echo ('     <td>0</td>');
                if ($display6 != 'none') {
                    echo ('     <td>0</td>');
                }
            }

            $allgroupeval = "";
            if ($dynamo->groupeval == 1) {
                $allgroupeval = dynamo_get_group_eval_avg($dynamo, $grpusrs, $grp->id);
                echo ('<tr>');
                echo (' <td >'.get_string('dynamogroupevalby', 'mod_dynamo').'</td>');
                echo (' <td >'.$allgroupeval->crit1.'</td>');
                echo (' <td >'.$allgroupeval->crit2.'</td>');
                echo (' <td >'.$allgroupeval->crit3.'</td>');
                echo (' <td >'.$allgroupeval->crit4.'</td>');
                echo (' <td >'.$allgroupeval->crit5.'</td>');
                echo (' <td  style="display:'.$display6.'">'.$allgroupeval->crit6.'</td>');
                echo ('</tr>');
            }
            echo ('   </tr>');
            echo (' </tbody>');
            echo ('</table>');
            echo ('<b>'.get_string('dynamoniwf', 'mod_dynamo').'</b> :<span class="change-color"
                style="color:white;background-color:'
                .dynamo_get_color_niwf($niwf[0]).'">'.number_format($niwf[0], 2, ',', ' ').'</span><br>');
            echo ('<b>'.get_string('dynamoconf', 'mod_dynamo')
                .'</b> :<span class="change-color" style="color:white;background-color:'
                .dynamo_get_color_conf($conf).'">'.number_format($conf, 2, ',', ' ').'</span><br>');
            echo ($canvas);

            $allgroupevalstr = "";
            if ($allgroupeval != "") {
                $allgroupevalstr = '['.$allgroupeval->crit1.','.$allgroupeval->crit2.','.$allgroupeval->crit3.','
                    .$allgroupeval->crit4.','.$allgroupeval->crit5;
                if ($display6 != 'none') {
                    $allgroupevalstr .= ','.$allgroupeval->crit6;
                }
                $allgroupevalstr .= ']';
            }

            $jscript = dynamo_get_graph_radar_report($jscript, $usrid, $pairevalstr, $autoevalstr, $allgroupevalstr, $labels
                , $grpusr->firstname, $grpusr->lastname);
        }
        ob_flush();
        flush();
    }

    return $jscript;
}

/**
 * Report 003.
 *
 * @param object $dynamo
 * @param string $jscript
 * @param boolean $display6
 * @param int $courseid
 */
function dynamo_rep_list_all_participant($dynamo, $jscript, $display6, $courseid) {
    global $OUTPUT;
    $nojumpclass = "nojump";
    $groups = dynamo_get_groups($dynamo->groupingid);
    if ($groups == null) {
        return;
    }

    echo ('<h3 class="report_title">'.get_string('dynamoreport03', 'mod_dynamo').'</h3>');
    echo ('<div id="pleasewait">'.get_string('dynamopleasewait', 'mod_dynamo').'</div>');
    echo ('<div class="button_list_subreport" style="display:none;">
            <div class="box-switch"><div class="box-switch-label">'.get_string('dynamorepbtsynthesis', 'mod_dynamo').'</div>
            <label class="switch">
                <input type="checkbox" checked onclick="$(\'.group_detail_table\').toggle();">
                <span class="slider"></span>
            </label>
            </div>
            <div class="box-switch"><div class="box-switch-label">'.get_string('dynamorepbtniwf', 'mod_dynamo').'</div>
            <label class="switch">
                <input id="chk_niwf_table" type="checkbox" onclick="$(\'.group_niwf_table\').toggle();">
                <span class="slider"></span>
            </label>
            </div>
            <div class="box-switch"><div class="box-switch-label">'.get_string('dynamorepbtevalothers', 'mod_dynamo').'</div>
            <label class="switch">
                <input type="checkbox" checked onclick="$(\'.eval_others_table\').toggle();">
                <span class="slider"></span>
            </label>
            </div>
            <div class="box-switch"><div class="box-switch-label">'.get_string('dynamorepbtcomment', 'mod_dynamo').'</div>
            <label class="switch">
                <input type="checkbox" checked onclick="$(\'.eval_comments_table\').toggle();">
                <span class="slider"></span>
            </label>
            </div>
            <div class="box-switch"><div class="box-switch-label">'.get_string('dynamorepbtevalbyothers', 'mod_dynamo').'</div>
            <label class="switch">
                <input type="checkbox" checked onclick="$(\'.eval_by_others_table\').toggle();">
                <span class="slider"></span>
            </label>
            </div>
            <div class="box-switch"><div class="box-switch-label">'.get_string('dynamorepbtgraphradar', 'mod_dynamo').'</div>
            <label class="switch">
                <input id="chk_graph_radar_table" type="checkbox" onclick="$(\'.graph_radar_table\').toggle();">
                <span class="slider"></span>
            </label>
            </div>
            <div class="box-switch"><div class="box-switch-label">'.get_string('dynamorepbtrelatifassurance', 'mod_dynamo').'</div>
            <label class="switch">
                <input id="chk_relatif_assurance" type="checkbox" onclick="$(\'.relatif_assurance\').toggle();">
                <span class="slider"></span>
            </label>
            </div>
            <div class="box-switch" style="max-width:350px;text-align:center;">
            <button class="btn btn-default" style="margin:10px;color:white;"
                onclick="removeColors();$(this).css(\'display\',\'none\');hideBeforePrint();
                $(\'#dynamorefresh\').css(\'display\',\'\');">'
                .get_string('dynamoremovecolors', 'mod_dynamo')
                .'</button><br><a id="dynamorefresh" onclick="location.reload();" title="'
                .get_string('dynamorefresh', 'mod_dynamo')
                .'" style="background:transparent;cursor:pointer;display:none;"><i class="fas fa-redo-alt"></i></a>
            </div>
        </div>');
    foreach ($groups as $grp) { // Loop to all groups of grouping.
        $grpusrs = dynamo_get_group_users($grp->id);

        foreach ($grpusrs as $grpusr) {
            $avatar = new user_picture($grpusr);
            $avatar->courseid = $courseid;
            $avatar->link = true;
            $avatar->size = 50;

            echo ('<div class="report-student"><h4 class="group_detail_title_rep '.$nojumpclass.'">'
                .$OUTPUT->render($avatar).$grp->name.' : '.fullname($grpusr).'</h4>');
            $nojumpclass = "";
            dynamo_display_group_detail_table($dynamo, $grp);
            dynamo_display_group_niwf_table($dynamo, $grp);
            dynamo_display_eval_others_table($dynamo, $grpusr->id, $display6);
            dynamo_display_eval_comments_table($dynamo, $grpusr->id);
            dynamo_display_eval_by_others_table($dynamo, $grpusr->id, $display6);
            dynamo_display_relatif_assurance($dynamo, $grpusrs, $grpusr->id);
            $jscript = dynamo_display_graph_radar_table($dynamo, $grpusr->id, $display6, $jscript);
            echo ('</div>');
        }
        ob_flush();
        flush();
    }

    $jscript = $jscript.'var checkboxes = document.getElementsByTagName("input");
                         for (var i=0; i<checkboxes.length; i++) {
                             if (checkboxes[i].type == "checkbox") {
                                 checkboxes[i].checked = true;
                             }
                         }';
    $jscript = $jscript.'$("#chk_graph_radar_table").prop("checked", false);';
    $jscript = $jscript.'$("#chk_niwf_table").prop("checked", false);';
    $jscript = $jscript.'$(".button_list_subreport").css("display","");';
    $jscript = $jscript.'$("#pleasewait").css("display","none");';
    return $jscript;
}
/**
 * Display the group niwf in the table
 *
 * @param stdClass $dynamo dynamo object.
 * @param stdClass $grp group info.
 */
function dynamo_display_group_niwf_table($dynamo, $grp) {
    $grpusrs = dynamo_get_group_users($grp->id);
    echo ('<div class="group_niwf_table" style="display:;">');
    echo ('<h5 class="dynagroupingtitlerep">'.get_string('dynamoniwf', 'mod_dynamo').'</h5>');
    echo (' <div class="table-container">
            <table class="tablelvl0_rep">
                <thead>
                <tr>');
    foreach ($grpusrs as $grpusr) { // Loop to all students of  groups to put their name in title.
        echo ('        <th>'.fullname($grpusr).'</th>');
    }

    echo ('        </tr>
                </thead>
                <tbody>
                <tr>');
    foreach ($grpusrs as $grpusr) { // Loop to all students of  groups to put their name in title.
        $niwf = dynamo_get_niwf($dynamo, $grpusrs, $grpusr->id);
        echo ('        <td style="background-color:white;"><span class="change-color" style="color:white;background-color:'
            .dynamo_get_color_niwf($niwf[0]).'">'.number_format($niwf[0], 2, ',', ' ').'</span></td>');
    }

    echo ('       </tr>
                </tbody>
            </table>
            </div>
        </div>');
}
/**
 * Display the group detail in the table
 *
 * @param stdClass $dynamo dynamo object.
 * @param stdClass $grp group info.
 */
function dynamo_display_group_detail_table($dynamo, $grp) {
    $grpusrs = dynamo_get_group_users($grp->id);
    echo ('<div class="group_detail_table" style="display:none;">');
    echo ('<h5 class="grp_'.$grp->id.' dynagroupingtitlerep" title="'
        .get_string('dynamogotoparticipant', 'mod_dynamo').'"><span class="ico-black"><i class="fas fa-user-cog"></i> '
        .$grp->name.'</span><a style="float:right;color:black;" href="#top"><i class="fas fa-arrow-up"></i></a></h5>');
    echo ('<div id="'.$grp->id.'" >');

    echo (' <div class="table-container">
                <table class="tablelvl0_rep">
                  <thead>
                    <tr>
                    <th class="dbackground">
                      <div>
                        <span class="dbottom">'.get_string('dynamoevaluator', 'mod_dynamo').'</span>
                        <span class="dtop">'.get_string('dynamoevaluated', 'mod_dynamo').'</span>
                        <div class="dline"></div>
                      </div>
                    </th>');
    foreach ($grpusrs as $grpusr) { // Loop to all students of  groups to put their name in title.
        echo ('        <th>'.fullname($grpusr).'</th>');
    }
    echo ('          <th>'.get_string('dynamoier', 'mod_dynamo').'</th>'); // Add the total column.
    echo ('          </tr>
                  </thead>
                  <tbody>');
    $i = 0;
    $nbstudent = 0;
    foreach ($grpusrs as $grpusr) { // Loop to all students of  groups.
        echo ('        <tr>
                        <td>'.fullname($grpusr).'</td>');
        $agridlib = dynamo_get_matrix($dynamo, $grpusrs); // Get the points matrix include sum and niwf.
        for ($j = 0; $j < count($agridlib[$i]); $j++) {
            if ($i != $j) {
                echo ('        <td>'.$agridlib[$i][$j].'</td>');
            } else {
                echo ('        <td style="color:#666">('.$agridlib[$i][$j].')</td>');
            }
        }
        echo ('          </tr>');
        if ($agridlib[$i][$j - 1] > 0) {
            $nbstudent++;
        }
        $i++;
    }
    // NIWFS.
    echo ('          <tr>');
    echo ('            <td style="background-color:#999;color:black;">'.get_string('dynamoniwf', 'mod_dynamo').'</td>');

    $i = count($agridlib) - 1;
    for ($j = 0; $j < count($agridlib[$i]); $j++) {
        $niwf = $agridlib[$i][$j];
        if ($niwf > 0 && $nbstudent > 0) {
            echo ('        <td class="change-color" style="color:'.dynamo_get_color_niwf($niwf).'">'.number_format($niwf, 2, ',', ' ')
                .'<br>'.(number_format(($niwf / $nbstudent) * 100, 2, ',', ' ')).'&#37;</td>');
        }
    }
    echo ('          </tr>');

    echo ('     </tbody>
              </table>

           </div>'); // Standard deviation = ecart type.

    echo ('</div>'); // End grouping.
    echo ('</div>'); // End group_detail_table.
}

/**
 * Display the table with the evaluation of a student
 *
 * @param object $dynamo
 * @param int $usrid
 * @param bool $display6
 */
function dynamo_display_eval_others_table($dynamo, $usrid, $display6) {
    global $DB;

    $usr = $DB->get_record('user', array('id' => $usrid ));

    $grp = dynamo_get_group_from_user($dynamo->groupingid, $usrid);
    echo ('<div class="eval_others_table" id="'.$grp->id.'" style="display:none;">');
    // User eval the others.
    echo (' <div class="table-container">
            <h3>'.fullname($usr).' : '.get_string('dynamoteacherlvl1evalother', 'mod_dynamo').'</h3>
            <table class="tablelvl0_rep">
                <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>'.get_string('dynamoparticipation', 'mod_dynamo').'</th>
                    <th>'.get_string('dynamoresponsabilite', 'mod_dynamo').'</th>
                    <th>'.get_string('dynamoscientifique', 'mod_dynamo').'</th>
                    <th>'.get_string('dynamotechnique', 'mod_dynamo').'</th>
                    <th>'.get_string('dynamoattitude', 'mod_dynamo').'</th>
                    <th style="display:'.$display6.'">'.$dynamo->critoptname.'</th>
                    <th>'.get_string('dynamosum', 'mod_dynamo').'</th>
                    <th>'.get_string('dynamoavg', 'mod_dynamo').'</th>
                </tr>
                </thead>
                <tbody>
    '); // Standard deviation = ecart type.
    $dynamoautoeval = array();
    // Display group evaluation.
    if ($dynamo->groupeval == 1) {
        $dynamoeval = dynamo_get_evaluation($dynamo->id, $usrid, $grp->id);
        $result = dynamo_compute_basis($dynamoeval, $display6);
        echo ('<tr style="border:2px solid black;">');
        echo ('    <td class="tdteach"><b>'.get_string('dynamoevalofgroup', 'mod_dynamo').'</b> : '.$grp->name.'</td>');
        echo ('    <td class="tdteach">'.$dynamoeval->crit1.'</td>');
        echo ('    <td class="tdteach">'.$dynamoeval->crit2.'</td>');
        echo ('    <td class="tdteach">'.$dynamoeval->crit3.'</td>');
        echo ('    <td class="tdteach">'.$dynamoeval->crit4.'</td>');
        echo ('    <td class="tdteach">'.$dynamoeval->crit5.'</td>');
        echo ('    <td class="tdteach" style="display:'.$display6.'">'.$dynamoeval->crit6.'</td>');
        echo ('    <td class="tdteach">'.$result->sum.'</td>');
        echo ('    <td class="tdteach">'.$result->avg.'</td>');
        echo ('</tr>');
        $dynamoeval->sum = $result->sum;
        $dynamoeval->avg = $result->avg;
        $dynamoeval->grp = 1;
    }

    $grpusrs = dynamo_get_group_users($grp->id);
    foreach ($grpusrs as $grpusrsub) { // Loop to all evaluation of  students.
        $color = "";
        if ($usrid == $grpusrsub->id) {
            $color = '#555';
        }

        $dynamoeval = dynamo_get_evaluation($dynamo->id, $usrid, $grpusrsub->id);
        if ($usrid == $grpusrsub->id) {
            $dynamoautoeval[] = $dynamoeval;
        }
        $result = dynamo_print_compute_basis($dynamoeval, $display6, $color, $grpusrsub);
        if (isset($result->sum)) {
            $dynamoeval->sum = $result->sum;
        }
        if (isset($result->avg)) {
            $dynamoeval->avg = $result->avg;
        }
        $dynamoeval->grp = 0;
    }
    echo (' </tbody>
        </table>
        </div>
    </div>');
}

/**
 * Display the two comments of the evaluation of a student
 *
 * @param object $dynamo
 * @param int $usrid
 */
function dynamo_display_eval_comments_table($dynamo, $usrid) {
      $comment = dynamo_get_comment($usrid, $dynamo);
      echo ('<div class="eval_comments_table" style="display:none;">');

      echo ('<b>'.get_string('dynamocommentcontr', 'mod_dynamo').'</b><br>');
      echo ($comment->comment1.'<br>');
      echo ('<b>'.get_string('dynamocommentfonction', 'mod_dynamo').'</b><br>');
      echo ($comment->comment2.'<br><br>');
      echo ('</div>');
}

/**
 * Display the table with the evaluation of a student by the others of the group
 *
 * @param object $dynamo
 * @param int $usrid
 * @param bool $display6
 */
function dynamo_display_eval_by_others_table($dynamo, $usrid, $display6) {
    global $DB;

    $usr = $DB->get_record('user', array('id' => $usrid ));

    $grp = dynamo_get_group_from_user($dynamo->groupingid, $usrid);
    echo ('<div class="eval_by_others_table" id="'.$grp->id.'" style="display:none;">');
    echo (' <div class="table-container">
                <h3>'.fullname($usr).' : '.get_string('dynamoteacherlvl1othereval', 'mod_dynamo').'</h3>
                <table class="tablelvl0_rep">
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>'.get_string('dynamoparticipation', 'mod_dynamo').'</th>
                        <th>'.get_string('dynamoresponsabilite', 'mod_dynamo').'</th>
                        <th>'.get_string('dynamoscientifique', 'mod_dynamo').'</th>
                        <th>'.get_string('dynamotechnique', 'mod_dynamo').'</th>
                        <th>'.get_string('dynamoattitude', 'mod_dynamo').'</th>
                        <th style="display:'.$display6.'">'.$dynamo->critoptname.'</th>
                        <th>'.get_string('dynamosum', 'mod_dynamo').'</th>
                        <th>'.get_string('dynamoavg', 'mod_dynamo').'</th>
                    </tr>
                    </thead>
                    <tbody>
    ');

    $grpusrs = dynamo_get_group_users($grp->id);
    foreach ($grpusrs as $grpusrsub) { // Loop to all evaluation of  students.
        $color = "";
        if ($usrid == $grpusrsub->id) {
            $color = '#555';
        }

        if ($grpusrsub->id == $usrid && $dynamo->autoeval == 0) {
            $color = "";
        } else {
            $dynamoeval = dynamo_get_evaluation($dynamo->id, $grpusrsub->id, $usrid);
            $result = dynamo_print_compute_basis($dynamoeval, $display6, $color, $grpusrsub);
        }
    }
    echo (' </tbody>
        </table>
    </div>
  </div>');
}

/**
 * Display the radar graph of a student
 *
 * @param object $dynamo
 * @param int $usrid
 * @param bool $display6
 * @param string $jscript
 */
function dynamo_display_graph_radar_table($dynamo, $usrid, $display6, $jscript) {
    global $DB;
    $dynamoautoeval = array();
    $usr = $DB->get_record('user', array('id' => $usrid ));
    $grp = dynamo_get_group_from_user($dynamo->groupingid, $usrid);
    $grpusrs = dynamo_get_group_users($grp->id);

    echo ('<div class="graph_radar_table">');

    $dynamoeval = dynamo_get_evaluation($dynamo->id, $usrid, $usrid);
    $dynamoautoeval[] = $dynamoeval;

    $labels = '[\''.get_string('dynamoparticipation', 'mod_dynamo').'\',\''
        .get_string('dynamoresponsabilite', 'mod_dynamo').'\',\''
        .get_string('dynamoscientifique', 'mod_dynamo').'\',\''
        .get_string('dynamotechnique', 'mod_dynamo').'\',\''.get_string('dynamoattitude', 'mod_dynamo').'\'';

    if ($display6 != 'none') {
        $labels .= ',\''.$dynamo->critoptname.'\'';
    }
    $labels .= ']';

    $data = dynamo_compute_advanced($usrid, $dynamo);
    $canvas = '<div class="graph-block"><canvas id="cvs_'.$usrid.'" width="720" height="360">[No canvas support]</canvas></div>';
    echo ($canvas);

    $autoevalstr = '['.$dynamoautoeval[0]->crit1.','.$dynamoautoeval[0]->crit2.','.$dynamoautoeval[0]->crit3.','
        .$dynamoautoeval[0]->crit4.','.$dynamoautoeval[0]->crit5;
    if ($display6 != 'none') {
        $autoevalstr .= ','.$dynamoautoeval[0]->crit6;
    }
    $autoevalstr .= ']';

    $pairevalstr = dynamo_get_peer_eval_str($data);
    if ($display6 != 'none') {
        $pairevalstr .= ','.round($data->autocritsum->total6 / $data->nbeval, 2);
    }
    $pairevalstr .= ']';

    if ($dynamo->groupeval == 1) {
        $allgroupeval = dynamo_get_group_eval_avg($dynamo, $grpusrs, $grp->id);
    } else {
        $allgroupeval = "";
    }
    $allgroupevalstr = "";
    if ($allgroupeval != "") {
        $allgroupevalstr = '['.$allgroupeval->crit1.','.$allgroupeval->crit2.','.$allgroupeval->crit3
            .','.$allgroupeval->crit4.','.$allgroupeval->crit5;
        if ($display6 != 'none') {
            $allgroupevalstr .= ','.$allgroupeval->crit6;
        }
        $allgroupevalstr .= ']';
    }

    $jscript = dynamo_get_graph_radar_report($jscript, $usrid, $pairevalstr, $autoevalstr
        , $allgroupevalstr, $labels, $usr->firstname, $usr->lastname);

    echo ('</div>'); // End grouping.

    return  $jscript;
}
/**
 * Display the relatif insurrance of a user in the group
 *
 * @param object $dynamo
 * @param object $grpusrs
 * @param int $usrid
 */
function dynamo_display_relatif_assurance($dynamo, $grpusrs, $usrid) {
    $conf = dynamo_get_conf($dynamo, $grpusrs, $usrid)[0];
    echo ('<div class="relatif_assurance" style="display:none;">');
    echo ('<b>'.get_string('dynamoconf', 'mod_dynamo')
               .'</b> :<span class="change-color" style="color:white;background-color:'
               .dynamo_get_color_conf($conf).'">'.number_format($conf, 2, ',', ' ').'</span><br>');
    echo ('</div>'); // End relative assurance.
}

/**
 * Report 004.
 * @param object $dynamo
 * @param string $jscript
 * @param boolean $display6
 * @param int $zoom
 */
function dynamo_rep_all_confidence($dynamo, $jscript, $display6, $zoom) {
    // Manage the zoom functionality of the graphic.
    switch($zoom) {
        case -1:
        case 0:
            $zoom = 0;
            $size = 530;
            break;
        case 1:
            $size = 630;
            break;
        case 2:
            $size = 830;
            break;
        case 3:
            $size = 1030;
            break;
        case 4:
            $size = 1230;
            break;
        default :
            $zoom = 4;
            $size = 1230;
            break;
    }

    echo ('<div style="margin-bottom:3px;"><button class="btn btn-default" onclick="reloadZoom('
        .$zoom.'-1);">-</button><button class="btn btn-default" onclick="reloadZoom('.$zoom.'+1);">+</button></div>');
    echo ('<div style="height:'.$size.'px !important;" id="graph-balls">
            <canvas id="layer_gfx"      width="'.$size.'" height="'.$size
            .'" style="position:absolute; top:0; left:0; z-index:0;background-color:transparent;">[No canvas support]</canvas>
            <canvas id="confidence_gfx" width="'.$size.'" height="'
            .$size.'" style="position:absolute; top:0; left:0; z-index:0;background-color:transparent;">[No canvas support]</canvas>
        </div>');

    $ret = dynamo_get_all_eval_by_student($dynamo, $display6);

    $data = $ret->result;
    $tooltips = $ret->tooltips;

    $idx = 0;
    echo ('<div class="table-container" style="position:relative;margin-top:15px;">');
    echo ('  <table class="table" style="text-align:center;">');
    echo ('    <thead>');
    echo ('      <tr style="cursor:pointer;">');
    echo ('        <th>'.get_string('dynamogroup', 'mod_dynamo').' <i class="fas fa-sort"></i></th>');
    echo ('        <th>'.get_string('dynamoheadfirstname', 'mod_dynamo').' <i class="fas fa-sort"></i></th>');
    echo ('        <th>'.get_string('dynamoheadlastname', 'mod_dynamo').' <i class="fas fa-sort"></i></th>');
    echo ('        <th>'.get_string('dynamoautoeval', 'mod_dynamo').' <i class="fas fa-sort"></i></th>');
    echo ('        <th>'.get_string('dynamoavgeval', 'mod_dynamo').' <i class="fas fa-sort"></i></th>');
    echo ('        <th><span style="font-size:2.0em;">&#8783;</span> <i class="fas fa-sort"></i></th>');
    echo ('      </tr>');
    echo ('    </thead>');
    echo ('    <tbody>');

    // Generate the data for the javascript function.
    $jscript = $jscript.'var data = [];';
    foreach ($data as $i => $value) {
        if (array_key_exists($i, $data)) { // Add to solve the case when student doesnt auto-evaluate.
            if (property_exists($data[$i], 'autoeval') ) { // Add to solve the case when student doesnt auto-evaluate.
                $idt = round($data[$i]->eval, 2).'_'.round($data[$i]->autoeval, 2);
                $jscript = $jscript.'data['.$idx.'] = {"id":"'.$data[$i]->userid.'","name":"'
                    .substr_replace($tooltips[$idt], "", -1).'", "evals":"'
                    .round($data[$i]->eval, 2).'", "autoeval":"'.round($data[$i]->autoeval, 2).'"};';
                echo ('     <tr><td>'.$data[$i]->name.'</td><td>'.$data[$i]->firstname.'</td><td>'.$data[$i]->lastname.'</td><td>'
                .round($data[$i]->autoeval, 2).'</td><td>'. round($data[$i]->eval, 2).'</td><td>'
                .round($data[$i]->autoeval - $data[$i]->eval, 2).'</td></tr>');
                $idx++;
            }
        }
    }
    echo ('    </tbody>');
    echo ('  </table>');
    echo ('</div>');

    $jscript = $jscript.'drawGraphSelfConfidence(data,"'.get_string('dynamographauto', 'mod_dynamo').'","'
        .get_string('dynamographpeers', 'mod_dynamo').'");';
    return  $jscript;
}

/**
 * Report 005.
 *
 * @param object $dynamo all info
 * @param int $id
 */
function dynamo_rep_yearbook($dynamo, $id) {
    global $OUTPUT;
    $groups = dynamo_get_groups($dynamo->groupingid);
    if ($groups == null) {
        return;
    }
    echo ('<div id="main-yearbook" style="display:table;width:100%;">');
    foreach ($groups as $grp) { // Loop to all groups of grouping.
        $grpusrs = dynamo_get_group_users($grp->id);
        foreach ($grpusrs as $grpusr) {
            echo ('<div class="report-yearbook" title="'.$grp->name.'">'
                .$OUTPUT->user_picture($grpusr, array('size' => 120))
                .'<div class="report-yearbook-title">'.$grp->name
                .'</div><div class="report-yearbook-descr"><a title="'
                .get_string('dynamogotoparticipant', 'mod_dynamo').'" href="view.php?id='.$id.'&groupid='
                .$grp->id.'&usrid='.$grpusr->id.'&tab=2&results=3">'.$grpusr->lastname.'<br>'.$grpusr->firstname
                .'</a><div>'.round(dynamo_get_niwf($dynamo, $grpusrs, $grpusr->id)[0], 2).'</div></div></div>');
        }
        ob_flush();
        flush();
    }
    echo ('</div>');
}

/* Report 006.
 *
 * @param object $cm
 */
function dynamo_rep_excel($cm) {
    $url = new moodle_url('/mod/dynamo/export/xls/export.php?id='.$cm->id.'&instance='.$cm->instance.'&course='.$cm->course);
    echo ('<div style="text-align:center;">'.get_string('dynamoexcelready', 'mod_dynamo'));
    echo ('<br><a style="font-size:24px;color:green;" alt="Export Excel" title="Export Excel" href ="'
        .$url.'" class="fas fa-file-excel" target="_outside"></a></div>');
}

/* Function dynamo_print_compute_basis.
*
* @param object $dynamoeval
* @param boolean $display6
* @param string $color
* @param object $grpusrsub
*/
function dynamo_print_compute_basis($dynamoeval, $display6, $color, $grpusrsub) {
    $result = dynamo_compute_basis($dynamoeval, $display6);
    echo ('<tr>');
    echo ('    <td style="color:'.$color.'" class="tdteach">'.fullname($grpusrsub).'</td>');
    echo ('    <td class="tdteach">'.$dynamoeval->crit1.'</td>');
    echo ('    <td class="tdteach">'.$dynamoeval->crit2.'</td>');
    echo ('    <td class="tdteach">'.$dynamoeval->crit3.'</td>');
    echo ('    <td class="tdteach">'.$dynamoeval->crit4.'</td>');
    echo ('    <td class="tdteach">'.$dynamoeval->crit5.'</td>');
    echo ('    <td class="tdteach" style="display:'.$display6.'">'.$dynamoeval->crit6.'</td>');
    echo ('    <td class="tdteach">'.$result->sum.'</td>');
    echo ('    <td class="tdteach">'.$result->avg.'</td>');
    echo ('</tr>');
}
/* Function return a string that contain a javascript array with peer eval.
*
* @param object $data
*/
function dynamo_get_peer_eval_str($data) {
    if ($data->nbeval > 0 ) {
        return '['.round($data->autocritsum->total1 / $data->nbeval, 2).','
            .round($data->autocritsum->total2 / $data->nbeval, 2).','
            .round($data->autocritsum->total3 / $data->nbeval, 2).','
            .round($data->autocritsum->total4 / $data->nbeval, 2).','
            .round($data->autocritsum->total5 / $data->nbeval, 2);
    } else {
        return '[0.0,0.0,0.0,0.0,0.0';
    }
}
