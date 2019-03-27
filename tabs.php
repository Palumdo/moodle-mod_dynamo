<?php
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
if($usrid !=0 && $groupid==0) {
  $groupid = dynamo_get_group_from_user($dynamo->groupementid, $usrid)->id;
}  
echo '  <span class="tablinks'.$active.'" onclick="location.href=\'view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&tab=4\'">'.get_string('dynamotab4', 'mod_dynamo').'</span>';
echo '</div>';
?>