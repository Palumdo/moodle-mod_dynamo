<?php 
//Our involvement ratio has been computed with reference to the following paper that shows NIWF to be one of the best factors to measure peer assesments :
//https://www.tandfonline.com/eprint/ee2eHDqmr2aTEb9t4dB8/full
  require_login($course, true, $cm);

  $groups = dynamo_get_groups($dynamo->groupementid);
  $jscript = '<script>
    window.onload = function ()
    {';
  echo ('<h3>'.get_string('dynamostudenttitle', 'mod_dynamo').' : '.$cm->name.'</h3>');
  echo('<select onchange="reloadme(this);">');
  echo('  <option></option>');

  $grp = 0;
  foreach ($groups as $sgrp) { // loop to all groups of grouping
    $selected = "";
    if($groupid == $sgrp->id) {
      $grp = $sgrp;
      $selected = ' selected';
    }  
    echo('  <option id="'.$sgrp->id.'"'.$selected.'>'.$sgrp->name.'</option>');
  }
  echo('</select>');
  
 if($grp != 0) {
    $grpusrs = dynamo_get_group_users($grp->id);
    echo('<h4 class="dynagroupingtitle" title="'.get_string('dynamogotoparticipant', 'mod_dynamo').'"><span class="ico-white"><i class="fas fa-user-cog"></i> '.$grp->name.'</span></h4>');
    echo('<div class="" id="'.$grp->id.'" style="display:;">');

    echo (' <div class="table-container">
              <table class="tablelvl0">
                <thead>
                  <tr><th class="dbackground">
                    <div>
                      <span class="dbottom">'.get_string('dynamoevaluator', 'mod_dynamo').'</span>
                      <span class="dtop">'.get_string('dynamoevaluated', 'mod_dynamo').'</span>
                      <div class="dline"></div>
                    </div>                  
                  </th>');
    foreach ($grpusrs as $grpusr) { // loop to all students of  groups to put their name in title
      echo('        <th style="text-align:center;">'.$grpusr->firstname.' '.$grpusr->lastname.'</th>');
    }
    echo('          <th style="text-align:center;">'.get_string('dynamoier', 'mod_dynamo').'</th>'); // add the total column
   
    echo ('       </tr>
                </thead>
                <tbody>');
    $i = 0;
    $nbstudent = 0;
    foreach ($grpusrs as $grpusr) { // loop to all students of  groups
      echo('        <tr onclick="document.location=\'view.php?id='.$cm->id.'&usrid='.$grpusr->id.'&groupid='.$grp->id.'&tab=5\'" style="cursor:pointer;">
                      <td style="background-color:#006DCC;color:white;text-align:center;">'.$grpusr->firstname.' '.$grpusr->lastname.'</td>');
      $aGridlib = dynamo_get_matrix($dynamo, $grpusrs); // get the points matrix include sum and nifs
      for ($j=0;$j<count($aGridlib[$i]);$j++) {
        if($i != $j) {
          echo('        <td style="text-align:center;">'.$aGridlib[$i][$j].'</td>');
        } else {
          echo('        <td style="text-align:center;color:#666">('.$aGridlib[$i][$j].')</td>');
        }
      }
      echo('          </tr>');
      if($aGridlib[$i][$j-1] > 0) $nbstudent++;
      $i++;
    }
    // NIFS
    echo('          <tr>');
    echo('            <td style="background-color:LightGrey;text-align:center;">'.get_string('dynamosnif', 'mod_dynamo').'</td>');
    
    $red    = 1 / ((count($aGridlib)-1)*2);
    $orange = 1 / ((count($aGridlib)-1)*1.5);
    

    $i = count($aGridlib)-1;
    for($j=0;$j<count($aGridlib[$i]);$j++) {
      $snif = $aGridlib[$i][$j];
      $color  = 'green';
      if($snif/$i < $orange) $color  = 'orange';
      if($snif/$i < $red)    $color  = 'red';
        
      echo('        <td style="background-color:white;text-align:center;color:'.$color.'">'.number_format($snif,2,',', ' ').'<br>'.(number_format(($snif/$nbstudent)*100,2,',', ' ')).'&#37;</td>');
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
      echo('<h4 class="group_detail_title" onclick="document.location=\'view.php?id='.$cm->id.'&usrid='.$grpusr->id.'&groupid='.$groupid.'&tab=5\'">'.$grpusr->firstname.' '.$grpusr->lastname.'</h4>');
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

      echo('     <th>'.get_string('dynamoparticipation',  'mod_dynamo').'</th>');
      echo('     <th>'.get_string('dynamoresponsabilite', 'mod_dynamo').'</th>');
      echo('     <th>'.get_string('dynamoscientifique',   'mod_dynamo').'</th>');
      echo('     <th>'.get_string('dynamotechnique',      'mod_dynamo').'</th>');
      echo('     <th>'.get_string('dynamotechnique',      'mod_dynamo').'</th>');
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
      echo('<b>'.get_string('dynamosnif', 'mod_dynamo').'</b> :<span style="color:white;background-color:'.dynamo_get_color_snif($snif[0]).'">'.number_format($snif[0],2,',', ' ').'</span> <a href="#" data-toggle="tooltip" dyna-data-title="'.$snif[1].'">&nbsp;<i class="fas fa-info-circle ico-blue"></i></a><br>');
      echo('<b>'.get_string('dynamoconf', 'mod_dynamo').'</b> :<span style="color:white;background-color:'.dynamo_get_color_conf($conf).'">'.number_format($conf,2,',', ' ').'</span><br>');
      echo($canvas);
      
      $allgroupevalstr = "";
      if($allgroupeval != "") {
        $allgroupevalstr = '['.$allgroupeval->crit1.','.$allgroupeval->crit2.','.$allgroupeval->crit3.','.$allgroupeval->crit4.','.$allgroupeval->crit5;
        if($display6 != 'none')  $allgroupevalstr .= ','.$allgroupeval->crit6;
        $allgroupevalstr .= ']';
      }      
      
      $jscript = dynamo_get_graph_radar($jscript, $usrid, $pairevalstr, $autoevalstr, $allgroupevalstr, $labels, $grpusr->firstname, $grpusr->lastname);
    }  
  }    

  $jscript = $jscript.'
      };
       function reloadme(obj) {
        val = $(obj).children(":selected").attr("id");
        location.href=\'view.php?id='.$id.'&usrid='.$usrid.'&groupid=\'+val+\'&tab=3\';
       }
</script>';
echo($jscript);

?>
