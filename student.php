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
 * This is the survey displayed to student to evaluate other peers and self evaluation
 * they give a note from 1 to 5 for each criteria
 * ***** **** *** ** *
 * they've 5 mandatory criteria (participation, responsability, science
 * expertice, technical expertice and attitude
 * Teacher can add a custom one.
 * The student must also do his autoevaluation
 * and optionaly a general evaluation of the group
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

// Get default tooltips text for helping students and add custom help add by the teacher.
$bubble1 = get_string('dynamocritparticipationdefault', 'mod_dynamo').', '.$dynamo->crit1;
$bubble2 = get_string('dynamocritresponsabilitedefault', 'mod_dynamo').', '.$dynamo->crit2;
$bubble3 = get_string('dynamocritscientifiquedefault', 'mod_dynamo').', '.$dynamo->crit3;
$bubble4 = get_string('dynamocrittechniquedefault', 'mod_dynamo').', '.$dynamo->crit4;
$bubble5 = get_string('dynamocritattitudedefault', 'mod_dynamo').', '.$dynamo->crit5;

$bubblecom1 = $dynamo->comment1;
if ($bubblecom1 == '') {
    $bubblecom1 = get_string('dynamonocomment', 'mod_dynamo');
}
$bubblecom2 = $dynamo->comment2;
if ($bubblecom2 == '') {
    $bubblecom2 = get_string('dynamonocomment', 'mod_dynamo');
}

$bubble1 = rtrim($bubble1, ', ');
$bubble2 = rtrim($bubble2, ', ');
$bubble3 = rtrim($bubble3, ', ');
$bubble4 = rtrim($bubble4, ', ');
$bubble5 = rtrim($bubble5, ', ');

echo '
    <div id="page-content" class="row-fluid">
        <div id="region-main-box" class="span9">
            <div class="row-fluid" style="padding-left:15px;">
                <h3>'.get_string('dynamostudenttitle', 'mod_dynamo').' : '.$cm->name.'</h3>
                <div style="margin-bottom:20px;">'.$dynamo->intro.'</div>
                <div class="row row-student-legend">
                    <div class="col-sm-1"><div class="black-legend">'.get_string('dynamolegend', 'mod_dynamo').': </div></div>
                    <div class="col-sm-2">
                        <span title="'.get_string('dynamoeval1', 'mod_dynamo').'">
                            <i class=" fas fa-circle"></i>
                            <i class="ico-student fas fa-circle"></i>
                            <i class="ico-student fas fa-circle"></i>
                            <i class="ico-student fas fa-circle"></i>
                            <i class="ico-student fas fa-circle"></i>
                        </span><div class="black-legend">'.get_string('dynamoeval1', 'mod_dynamo').'</div></div>
                    <div class="col-sm-2" style="max-width:120px;">
                        <span title="'.get_string('dynamoeval2', 'mod_dynamo').'">
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="ico-student fas fa-circle"></i>
                            <i class="ico-student fas fa-circle"></i>
                            <i class="ico-student fas fa-circle"></i>
                        </span>
                        <div class="black-legend">'.get_string('dynamoeval2', 'mod_dynamo').'</div></div>
                    <div class="col-sm-2" style="min-width:175px;">
                        <span title="'.get_string('dynamoeval3', 'mod_dynamo').'">
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i  class=" fas fa-circle"></i>
                            <i class="ico-student fas fa-circle"></i>
                            <i class="ico-student fas fa-circle"></i>
                        </span><div class="black-legend">'.get_string('dynamoeval3', 'mod_dynamo').'</div></div>
                    <div class="col-sm-2">
                        <span title="'.get_string('dynamoeval4', 'mod_dynamo').'">
                            <i class=" fas fa-circle"></i>
                            <i class=" fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="ico-student fas fa-circle"></i>
                        </span><div class="black-legend">'.get_string('dynamoeval4', 'mod_dynamo').'</div></div>
                    <div class="col-sm-2">
                        <span title="'.get_string('dynamoeval5', 'mod_dynamo').'">
                            <i class=" fas fa-circle"></i>
                            <i class=" fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class=" fas fa-circle"></i>
                        </span><div class="black-legend">'.get_string('dynamoeval5', 'mod_dynamo').'</div></div>
                </div>

                <div id="errormsg" class="errormsg">'.get_string('dynamonotfilled', 'mod_dynamo').'</div>
                <form action="save.php?id='.$cm->id.'"
                    onsubmit="return validation(\''.$display6.'\','.$dynamo->groupeval.');" method="post"
                        enctype="multipart/form-data">
                    <table class = "table table-striped">
                        <caption>'.get_string('dynamogrid', 'mod_dynamo').'</caption>
                        <thead>
                        <tr>
                            <th style="min-width:200px;">'.$group->name.'</th>
                            <th style="min-width:160px;">'.get_string('dynamoparticipation', 'mod_dynamo').' &nbsp;
                                <div class="toolpit"><i class="fas fa-info-circle ico-white"></i>
                                    <span class="toolpittext">'.$bubble1.'</span></div></th>
                            <th style="min-width:160px;">'.get_string('dynamoresponsabilite', 'mod_dynamo').'&nbsp;
                                <div class="toolpit"><i class="fas fa-info-circle ico-white"></i>
                                    <span class="toolpittext">'.$bubble2.'</span></div></th>
                            <th style="min-width:150px;">'.get_string('dynamoscientifique', 'mod_dynamo').'  &nbsp;
                                <div class="toolpit"><i class="fas fa-info-circle ico-white"></i>
                                    <span class="toolpittext">'.$bubble3.'</span></div></th>
                            <th style="min-width:150px;">'.get_string('dynamotechnique', 'mod_dynamo').'     &nbsp;
                                <div class="toolpit"><i class="fas fa-info-circle ico-white"></i>
                                    <span class="toolpittext">'.$bubble4.'</span></div></th>
                            <th style="min-width:130px;">'.get_string('dynamoattitude', 'mod_dynamo').'      &nbsp;
                                <div class="toolpit"><i class="fas fa-info-circle ico-white"></i>
                                    <span class="toolpittext">'.$bubble5.'</span></div></th>
                            <th style="min-width:200px;display:'.$display6.'">'.$dynamo->critoptname.'       &nbsp;
                                <div class="toolpit"><i class="fas fa-info-circle ico-white"></i>
                                    <span class="toolpittext">'.$dynamo->critopt.'</span></th>
                        </tr>
                        </thead>
                        <tbody>
';
if ($mode == 'student') {
    echo dynamo_get_body_table($groupusers, $USER->id, $dynamo, $group->id);
} else {
    echo dynamo_get_body_table_teacher($dynamo);
}
echo '
                        </tbody>
                    </table>
                    <br><br>
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">'.get_string('dynamocommentcontr', 'mod_dynamo').'&nbsp;
                                        <div class="toolpit">
                                        <i class="fas fa-info-circle ico-blue"></i><span class="toolpittext">'.$bubblecom1.'</span>
                                    </div>
                                </div>
                                <div class="panel-body">
                                  <textarea maxlength="1000" id="comment1" name="comment1" class="savemecom form-control" rows="8">'
                                    .$comment->comment1.'</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">'.get_string('dynamocommentfonction', 'mod_dynamo')
                                    .'&nbsp;<div class="toolpit">
                                    <i class="fas fa-info-circle ico-blue"></i><span class="toolpittext">'.$bubblecom2.'</span>
                                </div>
                            </div>
                            <div class="panel-body">
                                <textarea maxlength="1004" id="comment2" name="comment2" class="savemecom form-control" rows="8">'
                                    .$comment->comment2.'</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
if ($mode == 'student') {
    echo '
            <div class="container">
              <div class="row">
                <center><button class="btn btn-primary">'.get_string('save').'</button></center>
              </div>
            </div>
            <script>setTimeout(function(){  $("#block-region-side-post").css("display","none");}, 1000);</script>
    ';
}
echo '
                </form>
            </div>
        </div>
    </div>
';