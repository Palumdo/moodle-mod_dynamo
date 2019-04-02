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
 * this page is for the teacher. It will dispaly all group information on summary
 * the aim it's to detect group with trouble
 *
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
//Our involvement ratio has been computed with reference to the following paper that shows NIWF to be one of the best factors to measure peer assesments :
//https://www.tandfonline.com/eprint/ee2eHDqmr2aTEb9t4dB8/full
  require_login($course, true, $cm);
  $stat    = dynamo_get_groupement_stat($dynamo);
  
  $groups = dynamo_get_groups($dynamo->groupementid);
  $jscript = '<script>
     function hidenoprob() {
       var $rowsNo = $(".tablelvlx tbody tr").filter(function () {
         if ( ($.trim($(this).find("td").eq(6).html())).search("thumbs-up") > -1 || ($.trim($(this).find("td").eq(6).html())).search("handshake") > -1 ) return true;
         else return false;        
       }).toggle();
     }

     function hidenotcomplete() {
       var $rowsNo = $(".tablelvlx tbody tr").filter(function () {
        if (($.trim($(this).find("td").eq(1).html())).search("color:#ccc") > -1) return true;
        else return false;
       }).toggle();
     }

     
     $( ".fa-user" ).click(function() {
        var usrid   = $(this).data("id");
        var groupid = $(this).data("group");
        location.href=\'view.php?id='.$id.'&groupid=\'+groupid+\'&usrid=\'+usrid+\'&tab=5\'
        event.stopPropagation();
     });

     
     
     
    window.onload = function ()
    {
    ';
  echo ('<h3>'.get_string('dynamostudenttitle', 'mod_dynamo').' : '.$cm->name.'&nbsp;<a style="color:green;" alt="Export Excel" title="Export Excel" href ="/mod/dynamo/export01.php?id='.$cm->id.'&instance='.$cm->instance.'&course='.$cm->course.'" class="fas fa-file-excel" target="_outside"></a></h3>');
  echo ('<div style="width:100%;margin:15px;">
          <div class="box-switch">'.get_string('dynamoremovegroupnoprobs',  'mod_dynamo').'<br>
            <label class="switch">
              <input type="checkbox" onclick="hidenoprob();">
              <span class="slider round"></span>
            </label>
          </div>
          <div class="box-switch">'.get_string('dynamoremovegroupnotcomplete',  'mod_dynamo').'<br>
            <label class="switch">
              <input type="checkbox" onclick="hidenotcomplete();">
              <span class="slider round"></span>
            </label>
          </div>          <div class="box-switch" style="text-align:left;max-width:300px;width:300px;">
           '.get_string('dynamogroupcount', 'mod_dynamo').            ' : '.$stat->nb_group.'<br>
           '.get_string('dynamostudentcount', 'mod_dynamo').          ' : '.$stat->nb_participant.'<br>
           '.get_string('dynamostudentnoanswerscount', 'mod_dynamo'). ' : '.$stat->nb_no_answer.'<br>
          </div>
        </div>');
  echo('<table class="tablelvlx">
          <thead>
            <tr>
              <th></th>
              <th>'.get_string('dynamoheadparticiaption', 'mod_dynamo').'</th>
              <th>'.get_string('dynamoheadimplication', 'mod_dynamo').'</th>
              <th>'.get_string('dynamoheadconfidence', 'mod_dynamo').'</th>
              <th>'.get_string('dynamoheadcohesion', 'mod_dynamo').'</th>
              <th>'.get_string('dynamoheadconflit', 'mod_dynamo').'</th>
              <th>'.get_string('dynamoheadremarque', 'mod_dynamo').'</th>
            </tr>  
          </thead>
          <tbody>
       ');
  foreach ($groups as $grp) { // loop to all groups of grouping
    $grpusrs    = dynamo_get_group_users($grp->id);
    $groupstat  = dynamo_get_group_stat($dynamo, $grpusrs, $grp->id);
    echo('<tr style="cursor:pointer;" onclick="location.href=\'view.php?id='.$id.'&groupid='.$grp->id.'&tab=3\'">
            <td style="background-color:#006DCC;color:white;text-align:right;padding-right:5px;">'.$grp->name.'<div class="tooltip">&nbsp;<i class="fas fa-camera"></i><span class="tooltiptext" style="text-align:left !important;left:80px !important;">'.$groupstat->tooltips.'</span></div></td>
            <td>'.$groupstat->participation.'</td>
            <td>'.$groupstat->implication.'</td>
            <td>'.$groupstat->confiance.'</td>
            <td>&nbsp;</td>
            <td>'.$groupstat->conflit.'</td>
            <td>'.$groupstat->remark.'</td>
          </tr>');
  }
  echo('
        </tbody>
      </table>');

  $jscript = $jscript.'
      };
</script>';
echo($jscript);

?>
