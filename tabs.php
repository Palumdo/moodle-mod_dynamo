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
 * this file manage the tabulation of the teacher UI
 * it pass all the same parameter to all tabs to make navigation more 
 * confortable
 * url param are :
 * CM id, group id, user id (student) and tab number 
 *
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
echo '<div class="tab">';
if($tab==1) $active = ' active'; else $active = '';
echo '  <span class="tablinks'.$active.'" onclick="location.href=\'view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=1\'">'.get_string('dynamotab1', 'mod_dynamo').'</span>';
if($tab==2) $active = ' active'; else $active = '';
echo '  <span class="tablinks'.$active.'" onclick="location.href=\'view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=2\'">'.get_string('dynamotab2', 'mod_dynamo').'</span>';
if($tab==3) $active = ' active'; else $active = '';
echo '  <span class="tablinks'.$active.'" onclick="location.href=\'view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=3\'">'.get_string('dynamotab3', 'mod_dynamo').'</span>';
if($tab==5) $active = ' active'; else $active = '';
echo '  <span class="tablinks'.$active.'" onclick="location.href=\'view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=5\'">'.get_string('dynamotab5', 'mod_dynamo').'</span>';
if($tab==4) $active = ' active'; else $active = '';
// if the group is not know get from the user id
if($usrid !=0 && $groupid==0) {
  $groupid = dynamo_get_group_from_user($dynamo->groupementid, $usrid)->id;
}  
echo '  <span class="tablinks'.$active.'" onclick="location.href=\'view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=4\'">'.get_string('dynamotab4', 'mod_dynamo').'</span>';
echo '</div>';
?>