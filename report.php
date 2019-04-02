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
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
  require_login($course, true, $cm);
  
  $groups = dynamo_get_groups($dynamo->groupementid);
  $canvas = '';
  $jscript = '<script>
    window.onload = function ()
    {';

  echo ('<h3 id="top">'.get_string('dynamoreports', 'mod_dynamo').' : ('.$cm->name.')</h3>');
  echo('<select onchange="reloadme(this);">');
  echo('  <option id="0">'.get_string('dynamoreportselect', 'mod_dynamo').'</option>');
  echo('  <option id="1">'.get_string('dynamoreport01', 'mod_dynamo').'</option>');
  echo('  <option id="2">'.get_string('dynamoreport02', 'mod_dynamo').'</option>');
  echo('  <option id="3">'.get_string('dynamoreport03', 'mod_dynamo').'</option>');
  echo('  <option id="4">'.get_string('dynamoreport04', 'mod_dynamo').'</option>');
  echo('</select>');

  switch($report) {
    case 1:
      $result = dynamo_get_report_001($dynamo);
      rep_list_no_participant($result, $cm->name);
      break;

    case 2:
      $jscript = rep_list_all_group($dynamo,$jscript, $display6);
      break;

    case 3:
      $jscript = rep_list_all_participant($dynamo,$jscript, $display6);
      break;

    case 4:
      $jscript = rep_all_confidence($dynamo,$jscript,$display6,$id);
      break;
      
  }
  
      $jscript = $jscript.'
        };
         function reloadme(obj) {
          val = $(obj).children(":selected").attr("id");
          location.href=\'view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&report=\'+val+\'&tab=4\';
         }
         
         function gototag(obj) {
          val = $(obj).children(":selected").attr("id");
          var verticalPositionOfElement = $("."+val).offset().top;
          $(window).scrollTop(verticalPositionOfElement - 50);
         }
         
         function removeColors() {
          $(".change-color").css("color","black");
          $(".change-color").css("background-color","white");
         }
    </script>';
    
    

    echo($jscript);
// Report 001
function rep_list_no_participant($result, $name) {
  echo ('<h3 class="report_title">'.get_string('dynamoreport01', 'mod_dynamo').'</h3>');
  echo ('<div class="table-container">');
  echo('  <table class="table" style="text-align:center;">');
  echo('    <thead>');
  echo('      <tr>');
  echo('        <th>'.get_string('dynamoheadgroup', 'mod_dynamo').'</th>');
  echo('        <th>'.get_string('dynamoheadfirstname', 'mod_dynamo').'</th>');
  echo('        <th>'.get_string('dynamoheadlastname', 'mod_dynamo').'</th>');
  echo('        <th>'.get_string('dynamoheademail', 'mod_dynamo').'</th>');
  echo('        <th>'.get_string('dynamoheadidnumber', 'mod_dynamo').'</th>');
  echo('      </tr>');
  echo('    </thead>');
  echo('    <tbody>');
  $emails = '';
  foreach ($result as $usr) {
    echo('      <tr>');
    echo('        <td>'.$usr->name.'</td>');
    echo('        <td>'.$usr->firstname.'</td>');
    echo('        <td>'.$usr->lastname.'</td>');
    echo('        <td>'.$usr->email.'</td>');
    echo('        <td>'.$usr->idnumber.'</td>');
    echo('      </tr>');
    $emails .= $usr->email . ';';
  }
  echo('    </tbody>');
  echo('  </table>');
  echo('<div style="width:100%;word-wrap:break-word;margin-bottom:20px;">'.$emails.'</div>');
  if( $emails == '') {
    echo(get_string('dynamononoparticipant', 'mod_dynamo'));
  } else {
    $subject  = get_string('dynamoreport01mailsubject', 'mod_dynamo').$name; 
    $body     = get_string('dynamoreport01mailbody', 'mod_dynamo'); 
    echo('<a style="border:2px solid #ccc;padding:1em 1.5em;background-color:lightgrey;color:black;text-decoration:none;border-radius:5px;" href="mailto:'.$emails.'?subject='.$subject.'&body='.$body.'">'.get_string('dynamosendmail', 'mod_dynamo').'</a>');
  }
  echo('</div>'); 
  
  
}

// Report 002
function rep_list_all_group($dynamo, $jscript, $display6) {
  echo ('<h3 class="report_title">'.get_string('dynamoreport02', 'mod_dynamo').'</h3>');
  $groups = dynamo_get_groups($dynamo->groupementid);

  echo('<br>'.get_string('dynamogotogroup', 'mod_dynamo').' : <select name="dropdpown" size="1" id="select-anchor" onchange="gototag(this);">');
  foreach ($groups as $sgrp) {
    echo('<option id="grp_'.$sgrp->id.'"'.$selected.'>'.$sgrp->name.'</option>');
  }
  echo('</select><br>
        <button class="btn" onclick="removeColors();">'.get_string('dynamoremovecolors', 'mod_dynamo').'</button>');
  
    foreach ($groups as $grp) { // loop to all groups of grouping
      $grpusrs = dynamo_get_group_users($grp->id);
      echo('<h4 class="grp_'.$grp->id.' dynagroupingtitle" title="'.get_string('dynamogotoparticipant', 'mod_dynamo').'"><span class="ico-white"><i class="fas fa-user-cog"></i> '.$grp->name.'</span><a style="float:right;color:white;" href="#top"><i class="fas fa-arrow-up"></i></a></h4>');
      echo('<div class="" id="'.$grp->id.'">');

      echo (' <div class="table-container">
                <table class="tablelvl0_rep">
                  <thead>
                    <tr><th class="dbackground">
                      <div>
                        <span class="dbottom">'.get_string('dynamoevaluator', 'mod_dynamo').'</span>
                        <span class="dtop">'.get_string('dynamoevaluated', 'mod_dynamo').'</span>
                        <div class="dline"></div>
                      </div>                  
                    </th>');
      foreach ($grpusrs as $grpusr) { // loop to all students of  groups to put their name in title
        echo('        <th>'.$grpusr->firstname.' '.$grpusr->lastname.'</th>');
      }
      echo('          <th>'.get_string('dynamoier', 'mod_dynamo').'</th>'); // add the total column
     
      echo ('       </tr>
                  </thead>
                  <tbody>');
      $i = 0;
      $nbstudent = 0;
      foreach ($grpusrs as $grpusr) { // loop to all students of  groups
        echo('        <tr>
                        <td>'.$grpusr->firstname.' '.$grpusr->lastname.'</td>');
        $aGridlib = dynamo_get_matrix($dynamo, $grpusrs); // get the points matrix include sum and nifs
        for ($j=0;$j<count($aGridlib[$i]);$j++) {
          if($i != $j) {
            echo('        <td>'.$aGridlib[$i][$j].'</td>');
          } else {
            echo('        <td style="color:#666">('.$aGridlib[$i][$j].')</td>');
          }
        }
        echo('          </tr>');
        if($aGridlib[$i][$j-1] > 0) $nbstudent++;
        $i++;
      }
      // NIFS
      echo('          <tr>');
      echo('            <td style="background-color:LightGrey;color:black;">'.get_string('dynamosnif', 'mod_dynamo').'</td>');
      
      $red    = 1 / ((count($aGridlib)-1)*2);
      $orange = 1 / ((count($aGridlib)-1)*1.5);

      $i = count($aGridlib)-1;
      for($j=0;$j<count($aGridlib[$i]);$j++) {
        $snif = $aGridlib[$i][$j];
        $color  = 'green';
        if($snif/$i < $orange) $color  = 'orange';
        if($snif/$i < $red)    $color  = 'red';
          
        echo('        <td class="change-color" style="color:'.$color.'">'.number_format($snif,2,',', ' ').'<br>'.(number_format(($snif/$nbstudent)*100,2,',', ' ')).'&#37;</td>');
      }  
      echo('          </tr>');
      
      echo ('     </tbody>
                </table>

             </div>'); // Standard deviation = ecart type 

      echo('</div>'); // End grouping
      
     // Label of radar chart
      $labels = '[\''.get_string('dynamoparticipation', 'mod_dynamo').'\',\''.get_string('dynamoresponsabilite', 'mod_dynamo').'\',\''.get_string('dynamoscientifique', 'mod_dynamo').'\',\''.get_string('dynamotechnique', 'mod_dynamo').'\',\''.get_string('dynamoattitude', 'mod_dynamo').'\'';
      if($display6 != 'none') {
        $labels .= ',\''.$dynamo->critoptname.'\'';
      } 
      $labels .= ']';

      foreach ($grpusrs as $grpusr) {
        $usrid  = $grpusr->id;
        $data   = dynamo_compute_advanced($usrid, $dynamo);
        $snif   = dynamo_get_snif($dynamo, $grpusrs, $usrid);
        $conf   = dynamo_get_conf($dynamo, $grpusrs, $usrid);
        
        $canvas = '<div class="graph-block"><canvas id="cvs_'.$usrid.'" width="720" height="360">[No canvas support]</canvas></div>';
        echo('<h4 class="group_detail_title_rep">'.$grpusr->firstname.' '.$grpusr->lastname.'</h4>');
        $dynamoautoeval = dynamo_get_autoeval($usrid, $dynamo);

        // data for the spider graph 
        $autoevalstr = '['.$dynamoautoeval->crit1.','.$dynamoautoeval->crit2.','.$dynamoautoeval->crit3.','.$dynamoautoeval->crit4.','.$dynamoautoeval->crit5;
        if($display6 != 'none')  $autoevalstr .= ','.$dynamoautoeval->crit6;
        $autoevalstr .= ']';

        if($data->nbeval != 0) { 
          $pairevalstr = '['.round($data->autocritsum->total1/$data->nbeval,2).','.round($data->autocritsum->total2/$data->nbeval,2).','.round($data->autocritsum->total3/$data->nbeval,2).','.round($data->autocritsum->total4/$data->nbeval,2).','.round($data->autocritsum->total5/$data->nbeval,2);
          if($display6 != 'none')  $pairevalstr .= ','.round($data->autocritsum->total6/$data->nbeval,2);
          $pairevalstr .= ']';
        } else {
          $pairevalstr = '[0,0,0,0,0';
          if($display6 != 'none')  $pairevalstr .= ',0';
          $pairevalstr .= ']';
        }
        // end data

        echo('<table class="table" style="text-align:center;">');
        echo(' <thead>');
        echo('   <tr>');
        echo('     <th></th>');

        echo('     <th>'.get_string('dynamoparticipation', 'mod_dynamo').'</th>');
        echo('     <th>'.get_string('dynamoresponsabilite', 'mod_dynamo').'</th>');
        echo('     <th>'.get_string('dynamoscientifique', 'mod_dynamo').'</th>');
        echo('     <th>'.get_string('dynamotechnique', 'mod_dynamo').'</th>');
        echo('     <th>'.get_string('dynamotechnique', 'mod_dynamo').'</th>');
        if($display6 != 'none') echo('     <th>'.$dynamo->critoptname.'</th>');

        echo('   </tr>');
        echo(' </thead>');
        echo(' <tbody>');

        echo('   <tr>');
        echo('     <td>'.get_string('dynamoautoeval', 'mod_dynamo').'</td>');
        echo('     <td>'.$dynamoautoeval->crit1.'</td>');
        echo('     <td>'.$dynamoautoeval->crit2.'</td>');
        echo('     <td>'.$dynamoautoeval->crit3.'</td>');
        echo('     <td>'.$dynamoautoeval->crit4.'</td>');
        echo('     <td>'.$dynamoautoeval->crit5.'</td>');
        if($display6 != 'none') echo('     <td>'.$dynamoautoeval->crit6.'</td>');
        echo('   </tr>');

        echo('   <tr>');
        echo('     <td>'.get_string('dynamoevalgroup', 'mod_dynamo').'</td>');
        if($data->nbeval != 0) {
          echo('     <td>'.round($data->autocritsum->total1/$data->nbeval,2).'</td>');
          echo('     <td>'.round($data->autocritsum->total2/$data->nbeval,2).'</td>');
          echo('     <td>'.round($data->autocritsum->total3/$data->nbeval,2).'</td>');
          echo('     <td>'.round($data->autocritsum->total4/$data->nbeval,2).'</td>');
          echo('     <td>'.round($data->autocritsum->total5/$data->nbeval,2).'</td>');
          if($display6 != 'none') echo('     <td>'.round($data->autocritsum->total6/$data->nbeval,2).'</td>');
        }  else {
          echo('     <td>0</td>');
          echo('     <td>0</td>');
          echo('     <td>0</td>');
          echo('     <td>0</td>');
          echo('     <td>0</td>');
          if($display6 != 'none') echo('     <td>0</td>');
        }

        $allgroupeval = "";
        if($dynamo->groupeval == 1) {
          $allgroupeval = dynamo_get_group_eval_avg($dynamo, $usrid, $grpusrs, $grp->id);
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
        echo('   </tr>');

        echo(' </tbody>');
        echo('</table>');
        echo('<b>'.get_string('dynamosnif', 'mod_dynamo').'</b> :<span class="change-color" style="color:white;background-color:'.dynamo_get_color_snif($snif[0]).'">'.number_format($snif[0],2,',', ' ').'</span><br>');
        echo('<b>'.get_string('dynamoconf', 'mod_dynamo').'</b> :<span class="change-color" style="color:white;background-color:'.dynamo_get_color_conf($conf).'">'.number_format($conf,2,',', ' ').'</span><br>');
        echo($canvas);
        
        $allgroupevalstr = "";
        if($allgroupeval != "") {
          $allgroupevalstr = '['.$allgroupeval->crit1.','.$allgroupeval->crit2.','.$allgroupeval->crit3.','.$allgroupeval->crit4.','.$allgroupeval->crit5;
          if($display6 != 'none')  $allgroupevalstr .= ','.$allgroupeval->crit6;
          $allgroupevalstr .= ']';
        }      
        
        $jscript = dynamo_get_graph_radar_report($jscript, $usrid, $pairevalstr, $autoevalstr, $allgroupevalstr, $labels, $grpusr->firstname, $grpusr->lastname);

        echo('<div class="break-before"></div>');
        echo('<div class="break-after"></div>');
      }  
    } 
    
    
  return $jscript;    
}    

// Report 003
function rep_list_all_participant($dynamo,$jscript, $display6) {
  global $OUTPUT;
  
  echo ('<h3 class="report_title">'.get_string('dynamoreport03', 'mod_dynamo').'</h3>');
  echo ('<div class="button_list_subreport" style="width:100%;min-height:120px;">
          <div class="box-switch">'.get_string('dynamorepbtsynthesis',  'mod_dynamo').'<br>
            <label class="switch">
              <input type="checkbox" checked onclick="$(\'.group_detail_table\').toggle();">
              <span class="slider round"></span>
            </label>
          </div>
          <div class="box-switch">'.get_string('dynamorepbtsnif',  'mod_dynamo').'<br>
            <label class="switch">
              <input type="checkbox" checked onclick="$(\'.group_snif_table\').toggle();">
              <span class="slider round"></span>
            </label>
          </div>
          <div class="box-switch">'.get_string('dynamorepbtevalothers',  'mod_dynamo').'<br>
            <label class="switch">
              <input type="checkbox" checked onclick="$(\'.eval_others_table\').toggle();">
              <span class="slider round"></span>
            </label>
          </div>
          <div class="box-switch">'.get_string('dynamorepbtcomment',  'mod_dynamo').'<br>
            <label class="switch">
              <input type="checkbox" checked onclick="$(\'.eval_comments_table\').toggle();">
              <span class="slider round"></span>
            </label>
          </div>
          <div class="box-switch">'.get_string('dynamorepbtevalbyothers',  'mod_dynamo').'<br>
            <label class="switch">
              <input type="checkbox" checked onclick="$(\'.eval_by_others_table\').toggle();">
              <span class="slider round"></span>
            </label>
          </div>
          <div class="box-switch">'.get_string('dynamorepbtgraphradar',  'mod_dynamo').'<br>
            <label class="switch">
              <input type="checkbox" onclick="$(\'.graph_radar_table\').toggle();">
              <span class="slider round"></span>
            </label>
          </div>
          <!--<div class="box-switch">'.get_string('dynamorepbtgraphhisto',  'mod_dynamo').'<br>
            <label class="switch">
              <input type="checkbox" onclick="$(\'.graph_histo_table\').toggle();">
              <span class="slider round"></span>
            </label>
          </div>-->
          <div class="box-switch">
            <button class="btn" onclick="removeColors();">'.get_string('dynamoremovecolors', 'mod_dynamo').'</button>
          </div>
        </div>');
  $groups = dynamo_get_groups($dynamo->groupementid);
  foreach ($groups as $grp) { // loop to all groups of grouping  
    $grpusrs = dynamo_get_group_users($grp->id);  
    
    foreach ($grpusrs as $grpusr) {
      $avatar = new user_picture($grpusr);
      $avatar->courseid = $course->id;
      $avatar->link = true;
      echo('<h4 class="group_detail_title_rep">'.$OUTPUT->render($avatar).$grp->name.' : '.$grpusr->firstname.' '.$grpusr->lastname.'</h4>');

      display_group_detail_table($dynamo, $grp);
      display_group_snif_table($dynamo, $grp);
      display_eval_others_table($dynamo, $grpusr->id, $display6);
      display_eval_comments_table($dynamo, $grpusr->id);
      display_eval_by_others_table($dynamo, $grpusr->id, $display6);
      $jscript = display_graph_radar_table($dynamo, $grpusr->id, $display6,$jscript);
//      $jscript = display_graph_histo_table($dynamo, $grpusr->id, $display6,$jscript);
    }  
  }
 return $jscript;      
}
//***************************************************************
function display_group_snif_table($dynamo, $grp) {
  $grpusrs = dynamo_get_group_users($grp->id);
  echo ('<div class="group_snif_table" style="display:none;">');
  echo ('<h5 class="dynagroupingtitle">'.get_string('dynamosnif', 'mod_dynamo').'</h5>'); 
  echo (' <div class="table-container">
            <table class="tablelvl0_rep">
              <thead>
                <tr>');
  foreach ($grpusrs as $grpusr) { // loop to all students of  groups to put their name in title
    echo('        <th>'.$grpusr->firstname.' '.$grpusr->lastname.'</th>');
  }
  
  echo('        </tr>
              </thead>
              <tbody>
                <tr>');
  foreach ($grpusrs as $grpusr) { // loop to all students of  groups to put their name in title
    $snif = dynamo_get_snif($dynamo, $grpusrs, $grpusr->id);
    echo('        <td style="background-color:white;"><span class="change-color" style="color:white;background-color:'.dynamo_get_color_snif($snif[0]).'">'.number_format($snif[0],2,',', ' ').'</span></td>');
  }

  echo ('       </tr>
              </tbody>
            </table>
          </div> 
        </div>');   
  
}  
//***************************************************************
function display_group_detail_table($dynamo, $grp) {
    $grpusrs = dynamo_get_group_users($grp->id);
    echo ('<div class="group_detail_table" style="display:none;">');
    echo('<h5 class="grp_'.$grp->id.' dynagroupingtitle" title="'.get_string('dynamogotoparticipant', 'mod_dynamo').'"><span class="ico-white"><i class="fas fa-user-cog"></i> '.$grp->name.'</span><a style="float:right;color:white;" href="#top"><i class="fas fa-arrow-up"></i></a></h5>');
    echo('<div id="'.$grp->id.'" >');

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
    foreach ($grpusrs as $grpusr) { // loop to all students of  groups to put their name in title
      echo('        <th>'.$grpusr->firstname.' '.$grpusr->lastname.'</th>');
    }
    echo('          <th>'.get_string('dynamoier', 'mod_dynamo').'</th>'); // add the total column
    echo('          </tr>
                  </thead>
                  <tbody>');
    $i = 0;
    $nbstudent = 0;
    foreach ($grpusrs as $grpusr) { // loop to all students of  groups
      echo('        <tr>
                      <td>'.$grpusr->firstname.' '.$grpusr->lastname.'</td>');
      $aGridlib = dynamo_get_matrix($dynamo, $grpusrs); // get the points matrix include sum and nifs
      for ($j=0;$j<count($aGridlib[$i]);$j++) {
        if($i != $j) {
          echo('        <td>'.$aGridlib[$i][$j].'</td>');
        } else {
          echo('        <td style="color:#666">('.$aGridlib[$i][$j].')</td>');
        }
      }
      echo('          </tr>');
      if($aGridlib[$i][$j-1] > 0) $nbstudent++;
      $i++;
    }
    // NIFS
    echo('          <tr>');
    echo('            <td style="background-color:LightGrey;color:black;">'.get_string('dynamosnif', 'mod_dynamo').'</td>');
    
    $i = count($aGridlib)-1;
    for($j=0;$j<count($aGridlib[$i]);$j++) {
      $snif = $aGridlib[$i][$j];
      echo('        <td class="change-color" style="color:'.dynamo_get_color_snif($snif).'">'.number_format($snif,2,',', ' ').'<br>'.(number_format(($snif/$nbstudent)*100,2,',', ' ')).'&#37;</td>');
    }  
    echo('          </tr>');
    
    echo ('     </tbody>
              </table>
    
           </div>'); // Standard deviation = ecart type 
    
    echo('</div>'); // End grouping
    echo('</div>'); // End group_detail_table
} 
    

function display_eval_others_table($dynamo, $usrid, $display6) {
  global $CFG, $DB;
  
  $usr = $DB->get_record('user', array('id' =>$usrid )); 
  
  $grp =dynamo_get_group_from_user($dynamo->groupementid, $usrid);
  echo('<div class="eval_others_table" id="'.$grp->id.'" style="display:none;">');
    // user eval the others
  echo (' <div class="table-container">
          <h3>'.$usr->firstname.' '.$usr->lastname.' : '.get_string('dynamoteacherlvl1evalother', 'mod_dynamo').'</h3> 
            <table class="tablelvl0_rep">
              <thead>
                 <tr>
                    <th>&nbsp;</th>
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
  '); // Standard deviation = ecart type <th>'.get_string('dynamostddev', 'mod_dynamo').'</th> 
  $dynamoautoeval = array();
  
  // display group evaluation
  if($dynamo->groupeval == 1) {
    $dynamoeval = dynamo_get_evaluation($dynamo->id, $usrid, $grp->id);
    $result = dynamo_compute_basis($dynamoeval, $display6);
    echo ('<tr style="border:2px solid black;">');
    echo (' <td class="tdteach"><b>'.get_string('dynamoevalofgroup', 'mod_dynamo').'</b></td><td>'.$grp->name.'</td>');
    echo (' <td class="tdteach">'.$dynamoeval->crit1.'</td>');
    echo (' <td class="tdteach">'.$dynamoeval->crit2.'</td>');
    echo (' <td class="tdteach">'.$dynamoeval->crit3.'</td>');
    echo (' <td class="tdteach">'.$dynamoeval->crit4.'</td>');
    echo (' <td class="tdteach">'.$dynamoeval->crit5.'</td>');
    echo (' <td class="tdteach" style="display:'.$display6.'">'.$dynamoeval->crit6.'</td>');
    echo (' <td class="tdteach">'.$result->sum.'</td>');
    echo (' <td class="tdteach">'.$result->avg.'</td>');
    echo ('</tr>');
    $dynamoeval->sum = $result->sum;
    $dynamoeval->avg = $result->avg;
    $dynamoeval->grp = 1;
  }
    
        $grpusrs = dynamo_get_group_users($grp->id);
        foreach ($grpusrs as $grpusrsub) { // loop to all evaluation of  students
          $color = "";
          if($usrid == $grpusrsub->id) $color = '#6699cc';


          if($grpusrsub->id == $usrid && $dynamo->autoeval == 0) {
          } else {
            $dynamoeval = dynamo_get_evaluation($dynamo->id, $usrid, $grpusrsub->id);
            if($usrid ==  $grpusrsub->id) $dynamoautoeval[] = $dynamoeval;   
            $result = dynamo_compute_basis($dynamoeval, $display6);

            echo ('<tr>');
            echo (' <td style="color:'.$color.'" class="tdteach">'.$grpusrsub->firstname.'</td><td style="color:'.$color.'" class="tdteach">'.$grpusrsub->lastname.'</td>');
            echo (' <td class="tdteach">'.$dynamoeval->crit1.'</td>');
            echo (' <td class="tdteach">'.$dynamoeval->crit2.'</td>');
            echo (' <td class="tdteach">'.$dynamoeval->crit3.'</td>');
            echo (' <td class="tdteach">'.$dynamoeval->crit4.'</td>');
            echo (' <td class="tdteach">'.$dynamoeval->crit5.'</td>');
            echo (' <td class="tdteach" style="display:'.$display6.'">'.$dynamoeval->crit6.'</td>');
            echo (' <td class="tdteach">'.$result->sum.'</td>');
            echo (' <td class="tdteach">'.$result->avg.'</td>');
            echo ('</tr>');
            $dynamoeval->sum = $result->sum;
            $dynamoeval->avg = $result->avg;
            $dynamoeval->grp = 0;
          }
        }
  echo (' </tbody>
        </table>
      </div>
    </div>');
  
}   

function display_eval_comments_table($dynamo, $usrid) { 
      $comment = dynamo_get_comment($usrid, $dynamo);
      echo ('<div class="eval_comments_table" style="display:none;">');
      
      echo('<b>'.get_string('dynamocommentcontr', 'mod_dynamo').'</b><br>');
      echo($comment->comment1.'<br>');
      echo('<b>'.get_string('dynamocommentfonction', 'mod_dynamo').'</b><br>');
      echo($comment->comment2.'<br><br>');
      echo ('</div>');
}


function display_eval_by_others_table($dynamo, $usrid, $display6) {
  global $CFG, $DB;
  
  $usr = $DB->get_record('user', array('id' =>$usrid )); 
  
  $grp =dynamo_get_group_from_user($dynamo->groupementid, $usrid);
  echo('<div class="eval_by_others_table" id="'.$grp->id.'" style="display:none;">');
  
  
      echo (' <div class="table-container">
                <h3>'.$usr->firstname.' '.$usr->lastname.' : '.get_string('dynamoteacherlvl1othereval', 'mod_dynamo').'</h3> 
                <table class="tablelvl0_rep">
                  <thead>
                     <tr>
                        <th>&nbsp;</th>
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
   foreach ($grpusrs as $grpusrsub) { // loop to all evaluation of  students
     $color = "";
     if($usrid == $grpusrsub->id) $color = '#6699cc';
  
     if($grpusrsub->id == $usrid && $dynamo->autoeval == 0) {
     } else {
       $dynamoeval = dynamo_get_evaluation($dynamo->id, $grpusrsub->id, $usrid);
       $result = dynamo_compute_basis($dynamoeval, $display6);
  
       echo ('<tr>');
       echo (' <td style="color:'.$color.'" class="tdteach">'.$grpusrsub->firstname.'</td><td style="color:'.$color.'" class="tdteach">'.$grpusrsub->lastname.'</td>');
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
   }
  echo (' </tbody>
       </table>
     </div>
   </div>');
}  

function display_graph_radar_table($dynamo, $usrid, $display6, $jscript) {
  global $CFG, $DB;
  $dynamoautoeval = array();
  $usr      = $DB->get_record('user', array('id' =>$usrid )); 
  $grp      = dynamo_get_group_from_user($dynamo->groupementid, $usrid);
  $grpusrs  = dynamo_get_group_users($grp->id);
  
  echo('<div class="graph_radar_table">');
  
  $dynamoeval       = dynamo_get_evaluation($dynamo->id, $usrid, $usrid);
  $dynamoautoeval[] = $dynamoeval;  
   
  $labels = '[\''.get_string('dynamoparticipation', 'mod_dynamo').'\',\''.get_string('dynamoresponsabilite', 'mod_dynamo').'\',\''.get_string('dynamoscientifique', 'mod_dynamo').'\',\''.get_string('dynamotechnique', 'mod_dynamo').'\',\''.get_string('dynamoattitude', 'mod_dynamo').'\'';
  if($display6 != 'none') {
    $labels .= ',\''.$dynamo->critoptname.'\'';
  }
  $labels .= ']';
  
  $data = dynamo_compute_advanced($usrid, $dynamo);
  $canvas = '<div class="graph-block"><canvas id="cvs_'.$usrid.'" width="720" height="360">[No canvas support]</canvas></div>';
  echo($canvas);
  
  $autoevalstr = '['.$dynamoautoeval[0]->crit1.','.$dynamoautoeval[0]->crit2.','.$dynamoautoeval[0]->crit3.','.$dynamoautoeval[0]->crit4.','.$dynamoautoeval[0]->crit5;
  if($display6 != 'none')  $autoevalstr .= ','.$dynamoautoeval[0]->crit6;
  $autoevalstr .= ']';
  
  $pairevalstr = '['.round($data->autocritsum->total1/$data->nbeval,2).','.round($data->autocritsum->total2/$data->nbeval,2).','.round($data->autocritsum->total3/$data->nbeval,2).','.round($data->autocritsum->total4/$data->nbeval,2).','.round($data->autocritsum->total5/$data->nbeval,2);
  if($display6 != 'none')  $pairevalstr .= ','.round($data->autocritsum->total6/$data->nbeval,2);
  $pairevalstr .= ']';
  
  if ($dynamo->groupeval == 1) {
    $allgroupeval = dynamo_get_group_eval_avg($dynamo, $usrid, $grpusrs, $grp->id);
  } else {
    $allgroupeval     = "";
  }
  $allgroupevalstr  = "";     
  if($allgroupeval != "") {
    $allgroupevalstr = '['.$allgroupeval->crit1.','.$allgroupeval->crit2.','.$allgroupeval->crit3.','.$allgroupeval->crit4.','.$allgroupeval->crit5;
    if($display6 != 'none')  $allgroupevalstr .= ','.$allgroupeval->crit6;
    $allgroupevalstr .= ']';
  }

  $jscript = dynamo_get_graph_radar_report($jscript, $usrid, $pairevalstr, $autoevalstr, $allgroupevalstr, $labels, $usr->firstname, $usr->lastname);
  
  echo('</div>'); // End grouping xaxisLabels: '.$labels.',

  return  $jscript;
}  

function display_graph_histo_table($dynamo, $usrid, $display6, $jscript) {
  global $CFG, $DB;
  $dynamoautoeval = array();
  $usr = $DB->get_record('user', array('id' =>$usrid )); 
  $grp =dynamo_get_group_from_user($dynamo->groupementid, $usrid);

  echo('<div class="graph_histo_table">');
  $grpusrs = dynamo_get_group_users($grp->id);
  
  $dynamoeval       = dynamo_get_evaluation($dynamo->id, $usrid, $usrid);
  $dynamoautoeval[] = $dynamoeval;  
  
  $labels = '[\''.get_string('dynamoparticipation', 'mod_dynamo').'\',\''.get_string('dynamoresponsabilite', 'mod_dynamo').'\',\''.get_string('dynamoscientifique', 'mod_dynamo').'\',\''.get_string('dynamotechnique', 'mod_dynamo').'\',\''.get_string('dynamoattitude', 'mod_dynamo').'\'';
  if($display6 != 'none') {
   $labels .= ',\''.$dynamo->critoptname.'\'';
  }
  $labels .= ']';
  
  $data = dynamo_compute_advanced($usrid, $dynamo);
  $canvas = '<div class="graph-block"><canvas id="cvsh_'.$usrid.'" width="960" height="360">[No canvas support]</canvas></div>';
  echo($canvas);
  
  if ($dynamo->groupeval == 1) {
    $allgroupeval = dynamo_get_group_eval_avg($dynamo, $usrid, $grpusrs, $grp->id);
  } else {
    $allgroupeval     = "";
  }
  $allgroupevalstr  = "";     
  if($allgroupeval != "") {
    $allgroupevalstr = '['.$allgroupeval->crit1.','.$allgroupeval->crit2.','.$allgroupeval->crit3.','.$allgroupeval->crit4.','.$allgroupeval->crit5;
    if($display6 != 'none')  $allgroupevalstr .= ','.$allgroupeval->crit6;
    $allgroupevalstr .= ']';
  }
  
  if($allgroupeval == "") {
     $multievalsr  = '[';
     $multievalsr .= '['.$dynamoautoeval[0]->crit1.','.round($data->autocritsum->total1/$data->nbeval,2).']'; 
     $multievalsr .= ',['.$dynamoautoeval[0]->crit2.','.round($data->autocritsum->total2/$data->nbeval,2).']'; 
     $multievalsr .= ',['.$dynamoautoeval[0]->crit3.','.round($data->autocritsum->total3/$data->nbeval,2).']'; 
     $multievalsr .= ',['.$dynamoautoeval[0]->crit4.','.round($data->autocritsum->total4/$data->nbeval,2).']'; 
     $multievalsr .= ',['.$dynamoautoeval[0]->crit5.','.round($data->autocritsum->total5/$data->nbeval,2).']';   
     if($display6 != 'none') {
       $multievalsr .= ',['.$dynamoautoeval[0]->crit6.','.round($data->autocritsum->total6/$data->nbeval,2).']';   
     }
     $multievalsr .= ']';
  } else {
     $multievalsr  = '[';
     $multievalsr .=  '['.$dynamoautoeval[0]->crit1.','.round($data->autocritsum->total1/$data->nbeval,2).','.$allgroupeval->crit1.']'; 
     $multievalsr .= ',['.$dynamoautoeval[0]->crit2.','.round($data->autocritsum->total2/$data->nbeval,2).','.$allgroupeval->crit2.']'; 
     $multievalsr .= ',['.$dynamoautoeval[0]->crit3.','.round($data->autocritsum->total3/$data->nbeval,2).','.$allgroupeval->crit3.']'; 
     $multievalsr .= ',['.$dynamoautoeval[0]->crit4.','.round($data->autocritsum->total4/$data->nbeval,2).','.$allgroupeval->crit4.']'; 
     $multievalsr .= ',['.$dynamoautoeval[0]->crit5.','.round($data->autocritsum->total5/$data->nbeval,2).','.$allgroupeval->crit5.']';   
     if($display6 != 'none') {
       $multievalsr .= ',['.$dynamoautoeval[0]->crit6.','.round($data->autocritsum->total6/$data->nbeval,2).','.$allgroupeval->crit6.']';   
     }
     $multievalsr .= ']';
   }

   $multievalsr = str_replace ("NAN","0",$multievalsr);
      
   if( $allgroupevalstr == "") {
        $jscript = $jscript.'
            var data = '.$multievalsr.';

            new RGraph.Bar({
              id: \'cvsh_'.$usrid.'\',
              data: data,
              options: {
                  title : \''.get_string('dynamoradar01title2', 'mod_dynamo').'\',
                  colorsStroke: \'rgba(0,0,0,0)\',
                  colors: [\'Gradient(white:blue:blue:blue:blue)\',\'Gradient(white:#FFA500:#FFA500:#FFA500:#FFA500)\'],
                  backgroundGridVlines: false,
                  backgroundGridBorder: false,
                  textColor: \'black\',
                  labels: '.$labels.',
                  textSize: 8,
                  marginLeft: 35,
                  marginBottom: 35,
                  marginTop: 15,
                  marginRight: 5,
                  key: [\''.htmlspecialchars($usr->firstname,ENT_QUOTES).' '.htmlspecialchars($usr->lastname,ENT_QUOTES).'\',\''.get_string('dynamogroupevaluatedby', 'mod_dynamo').'\'], 
                  keyColors: [\'blue\', \'#FFA500\'],
              }
            }).draw();';
    } else {
        $jscript = $jscript.'
            var data = '.$multievalsr.';
              
            new RGraph.Bar({
              id: \'cvsh_'.$usrid.'\',
              data: data,
              options: {
                  title : \''.get_string('dynamoradar01title3', 'mod_dynamo').'\',
                  colorsStroke: \'rgba(0,0,0,0)\',
                  colors: [\'Gradient(white:blue:blue:blue:blue)\',\'Gradient(white:#FFA500:#FFA500:#FFA500:#FFA500)\',\'Gradient(white:#aff:#aff:#aff:#aff)\'],
                  backgroundGridVlines: false,
                  backgroundGridBorder: false,
                  textColor: \'black\',
                  labels: '.$labels.',
                  textSize: 8,
                  marginLeft: 35,
                  marginBottom: 35,
                  marginTop: 15,
                  marginRight: 5,
                  key: [\''.htmlspecialchars($usr->firstname,ENT_QUOTES).' '.htmlspecialchars($usr->lastname,ENT_QUOTES).'\',\''.get_string('dynamogroupevaluatedby', 'mod_dynamo').'\',\''.get_string('dynamogroupevalby', 'mod_dynamo').'\'], 
                  keyPositionX : 700,
                  keyPositionY : 25,
                  keyColors: [\'blue\', \'#FFA500\', \'#aff\'],
                  keyBackground: \'rgba(255,255,255,0.5)\'
              }
            }).draw();';
    }

  echo('</div>'); // End grouping xaxisLabels: '.$labels.',

  return  $jscript;
}  

// Report 004
function rep_all_confidence($dynamo, $jscript, $display6, $id) {
 echo('<div id="graph-balls">
        <canvas id="layer_gfx"      width="1030" height="1030" style="position:absolute; top:0; left:0; z-index:0;background-color:transparent;">[No canvas support]</canvas>
        <canvas id="confidence_gfx" width="1030" height="1030" style="position:absolute; top:0; left:0; z-index:0;background-color:transparent;">[No canvas support]</canvas>
      </div>');
  
 $ret = dynamo_get_all_eval_by_student($dynamo, $display6);
 
 $data = $ret->result;
 $tooltips = $ret->tooltips;
 
 $jscript = $jscript.'var data = [];';
 $idx=0;
  echo ('<div class="table-container" style="position:relative;margin-top:15px;">');
  echo('  <table class="table" style="text-align:center;">');
  echo('    <thead>');
  echo('      <tr style="cursor:pointer;">');
  echo('        <th>'.get_string('dynamogroup', 'mod_dynamo').' <i class="fas fa-sort"></i></th>');
  echo('        <th>'.get_string('dynamoheadfirstname', 'mod_dynamo').' <i class="fas fa-sort"></i></th>');
  echo('        <th>'.get_string('dynamoheadlastname', 'mod_dynamo').' <i class="fas fa-sort"></i></th>');
  echo('        <th>'.get_string('dynamoautoeval', 'mod_dynamo').' <i class="fas fa-sort"></i></th>');
  echo('        <th>'.get_string('dynamoavgeval', 'mod_dynamo').' <i class="fas fa-sort"></i></th>');
  echo('        <th><span style="font-size:2.0em;">&#8783;</span> <i class="fas fa-sort"></i></th>');
  echo('      </tr>');
  echo('    </thead>');
  echo('    <tbody>');

 foreach ($data as $i => $value) {
   $idt = Round($data[$i]->eval,2).'_'.Round($data[$i]->autoeval,2); 
   $jscript = $jscript.'data['.$idx.'] = {"id":"'.$data[$i]->userid.'","name":"'.substr_replace($tooltips[$idt],"",-1).'", "evals":"'.Round($data[$i]->eval,2).'", "autoeval":"'.Round($data[$i]->autoeval,2).'"};';
   echo('     <tr><td>'.$data[$i]->name.'</td><td>'.$data[$i]->firstname.'</td><td>'.$data[$i]->lastname.'</td><td>'.Round($data[$i]->autoeval,2).'</td><td>'. Round($data[$i]->eval,2).'</td><td>'.(Round($data[$i]->autoeval,2)-Round($data[$i]->eval,2)).'</td></tr>');   
   $idx++;
 }
  echo('    </tbody>');
  echo('  </table>');
  echo('</div>');
 
 $jscript = $jscript.'var canvas  = document.getElementById("confidence_gfx");';
 $jscript = $jscript.'var canvas2 = document.getElementById("layer_gfx");';
 $jscript = $jscript.'';
 $jscript = $jscript.'var ctx     = canvas.getContext("2d");';
 $jscript = $jscript.'var ctx2    = canvas2.getContext("2d");';
 $jscript = $jscript.'';
 $jscript = $jscript.'var border  = 30;';
 $jscript = $jscript.'var width   = canvas.width - border;';
 $jscript = $jscript.'var height  = canvas.height - border;';
 $jscript = $jscript.'var minNote = 5.5;';
 $jscript = $jscript.'var maxNote = 1.0;';
 $jscript = $jscript.'var difNote = maxNote - minNote;';
 $jscript = $jscript.'var zoom    = 0;';
 $jscript = $jscript.'';
 $jscript = $jscript.'for(i=0;i<data.length;i++) {';
 $jscript = $jscript.'  evals    = parseFloat(data[i].evals);';
 $jscript = $jscript.'  autoeval = parseFloat(data[i].autoeval);';
 $jscript = $jscript.'';
 $jscript = $jscript.'  if(evals   < minNote && evals != 0)     minNote  = Math.round(evals)-0.5;';
 $jscript = $jscript.'  if(autoeval< minNote && autoeval != 0)  minNote  = Math.round(autoeval)-0.5;';
 $jscript = $jscript.'';
 $jscript = $jscript.'  if(evals   > maxNote)  maxNote  = Math.round(evals)+0.5;';
 $jscript = $jscript.'  if(autoeval> maxNote)  maxNote  = Math.round(autoeval)+0.5;';
 $jscript = $jscript.'}  ';
 $jscript = $jscript.'difNote = maxNote - minNote;';
 $jscript = $jscript.'';
 $jscript = $jscript.'function drawAxes() {';
 $jscript = $jscript.'  ctx.clearRect(0, 0, canvas.width, canvas.height);';
 $jscript = $jscript.'  ctx.beginPath();';
 $jscript = $jscript.'  ctx.strokeStyle = "#000";';
 $jscript = $jscript.'  ctx.moveTo(border, 0);';
 $jscript = $jscript.'  ctx.lineTo(border, height);';
 $jscript = $jscript.'  ctx.stroke();';
 $jscript = $jscript.'';
 $jscript = $jscript.'  ctx.moveTo(border, height);';
 $jscript = $jscript.'  ctx.lineTo(width+border, height);';
 $jscript = $jscript.'  ctx.stroke();';
 $jscript = $jscript.'  for(i=minNote;i<=maxNote;i+=0.25) {  ';
 $jscript = $jscript.'    ctx.moveTo(border-3, (i-minNote)*(height/difNote));';
 $jscript = $jscript.'    ctx.lineTo(border+3, (i-minNote)*(height/difNote));';
 $jscript = $jscript.'    ctx.stroke();';
 $jscript = $jscript.'    ctx.moveTo(border-3, (i-minNote)*(height/difNote)+(height/difNote/2));';
 $jscript = $jscript.'    ctx.lineTo(border+3, (i-minNote)*(height/difNote)+(height/difNote/2));';
 $jscript = $jscript.'    ctx.stroke();';
 $jscript = $jscript.'';
 $jscript = $jscript.'    if(maxNote-(i-minNote)<=5) {';
 $jscript = $jscript.'      ctx.fillStyle = "#000";';
 $jscript = $jscript.'      ctx.font = "11px Verdana";';
 $jscript = $jscript.'      if(i>minNote) ctx.fillText(maxNote-(i-minNote), 0, (i-minNote)*(height/difNote)+6);';
 $jscript = $jscript.'      else ctx.fillText(maxNote-(i-minNote), 0, (i-minNote)*(height/difNote)+12);';
 $jscript = $jscript.'    }'; 
 $jscript = $jscript.'  }';
 $jscript = $jscript.'  for(i=minNote;i<=maxNote;i+=0.25) {  ';
 $jscript = $jscript.'    ctx.moveTo(border+(i-minNote)*(width/difNote), height+3);';
 $jscript = $jscript.'    ctx.lineTo(border+(i-minNote)*(width/difNote), height-3);';
 $jscript = $jscript.'    ctx.stroke();';
 $jscript = $jscript.'    ctx.moveTo(border+(i-minNote)*(width/difNote)+(width/difNote/2), height+3);';
 $jscript = $jscript.'    ctx.lineTo(border+(i-minNote)*(width/difNote)+(width/difNote/2), height-3);';
 $jscript = $jscript.'    ctx.stroke();';
 $jscript = $jscript.'';
 $jscript = $jscript.'    if(i == maxNote) decalX = 20; ';
 $jscript = $jscript.'    else decalX = 5; ';
 $jscript = $jscript.'';
 $jscript = $jscript.'    if(i<=5) {'; 
 $jscript = $jscript.'      ctx.font = "11px Verdana";';
 $jscript = $jscript.'      ctx.fillText(i, border+(i-minNote)*(width/difNote)-decalX, height+16);';
 $jscript = $jscript.'    }';
 $jscript = $jscript.'  }    ';
 $jscript = $jscript.'  ctx.font = "12px Arial";';
 $jscript = $jscript.'  ctx.fillText("Auto", border+3, 12);';
 $jscript = $jscript.'  ctx.fillText("Pairs", width-50, height-14);';
 $jscript = $jscript.'  ctx.moveTo(border, height);';
 $jscript = $jscript.'  ctx.lineTo(width+border, 0);';
 $jscript = $jscript.'  ctx.stroke();';
 $jscript = $jscript.'}';
 $jscript = $jscript.'';
 $jscript = $jscript.'function drawBalls() {';
 $jscript = $jscript.'  for(i=0;i<data.length;i++) {  ';
 $jscript = $jscript.'      x   = parseInt((data[i].evals-minNote) * (width/difNote) + border);';
 $jscript = $jscript.'      y   = parseInt(height - ((data[i].autoeval - minNote) * (height/difNote)) - 8);';
 $jscript = $jscript.'      yy  = parseInt((data[i].autoeval - minNote) * (height/difNote)); ';
 $jscript = $jscript.'      $("#graph-balls").append("<div class=\"myballs tooltip\" id=\""+data[i].id+"\" style=\"top:"+y+"px;left:"+x+"px;\"><b>&nbsp;</b><span class=\"tooltiptext\">"+data[i].name+"("+data[i].autoeval+" - "+data[i].evals+")</span></div>");';
 $jscript = $jscript.'      ctx.beginPath();';
 $jscript = $jscript.'      ctx.arc(x+4, y+4, 4, 0, 2 * Math.PI);';
 $jscript = $jscript.'      ctx.strokeStyle = "rgba(0,144,0,0.5)";';
 $jscript = $jscript.'      ctx.fillStyle   = "rgba(0,144,0,0.5)";';
 $jscript = $jscript.'      if(Math.abs(data[i].evals-data[i].autoeval)> 0.5 )  {ctx.fillStyle = "rgba(255,140,0,0.5)";  ctx.strokeStyle = "rgba(255,140,0,0.5)";}';
 $jscript = $jscript.'      if(Math.abs(data[i].evals-data[i].autoeval)> 1 )    {ctx.fillStyle = "rgba(0,0,0,0.5)";   ctx.strokeStyle = "rgba(0,0,0,0.5)";}';
 $jscript = $jscript.'      ';
 $jscript = $jscript.'      ctx.fill();';
 $jscript = $jscript.'      ctx.stroke(); ';
 $jscript = $jscript.'  }';
 $jscript = $jscript.'}  ';
 $jscript = $jscript.'';
 $jscript = $jscript.'drawAxes();';
 $jscript = $jscript.'drawBalls();';
 $jscript = $jscript.'';
 $jscript = $jscript.'';
 $jscript = $jscript.'$("div.myballs").mouseover(function() {    ';
 $jscript = $jscript.'  $( "div.myballs" ).css("display","none");';
 $jscript = $jscript.'  $(this).css("display","");';
 $jscript = $jscript.'  pos = $(this).position();';
 $jscript = $jscript.'  ctx2.clearRect(0, 0, canvas2.width, canvas2.height);';
 $jscript = $jscript.'  ctx2.beginPath();';
 $jscript = $jscript.'  ctx2.strokeStyle = "#999";';
 $jscript = $jscript.'  ctx2.moveTo(pos.left, pos.top+6);';
 $jscript = $jscript.'  ctx2.lineTo(pos.left, height);';
 $jscript = $jscript.'  ctx2.stroke();';
 $jscript = $jscript.'  ctx2.moveTo(pos.left+6, pos.top+8);';
 $jscript = $jscript.'  ctx2.lineTo(border, pos.top+8);';
 $jscript = $jscript.'  ctx2.stroke();';
 $jscript = $jscript.'}).mouseout(function() {    ';
 $jscript = $jscript.'  $("div.myballs").css("display","");';
 $jscript = $jscript.'  ctx2.clearRect(0, 0, canvas2.width, canvas2.height);';
 $jscript = $jscript.'});';

 $jscript = $jscript.' $("th").click(function(){';
 $jscript = $jscript.'    var table = $(this).parents("table").eq(0);';
 $jscript = $jscript.'    var rows = table.find("tr:gt(0)").toArray().sort(comparer($(this).index()));';
 $jscript = $jscript.'    this.asc = !this.asc;';
 $jscript = $jscript.'    if (!this.asc){rows = rows.reverse();}';
 $jscript = $jscript.'    for (var i = 0; i < rows.length; i++){table.append(rows[i]);}';
 $jscript = $jscript.'});';
 $jscript = $jscript.'function comparer(index) {';
 $jscript = $jscript.'    return function(a, b) {';
 $jscript = $jscript.'        var valA = getCellValue(a, index);';
 $jscript = $jscript.'        var valB = getCellValue(b, index);';
 $jscript = $jscript.'        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);';
 $jscript = $jscript.'    }';
 $jscript = $jscript.'}';
 $jscript = $jscript.'function getCellValue(row, index){ return $(row).children("td").eq(index).text(); }';
 $jscript = $jscript.' $(".myballs").click(function(){';
 $jscript = $jscript.'   location.href="view.php?id='.$id.'&groupid=0&usrid="+this.id+"&tab=5";';
 $jscript = $jscript.'});';
 
 return  $jscript;
}
?>  