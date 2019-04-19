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
 * this page is for the teacher. It will display all groups information on summary
 * the aim it's to detect groups with trouble
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
//Our involvement ratio has been computed with reference to the following paper that shows NIWF to be one of the best factors to measure peer assesments :
//https://www.tandfonline.com/eprint/ee2eHDqmr2aTEb9t4dB8/full
  require_login($course, true, $cm);
   
  $stat         = dynamo_get_groupement_stat($dynamo);
  $groups       = dynamo_get_groups($dynamo->groupementid);
  $alternative  = '<div id="main-overview" style="display:none;width:100%;">';
  echo ('<h3>'.get_string('dynamostudenttitle', 'mod_dynamo').' : '.$cm->name.'&nbsp;<a style="color:green;" alt="Export Excel" title="Export Excel" href ="/mod/dynamo/export01.php?id='.$cm->id.'&instance='.$cm->instance.'&course='.$cm->course.'" class="fas fa-file-excel" target="_outside"></a></h3>');
  echo ('<div id="pleasewait">'.get_string('dynamopleasewait', 'mod_dynamo').'</div>');
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
          <div class="box-switch"><div class="box-switch-label">'.get_string('dynamoswitchoverview',  'mod_dynamo').'</div>
            <label class="switch">
              <input type="checkbox" onclick="switchoverview();">
              <span class="slider"></span>
            </label>
          </div>          
          
          <div class="box-switch" style="text-align:left;max-width:300px;width:300px;"><div style="padding:15px;">
           '.get_string('dynamogroupcount', 'mod_dynamo').            ' : '.$stat->nb_group.'<br>
           '.get_string('dynamostudentcount', 'mod_dynamo').          ' : '.$stat->nb_participant.'<br>
           '.get_string('dynamostudentnoanswerscount', 'mod_dynamo'). ' : <a href="/mod/dynamo/view.php?id='.$id.'&groupid='.$groupid.'&usrid='.$usrid.'&report=1&tab=4">'.$stat->nb_no_answer.'</a></div>
          </div>
        </div>');
  echo('<div id="table-overview"><table class="tablelvlx">
          <thead>
            <tr>
              <th style="background-color:'.$facColor.'">&nbsp;</th>
              <th>'.get_string('dynamoheadparticiaption', 'mod_dynamo').'</th>
              <th>'.get_string('dynamoheadimplication', 'mod_dynamo').'</th>
              <th>'.get_string('dynamoheadconfidence', 'mod_dynamo').'</th>
              <th>'.get_string('dynamoheadcohesion', 'mod_dynamo').'</th>
              <th>'.get_string('dynamoheadconflit', 'mod_dynamo').'</th>
              <th style="border-left:3px solid grey;text-align:center;cursor:pointer;">'.get_string('dynamoheadremarque', 'mod_dynamo').' <i class="fas fa-sort"></th>
              <th></th>
            </tr>  
          </thead>
          <tbody>
       ');
  foreach ($groups as $grp) { // loop to all groups of grouping
    $grpusrs    = dynamo_get_group_users($grp->id);
    $groupstat  = dynamo_get_group_stat($dynamo, $grpusrs, $grp->id);
    
    $coursecontext  = get_context_instance(CONTEXT_COURSE, $COURSE->id);
    // display debug info to admin
    if(has_capability('moodle/site:config', $coursecontext)) {   
      $oclique       = dynamo_get_clique($dynamo, $grpusrs, true);
    } else $oclique  = dynamo_get_clique($dynamo, $grpusrs, false);
    
    $clique = $oclique->grp;
    $type   = $oclique->type;
    $list   = $oclique->list;

    $cliqueStr  = "";
    $cnt = 0;
    foreach($clique as $cusers)  {
      if(count($cusers) > 0) {
        foreach($cusers as $cuser) {
         $cliqueStr .= '<i class="fas fa-user colok" data-id="'.$cuser.'" data-group="'.$grp->id.'" title="'.$grpusrs[$cuser]->firstname.' '.$grpusrs[$cuser]->lastname.'"></i>';
        }  
        $cliqueStr .= '|';
      }
      $cnt++;
    }
    $cliqueStr = rtrim($cliqueStr, '|');
    $cliqueStr = str_replace('>|', '><b> | </b>' ,$cliqueStr);

    // Add icon type conflit group 
    $groupstat->conflit .= dynamo_get_group_type($type, $grp->id,  $oclique->max);

    $addClass = "";
    if(strpos($groupstat->participation, 'color:#ccc')  !==false) {
      $addClass = " abstent";
    }  
    $alternative .= '<div class="overview-group'.$addClass.'" onclick="location.href=\'view.php?id='.$id.'&groupid='.$grp->id.'&tab=3\'" title="'.get_string('dynamotab3', 'mod_dynamo').'&#10;'.$groupstat->names.'"><div class="overview-name">'.$grp->name.'</div><div class="overview-climat">'.$groupstat->conflit.$groupstat->remark.'</div></div>';
    
    echo('<tr style="cursor:pointer;" onclick="location.href=\'view.php?id='.$id.'&groupid='.$grp->id.'&tab=3\'" title="'.get_string('dynamotab3', 'mod_dynamo').'">
            <td class="camera">'.$grp->name.'<div class="tooltip">&nbsp;<i class="fas fa-camera"></i><span class="tooltiptext tooltip-corr">'.$groupstat->tooltips.'</span></div></td>
            <td>'.$groupstat->participation.'</td>
            <td>'.$groupstat->implication.'</td>
            <td>'.$groupstat->confiance.'</td>
            <td>'.$cliqueStr.'</td>
            <td>'.$groupstat->conflit.'</td>
            <td class="camera-border">'.$groupstat->remark.'</td>
            <td class="td-num">⏲️</td>
          </tr>');
    ob_flush();
    flush();          
  }
  echo('
        </tbody>
      </table></div>');


  $alternative .= '</div>';
  echo($alternative);

  $jscript = '
    <script>
      $(".fa-user").click(function() {
       var usrid   = $(this).data("id");
       var groupid = $(this).data("group");
       location.href=\'view.php?id='.$id.'&groupid=\'+groupid+\'&usrid=\'+usrid+\'&tab=5\'
       event.stopPropagation();
      });
    
      window.onload = function () {
        var checkboxes = document.getElementsByTagName("input");
        for (var i=0; i<checkboxes.length; i++)  {
          if (checkboxes[i].type == "checkbox")   {
            checkboxes[i].checked = false;
          }
        }
        
        $("#button-list-teacher").css("display","flex");
        $("#pleasewait").css("display","none");
        numTable();
      };
      
      $("th:nth-child(7)").click(function(){
          var table = $(this).parents("table").eq(0);
          var rows = table.find("tr:gt(0)").toArray().sort(comparer($(this).index()));
          this.asc = !this.asc;
          if (!this.asc){rows = rows.reverse();}
          for (var i = 0; i < rows.length; i++){table.append(rows[i]);}
          numTable();
      });
      function comparer(index) {
          return function(a, b) {
              var valA = getCellValue(a, index);
              var valB = getCellValue(b, index);
              return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
          }
      }      
      function getCellValue(row, index){ return $(row).children("td").eq(index).text(); }
    </script>';
echo($jscript);
?>
