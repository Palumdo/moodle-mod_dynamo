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
 * Library of interface functions and constants.
 *
 * @package     dynamo
 * @copyright   2018 UCLouvain
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function dynamo_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        default:
            return null;
    }
}
   
/**
 * Saves a new instance of the dynamo into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $dynamo An object from the form.
 * @param dynamo_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function dynamo_add_instance($dynamo, $mform) {
    global $DB;

    $dynamo->timecreated = time();
    $formdata = $mform->get_data();
    $dynamo->crit1          = $formdata->dynamo_participation;
    $dynamo->crit2          = $formdata->dynamo_responsability;
    $dynamo->crit3          = $formdata->dynamo_science;
    $dynamo->crit4          = $formdata->dynamo_technical;
    $dynamo->crit5          = $formdata->dynamo_attitude;
    $dynamo->critopt        = $formdata->dynamo_optional;
    $dynamo->critoptname    = $formdata->dynamo_optional_name;
    $dynamo->groupementid   = $formdata->dynamo_grouping_id;
    $dynamo->autoeval       = $formdata->dynamo_auto;
    $dynamo->groupeval      = $formdata->dynamo_group_eval;
    $dynamo->comment1       = $formdata->dynamo_comment1;
    $dynamo->comment2       = $formdata->dynamo_comment2;
    
    $id = $DB->insert_record('dynamo', $dynamo);

    dynamo_grade_item_update($dynamo);
    
    return $id;
}

/**
 * Updates an instance of the dynamo in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $dynamo An object from the form in mod_form.php.
 * @param dynamo_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function dynamo_update_instance($dynamo, $mform) {
    global $DB;

    $dynamo->timemodified = time();
    $dynamo->id = $dynamo->instance;

    $formdata = $mform->get_data();
    $dynamo->crit1          = $formdata->dynamo_participation;
    $dynamo->crit2          = $formdata->dynamo_responsability;
    $dynamo->crit3          = $formdata->dynamo_science;
    $dynamo->crit4          = $formdata->dynamo_technical;
    $dynamo->crit5          = $formdata->dynamo_attitude;
    $dynamo->critopt        = $formdata->dynamo_optional;
    $dynamo->critoptname    = $formdata->dynamo_optional_name;
    $dynamo->groupementid   = $formdata->dynamo_grouping_id;
    $dynamo->autoeval       = $formdata->dynamo_auto;
    $dynamo->groupeval      = $formdata->dynamo_group_eval;
    $dynamo->comment1       = $formdata->dynamo_comment1;
    $dynamo->comment2       = $formdata->dynamo_comment2;

    dynamo_grade_item_update($dynamo);
    return $DB->update_record('dynamo', $dynamo);
}

/**
 * Removes an instance of the dynamo from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function dynamo_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('dynamo', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('dynamo_eval', array('builder' => $id));
    $DB->delete_records('dynamo', array('id' => $id));

    // must also delete sub record in dynamo_eval
    return true;
}

/**
 * Extends the global navigation tree by adding dynamo nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $dynamonode An object representing the navigation tree node.
 * @param stdClass $course.
 * @param stdClass $module.
 * @param cm_info $cm.
 */
function dynamo_extend_navigation($dynamonode, $course, $module, $cm) {
}

/**
 * Extends the settings navigation with the dynamo settings.
 *
 * This function is called when the context for the page is a dynamo module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $dynamonode {@link navigation_node}
 */
function dynamo_extend_settings_navigation(settings_navigation $settings, navigation_node $navref) {
  global $PAGE, $DB;
   
  $cm = $PAGE->cm;
  if (!$cm) {
    return;
  }

  $context = $cm->context;
  $course  = $PAGE->course;
  
  $id = $course->id;

  if (has_capability('mod/dynamo:create', $context)) {
    $url =new moodle_url($CFG->wwwroot . '/mod/dynamo/reportgrouping.php', array('id'=>$id));
    $node = navigation_node::create(get_string('dynamogrpingreport', 'mod_dynamo'), $url,navigation_node::TYPE_SETTING, null, null);
    $navref->add_node($node);
  }
  
  return;
}

/**
 * Creates or updates grade item for the given dynamo instance.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $dynamo Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function dynamo_grade_item_update($dynamo, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($dynamo->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($dynamo->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $dynamo->grade;
        $item['grademin']  = 0;
    } else if ($dynamo->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$dynamo->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('/mod/dynamo', $dynamo->course, 'mod', 'dynamo', $dynamo->id, 0, null, $item);
}

/**
 * Delete grade item for given dynamo instance.
 *
 * @param stdClass $dynamo Instance object.
 * @return grade_item.
 */
function dynamo_grade_item_delete($dynamo) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('/mod/dynamo', $dynamo->course, 'mod', 'dynamo',
                        $dynamo->id, 0, null, array('deleted' => 1));
}

/**
 * Update dynamo grades in the gradebook.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $dynamo Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function dynamo_update_grades($dynamo, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();
    grade_update('/mod/dynamo', $dynamo->course, 'mod', 'dynamo', $dynamo->id, 0, $grades);
}

/**

 */
function dynamo_get_group($grouping, $userid)  {
  global $CFG, $DB;
  $sql = " 
    SELECT t2.id,t2.name
      FROM mdl_groups_members t1
          ,mdl_groups         t2 
     WHERE groupid in (
                        SELECT groupid
                          FROM mdl_groupings_groups
                         WHERE groupingid = :param1
                       )
       
       AND t1.groupid = t2.id
       AND t1.userid  = :param2
  ";
  
  $params = array('param1' => $grouping, 'param2' => $userid);
  $result = $DB->get_record_sql($sql, $params);
  
  if( $result == false) return null;
  return $result;
}

/**

 */
function dynamo_get_groups($grouping) {
  global $CFG, $DB;
  $sql = " 
    SELECT t2.id, t2.name
      FROM mdl_groupings_groups t1
          ,mdl_groups t2
     WHERE groupingid = :param1
       AND t1.groupid = t2.id
  ";

  $params = array('param1' => $grouping);
  $result = $DB->get_records_sql($sql, $params);
  
  if( $result == false) return null;
  return $result;
}  

/**

 */
function dynamo_get_group_users($groupid)  {
  global $CFG, $DB;
  $sql = " 
    SELECT t2.*
      FROM mdl_groups_members t1
          ,mdl_user          t2 
     WHERE t1.groupid = :param1
       AND t2.id  = t1.userid
     ORDER BY t2.firstname,t2.lastname
  ";
  
  $params = array('param1' => $groupid);
  $result = $DB->get_records_sql($sql, $params);

  return $result;
}  

/**

 */
function dynamo_get_groupment_users($groupingid)  {
  global $CFG, $DB;
  $sql = " 
    SELECT t4.id, t4.firstname,t4.lastname
      FROM mdl_groupings_groups t1
          ,mdl_groups           t2
          ,mdl_groups_members   t3
          ,mdl_user             t4
     WHERE groupingid = :param1
       AND t1.groupid = t2.id
       AND t2.id      = t3.groupid
       AND t3.userid  = t4.id
     ORDER BY t4.firstname,t4.lastname
  ";
  
  $params = array('param1' => $groupingid);
  $result = $DB->get_records_sql($sql, $params);

  return $result;
} 

/**

 */
function dynamo_get_body_table($groupusers, $userid, $dynamo, $groupid) {
  global $CFG, $DB;
  $bodytable = '';
  
  $display6 = '';
  if($dynamo->critoptname == '')  $display6 = 'none';

  foreach ($groupusers as $user) {
    $color    = '';
    if($userid ==  $user->id) $color    = '#6699cc';
    
    if($userid ==  $user->id && $dynamo->autoeval == 0) {
      // no auto evaluation
    } else {

      if (!$dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' =>$userid , 'userid' =>$user->id ))) {
        $dynamoeval = new stdClass();
        $dynamoeval->crit1 = 0;
        $dynamoeval->crit2 = 0;
        $dynamoeval->crit3 = 0;
        $dynamoeval->crit4 = 0;
        $dynamoeval->crit5 = 0;
        $dynamoeval->crit6 = 0;
      }
      
      $bodytable = $bodytable.'
                      <tr>
                         <td style="color:'.$color.'">'.$user->firstname.' '.$user->lastname.'</td>
                         <td><input class="saveme hiddenval" name="'.$user->id.'_1"  id="'.$user->id.'_1"  value="'.$dynamoeval->crit1.'" style="display:none;color:#black;"><i data-id="'.$user->id.'_1" data-value="1" class="mystar fa fa-user-clock"></i><i data-id="'.$user->id.'_1" data-value="2" class="mystar fa fa-user-clock"></i><i data-id="'.$user->id.'_1" data-value="3" class="mystar fa fa-user-clock"></i><i data-id="'.$user->id.'_1" data-value="4" class="mystar fa fa-user-clock"></i><i data-id="'.$user->id.'_1" data-value="5" class="mystar fa fa-user-clock"></i></td>
                         <td><input class="saveme hiddenval" name="'.$user->id.'_2"  id="'.$user->id.'_2"  value="'.$dynamoeval->crit2.'" style="display:none;color:#black;"><i data-id="'.$user->id.'_2" data-value="1" class="mystar fa fa-medal"></i><i data-id="'.$user->id.'_2" data-value="2" class="mystar fa fa-medal"></i><i data-id="'.$user->id.'_2" data-value="3" class="mystar fa fa-medal"></i><i data-id="'.$user->id.'_2" data-value="4" class="mystar fa fa-medal"></i><i data-id="'.$user->id.'_2" data-value="5" class="mystar fa fa-medal"></i></td>
                         <td><input class="saveme hiddenval" name="'.$user->id.'_3"  id="'.$user->id.'_3"  value="'.$dynamoeval->crit3.'" style="display:none;color:#black;"><i data-id="'.$user->id.'_3" data-value="1" class="mystar fa fa-lightbulb"></i><i data-id="'.$user->id.'_3" data-value="2" class="mystar fa fa-lightbulb"></i><i data-id="'.$user->id.'_3" data-value="3" class="mystar fa fa-lightbulb"></i><i data-id="'.$user->id.'_3" data-value="4" class="mystar fa fa-lightbulb"></i><i data-id="'.$user->id.'_3" data-value="5" class="mystar fa fa-lightbulb"></i></td>
                         <td><input class="saveme hiddenval" name="'.$user->id.'_4"  id="'.$user->id.'_4"  value="'.$dynamoeval->crit4.'" style="display:none;color:#black;"><i data-id="'.$user->id.'_4" data-value="1" class="mystar fa fa-wrench"></i><i data-id="'.$user->id.'_4" data-value="2" class="mystar fa fa-wrench"></i><i data-id="'.$user->id.'_4" data-value="3" class="mystar fa fa-wrench"></i><i data-id="'.$user->id.'_4" data-value="4" class="mystar fa fa-wrench"></i><i data-id="'.$user->id.'_4" data-value="5" class="mystar fa fa-wrench"></i></td>
                         <td><input class="saveme hiddenval" name="'.$user->id.'_5"  id="'.$user->id.'_5"  value="'.$dynamoeval->crit5.'" style="display:none;color:#black;"><i data-id="'.$user->id.'_5" data-value="1" class="mystar fa fa-smile"></i><i data-id="'.$user->id.'_5" data-value="2" class="mystar fa fa-smile"></i><i data-id="'.$user->id.'_5" data-value="3" class="mystar fa fa-smile"></i><i data-id="'.$user->id.'_5" data-value="4" class="mystar fa fa-smile"></i><i data-id="'.$user->id.'_5" data-value="5" class="mystar fa fa-smile"></i></td>
                         <td style="display:'.$display6.'"><input class="saveme hiddenval" name="'.$user->id.'_6" id="'.$user->id.'_6"  value="'.$dynamoeval->crit6.'" style="display:none;color:#black;"><i data-id="'.$user->id.'_6" data-value="1" class="mystar fa fa-star"></i><i data-id="'.$user->id.'_6" data-value="2" class="mystar fa fa-star"></i><i data-id="'.$user->id.'_6" data-value="3" class="mystar fa fa-star"></i><i data-id="'.$user->id.'_6" data-value="4" class="mystar fa fa-star"></i><i data-id="'.$user->id.'_6" data-value="5" class="mystar fa fa-star"></i></td>
                      </tr>
      ';    
    }
  }

  if($dynamo->groupeval == 1) {
      if (!$dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' =>$userid , 'userid' =>$groupid ))) {
        $dynamoeval = new stdClass();
        $dynamoeval->crit1 = 0;
        $dynamoeval->crit2 = 0;
        $dynamoeval->crit3 = 0;
        $dynamoeval->crit4 = 0;
        $dynamoeval->crit5 = 0;
        $dynamoeval->crit6 = 0;
      }

      $bodytable = $bodytable.'
               <table class="table" style="border:1px solid black;">
                 <thead>
                  <th colspan="6" style="padding:0;"></th>
                 </thead>
                 <tbody>
                <tr>
                   <td style="min-width:200px;font-weight:bold;">Groupe</td>
                   <td style="min-width:160px;"><input class="savemegrp hiddenval" name="'.$groupid.'_g1"  id="'.$groupid.'_1" value="'.$dynamoeval->crit1.'" style="display:none;color:black;"><i data-id="'.$groupid.'_1" data-criteria="1" data-value="1" class="mystar fa fa-user-clock"></i><i data-id="'.$groupid.'_1" data-criteria="1" data-value="2" class="mystar fa fa-user-clock"></i><i data-id="'.$groupid.'_1" data-criteria="1" data-value="3" class="mystar fa fa-user-clock"></i><i data-id="'.$groupid.'_1" data-criteria="1" data-value="4" class="mystar fa fa-user-clock"></i><i data-id="'.$groupid.'_1" data-criteria="1" data-value="5" class="mystar fa fa-user-clock"></i></td>
                   <td style="min-width:160px;"><input class="savemegrp hiddenval" name="'.$groupid.'_g2"  id="'.$groupid.'_2" value="'.$dynamoeval->crit2.'" style="display:none;color:black;"><i data-id="'.$groupid.'_2" data-criteria="2" data-value="1" class="mystar fa fa-medal"></i><i data-id="'.$groupid.'_2" data-criteria="2" data-value="2" class="mystar fa fa-medal"></i><i data-id="'.$groupid.'_2" data-criteria="2" data-value="3" class="mystar fa fa-medal"></i><i data-id="'.$groupid.'_2" data-criteria="2" data-value="4" class="mystar fa fa-medal"></i><i data-id="'.$groupid.'_2" data-criteria="2" data-value="5" class="mystar fa fa-medal"></i></td>
                   <td style="min-width:150px;"><input class="savemegrp hiddenval" name="'.$groupid.'_g3"  id="'.$groupid.'_3" value="'.$dynamoeval->crit3.'" style="display:none;color:black;"><i data-id="'.$groupid.'_3" data-criteria="2" data-value="1" class="mystar fa fa-lightbulb"></i><i data-id="'.$groupid.'_3" data-criteria="2" data-value="2" class="mystar fa fa-lightbulb"></i><i data-id="'.$groupid.'_3" data-criteria="2" data-value="3" class="mystar fa fa-lightbulb"></i><i data-id="'.$groupid.'_3" data-criteria="2" data-value="4" class="mystar fa fa-lightbulb"></i><i data-id="'.$groupid.'_3" data-criteria="2" data-value="5" class="mystar fa fa-lightbulb"></i></td>
                   <td style="min-width:150px;"><input class="savemegrp hiddenval" name="'.$groupid.'_g4"  id="'.$groupid.'_4" value="'.$dynamoeval->crit4.'" style="display:none;color:black;"><i data-id="'.$groupid.'_4" data-criteria="2" data-value="1" class="mystar fa fa-wrench"></i><i data-id="'.$groupid.'_4" data-criteria="2" data-value="2" class="mystar fa fa-wrench"></i><i data-id="'.$groupid.'_4" data-criteria="2" data-value="3" class="mystar fa fa-wrench"></i><i data-id="'.$groupid.'_4" data-criteria="2" data-value="4" class="mystar fa fa-wrench"></i><i data-id="'.$groupid.'_4" data-criteria="2" data-value="5" class="mystar fa fa-wrench"></i></td>
                   <td style="min-width:130px;"><input class="savemegrp hiddenval" name="'.$groupid.'_g5"  id="'.$groupid.'_5" value="'.$dynamoeval->crit5.'" style="display:none;color:black;"><i data-id="'.$groupid.'_5" data-criteria="2" data-value="1" class="mystar fa fa-smile"></i><i data-id="'.$groupid.'_5" data-criteria="2" data-value="2" class="mystar fa fa-smile"></i><i data-id="'.$groupid.'_5" data-criteria="2" data-value="3" class="mystar fa fa-smile"></i><i data-id="'.$groupid.'_5" data-criteria="2" data-value="4" class="mystar fa fa-smile"></i><i data-id="'.$groupid.'_5" data-criteria="2" data-value="5" class="mystar fa fa-smile"></i></td>
                   <td style="min-width:200px;display:'.$display6.'"><input class="savemegrp hiddenval" name="'.$groupid.'_g6" id="'.$groupid.'_6" value="'.$dynamoeval->crit6.'" style="display:none;color:black;"><i data-id="'.$groupid.'_6" data-criteria="2" data-value="1" class="mystar fa fa-star"></i><i data-id="'.$groupid.'_6" data-criteria="2" data-value="2" class="mystar fa fa-star"></i><i data-id="'.$groupid.'_6" data-criteria="2" data-value="3" class="mystar fa fa-star"></i><i data-id="'.$groupid.'_6" data-criteria="2" data-value="4" class="mystar fa fa-star"></i><i data-id="'.$groupid.'_6" data-criteria="2" data-value="5" class="mystar fa fa-star"></i></td>
                </tr>
                 </tbody>
              </table>
    ';    
  }

  
  return $bodytable;
}
/**

 */
function dynamo_get_comment($evalbyid, $dynamo) {
  global $CFG, $DB;
  if (!$dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' =>$evalbyid))) {
    $dynamoeval = new stdClass();
    $dynamoeval->comment1 = '';
    $dynamoeval->comment2 = '';
  }
  
  return $dynamoeval;
}  
/**

 */
function dynamo_compute_basis($dynamoeval, $crit6) {
  $result = new stdClass();
  $nbcrit = 6;
  if($crit6 != '') $nbcrit--;
  
  $result->sum = $dynamoeval->crit1 + $dynamoeval->crit2 + $dynamoeval->crit3 + $dynamoeval->crit4 + $dynamoeval->crit5 + $dynamoeval->crit6; 
  $result->avg = round($result->sum/$nbcrit,2); 
  
  return $result;
}  
/**

 */
function dynamo_compute_advanced($userid, $dynamo) {
  global $CFG, $DB;
  $result = new stdClass();
  
  $sql = " 
    SELECT sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5 + t1.crit6) total
      FROM mdl_dynamo_eval t1
     WHERE t1.userid    = :param1
       AND t1.evalbyid != :param2
       AND t1.builder   = :param3
       AND t1.critgrp   = 0
  ";
  
  $params = array('param1' => $userid, 'param2' => $userid, 'param3' => $dynamo->id);
  $resultSum = $DB->get_record_sql($sql, $params);
  
  
  $sql = " 
    SELECT sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5 + t1.crit6) total
      FROM mdl_dynamo_eval t1
     WHERE t1.userid    = :param1
       AND t1.evalbyid  = :param2
       AND t1.builder   = :param3
  ";
  
  $params = array('param1' => $userid, 'param2' => $userid, 'param3' => $dynamo->id);
  $resultAutoSum = $DB->get_record_sql($sql, $params);
  
  $sql = " 
    SELECT count(userid) nbeval
      FROM mdl_dynamo_eval t1
     WHERE t1.userid    = :param1
       AND t1.evalbyid != :param2
       AND t1.builder   = :param3
       AND t1.critgrp   = 0
  ";
  
  $params = array('param1' => $userid, 'param2' => $userid, 'param3' => $dynamo->id);
  $resultNbEval = $DB->get_record_sql($sql, $params);

  $sql = " 
    SELECT COALESCE(sum(t1.crit1),0) total1, COALESCE(sum(t1.crit2),0) total2, COALESCE(sum(t1.crit3),0) total3, COALESCE(sum(t1.crit4),0) total4, COALESCE(sum(t1.crit5),0) total5, COALESCE(sum(t1.crit6),0) total6
      FROM mdl_dynamo_eval t1
     WHERE t1.userid   = :param1
       AND t1.evalbyid != :param2
       AND t1.builder   = :param3
       AND t1.critgrp   = 0
  ";
  
  $params = array('param1' => $userid, 'param2' => $userid, 'param3' => $dynamo->id);
  $resultAutoCritSum = $DB->get_record_sql($sql, $params);
  
  $result->sum          = $resultSum->total;
  $result->autosum      = $resultAutoSum->total;
  $result->nbeval       = $resultNbEval->nbeval;
  $result->autocritsum  = $resultAutoCritSum;
  if ($dynamo->critoptname == '') $result->nbcrit = 5;
  else $result->nbcrit = 6;
  
  return $result;
}  
/**

 */
function dynamo_cm_info_view(cm_info $cm) {
   global $DB,$USER;
   
  $role     = $DB->get_record('role', array('shortname' => 'editingteacher'));
  $context  = context_module::instance($cm->id);
  
  $isTeatcher = false;
  if (has_capability('mod/dynamo:create', $context)) {
    $isTeatcher = true;
  }

  if(!$isTeatcher) return false;

   
  if (!$dynamo = $DB->get_record('dynamo', array('id'=>$cm->instance))) {
    return false;
  }
  $cm->set_after_link(' <a alt="Export Excel" title="Export Excel" href="/mod/dynamo/export.php?id='.$cm->id.'&instance='.$cm->instance.'&course='.$cm->course.'"><img class="icon navicon" alt="Export" src="/theme/image.php/uclouvain/core/1539865978/i/report" tabindex="-1"></a>');
}      
/**

 */
function dynamo_get_grid($dynamo) {
  global $CFG, $DB;
  $result = new stdClass();
  
  $sql = " 
    SELECT RAND() mainid, t1.* FROM (
    SELECT t1.userid, t1.evalbyid,  sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5 + t1.crit6) total
      FROM mdl_dynamo_eval t1
     WHERE t1.builder   = :param1
       AND t1.critgrp   = 0
      GROUP BY t1.userid, t1.evalbyid) t1
  ";
  
  $params = array('param1' => $dynamo->id);
  $result = $DB->get_records_sql($sql, $params);      
  return $result;
}  
/**

 */
function dynamo_get_total($arrayOfObjects, $id, $by ) {
  $ok = 0;
  foreach ($arrayOfObjects as $e) {
    if($e->evalbyid == $id) $ok = 1;
  }
  
  if($ok == 0) return 0;
  
  foreach ($arrayOfObjects as $e) {
    if( $e->userid == $id && $e->evalbyid == $by) return $e->total;
  }
}  
/**

 */
function dynamo_get_group_from_user ($groupementid, $usrid) {
  global $CFG, $DB;
  $sql = " 
  SELECT t2.id, t2.name
    FROM mdl_groupings_groups t1
        ,mdl_groups t2
        ,mdl_groups_members t3
   WHERE groupingid = :param1
     AND t1.groupid = t2.id
     AND t3.groupid = t1.groupid
     AND t3.userid  = :param2
  ";

  $params = array('param1' => $groupementid, 'param2' => $usrid);
  $result = $DB->get_records_sql($sql, $params);
  
  if( $result == false) return null;
  
  foreach ($result as $grp) 
    return  $grp;
}  

/**

 */
function dynamo_get_snif($dynamo, $grpusrs, $usrid) {
    $aGrid  = [];            
    $i      = 0;
    $j      = 0;
    $ki     = 0;
    $evals  = dynamo_get_grid($dynamo);
    $calcul = 'NIWF<br>';
    
    foreach ($grpusrs as $grpusr) { // loop to all students of  groups
      $totals     = 0;
      $aGrid[$i]  = [];
      $j=0;
      foreach ($grpusrs as $grpusrev) {
        if($grpusrev->id != $grpusr->id) {
          $total          = dynamo_get_total($evals, $grpusrev->id ,$grpusr->id);
          $totals        += $total;

          $calcul .= ' + '.$total;
          
          $aGrid[$i][$j]  = $total;
        } else {
          $aGrid[$i][$j]  = 0; 
        }
       $j++;
      }  
      $aGrid[$i][$j] = $totals;
      
      $calcul .= ' = '.$totals.'<br>';
      
      if($usrid == $grpusr->id) $ki = $i;
      $i++;
    }

    $snif = 0;
    for($j=0;$j<count($aGrid) ;$j++) {
      if($aGrid[$j][count($aGrid[$j])-1] > 0) {
        $snif += $aGrid[$j][$ki] / $aGrid[$j][count($aGrid[$j])-1];      
        $calcul .= ' + ('.$aGrid[$j][$ki].'/'.$aGrid[$j][count($aGrid[$j])-1].')';
      }
    }

    return [$snif, str_replace('<br> + ', '&#xa;', $calcul)]; 
}      
/**

 */
function dynamo_get_matrix($dynamo, $grpusrs) {
    $aGrid      = [];            
    $aGridAuto  = [];            
    $i          = 0;
    $j          = 0;
    $snif       = 0;
    $evals      = dynamo_get_grid($dynamo);
    $nbuserpart = 0;
    
    foreach ($grpusrs as $grpusr) { // loop to all students of  groups
      $totals         = 0;
      $aGrid[$i]      = [];
      $aGridAuto[$i]  = [];
      $j              = 0;
      foreach ($grpusrs as $grpusrev) {
        $total              = dynamo_get_total($evals, $grpusrev->id ,$grpusr->id);
        if($grpusrev->id != $grpusr->id) {
          $totals            += $total;
          $aGrid[$i][$j]      = $total;
          $aGridAuto[$i][$j]  = 0;
        } else { // AUTO EVAL
          $aGrid[$i][$j]      = 0; 
          $aGridAuto[$i][$j]  = $total;
        }
       $j++;
      }  
      $aGrid[$i][$j] = $totals; // last column for totals
      if($totals > 0) $nbuserpart++;
      $i++;
    }
    
    // Add NIWF at the last line
    $aGrid[count($grpusrs)] = [];
    for($i=0;$i<count($grpusrs) ;$i++) {
      $snif = 0;
      for($j=0;$j<count($aGrid)-1 ;$j++) {
        if($aGrid[$j][count($aGrid[$j])-1] > 0) $snif += $aGrid[$j][$i] / $aGrid[$j][count($aGrid[$j])-1];
        if($aGrid[$j][$i] == 0) $aGrid[$j][$i] = $aGridAuto[$i][$j]; // put back the auto eval in the matrix
      }
      $aGrid[count($grpusrs)][$i] = $snif;
    }  
  return $aGrid;
}  
/**

 */
function dynamo_get_autoeval($userid, $dynamo) {
  global $DB;
 
  if (!$dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' =>$userid , 'userid' =>$userid ))) {
    $dynamoeval = new stdClass();
    $dynamoeval->crit1 = 0;
    $dynamoeval->crit2 = 0;
    $dynamoeval->crit3 = 0;
    $dynamoeval->crit4 = 0;
    $dynamoeval->crit5 = 0;
    $dynamoeval->crit6 = 0;
  }
  return $dynamoeval;
}
/**

 */
function dynamo_get_conf($dynamo, $grpusrs, $usrid) {
    $aGrid    = [];            
    $i        = 0;
    $j        = 0;
    $ki       = 0;
    $autoeval = 0;
    $conf     = 0;
    $evals    = dynamo_get_grid($dynamo);
    
    foreach ($grpusrs as $grpusr) { // loop to all students of  groups
      $totals = 0;
      $aGrid[$i] = [];
      $j=0;
      foreach ($grpusrs as $grpusrev) {
        if($grpusrev->id != $grpusr->id) {
          $total          = dynamo_get_total($evals, $grpusrev->id ,$grpusr->id);
          $totals        += $total;
          $aGrid[$i][$j]  = $total;
        } else {
          $aGrid[$i][$j]  = 0; 
          $autoeval       = dynamo_get_total($evals,  $usrid , $usrid);
        }
       $j++;
      }
      
      $aGrid[$i][$j] = $totals;
      if($usrid == $grpusr->id) $ki = $i;
      $i++;
    }

    $snif = 0;
    $tea  = 0;
    $nbstudent = 0;
    for($j=0;$j<count($aGrid) ;$j++) {
      //$snif += $aGrid[$j][$ki] / $aGrid[$j][count($aGrid[$j])-1];
      if($aGrid[$j][count($aGrid[$j])-1] > 0) {
        $snif += $aGrid[$j][$ki] / $aGrid[$j][count($aGrid[$j])-1];      
        $nbstudent++; // student that answers 
      }
    }
    
    if($autoeval == 0) return 10;
    
    $sum  = 0; // $aGrid[$ki][count($aGrid[$ki])-1];
    for($j=0;$j<count($aGrid) ;$j++) {
      if($j != $ki) $sum  += $aGrid[$j][$ki]; 
    }  
    $NSA  = ($autoeval  / $sum) * ($nbstudent-1); // (count($aGrid)-1);
    $conf = $NSA / $snif;
    
    return $conf;
}    
/**

 */
function dynamo_get_color_snif($val) {
  if($val < 0.65) return 'black';
  if($val < 0.80) return 'red';
  if($val < 0.90) return 'orange';
  
  return 'green';
}  

/**

 */
function dynamo_get_color_conf($val) {
  if($val > 1.50) return 'black';
  if($val > 1.25) return 'red';
  if($val > 1.10) return 'orange';
  
  return 'green';
}  
/**

 */
function dynamo_get_body_table_teacher($dynamo) {
  global $CFG, $DB;
  
  $sql = " 
    SELECT t2.id, t2.name
      FROM mdl_groupings_groups t1
          ,mdl_groups t2
     WHERE groupingid = :param1
       AND t1.groupid = t2.id
  ";

  $params     = array('param1' => $dynamo->groupementid);
  $result     = $DB->get_records_sql($sql, $params);
  $groupid    =  reset($result)->id;
  $groupusers = dynamo_get_group_users($groupid);

   $sql = " 
    SELECT t2.id,t2.firstname,t2.lastname
      FROM mdl_groups_members t1
          ,mdl_user          t2 
     WHERE t1.groupid = :param1
       AND t2.id  = t1.userid
     ORDER BY t2.firstname,t2.lastname
  ";
  
  $params = array('param1' => $groupid);
  $result = $DB->get_records_sql($sql, $params);
  $userid = reset($result)->id;
   
  return dynamo_get_body_table($groupusers, $userid, $dynamo, $groupid);
}

function dynamo_get_group_eval_avg($dynamo, $usrid, $grpusrs, $grpid) {
  global $CFG, $DB;
  
  $allgroupeval = new stdClass();
  $allgroupeval->crit1 = 0;
  $allgroupeval->crit2 = 0;
  $allgroupeval->crit3 = 0;
  $allgroupeval->crit4 = 0;
  $allgroupeval->crit5 = 0;
  $allgroupeval->crit6 = 0;
  $i=0;
  foreach ($grpusrs as $grpusr) { // loop to all students of  groups
//    if($usrid != $grpusr->id) {
      if ($dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' => $grpusr->id , 'userid' => $grpid ))) {
        $i++;
        $allgroupeval->crit1 += (int)$dynamoeval->crit1;
        $allgroupeval->crit2 += (int)$dynamoeval->crit2;
        $allgroupeval->crit3 += (int)$dynamoeval->crit3;
        $allgroupeval->crit4 += (int)$dynamoeval->crit4;
        $allgroupeval->crit5 += (int)$dynamoeval->crit5;
        $allgroupeval->crit6 += (int)$dynamoeval->crit6;
      }
//    }
  }

  if($i > 0) {
    $allgroupeval->crit1 =  round($allgroupeval->crit1 / $i, 2);
    $allgroupeval->crit2 =  round($allgroupeval->crit2 / $i, 2);
    $allgroupeval->crit3 =  round($allgroupeval->crit3 / $i, 2);
    $allgroupeval->crit4 =  round($allgroupeval->crit4 / $i, 2);
    $allgroupeval->crit5 =  round($allgroupeval->crit5 / $i, 2);
    $allgroupeval->crit6 =  round($allgroupeval->crit6 / $i, 2);
  }
  
  return $allgroupeval;
}  


function dynamo_get_group_stat($dynamo, $grpusrs, $grpid) {
  global $CFG, $DB;
    
  $groupstat = new stdClass();  
  $participationGood  = "";
  $participationBad   = "";
  $participation      = "";
  $implication        = "";
  $confiance          = "";
  $notperfect         = 0;
  $preSnif            = -1;
  $snifStatus         = "";
  $worstColor         = "";
  foreach ($grpusrs as $grpusr) { 
    // Participation
    if ($dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' => $grpusr->id))) {
      $participation = $participation.'<i style="font-size:1.2em;margin-left:2px;color:green" data-id="'.$grpusr->id.'" data-group="'.$grpid.'" class="fas fa-user" title="'.$grpusr->firstname.' '.$grpusr->lastname.'"></i>';
    } else {
      $notperfect++;
      $participation = $participation.'<i style="font-size:1.2em;margin-left:2px;color:#ccc" data-id="'.$grpusr->id.'" data-group="'.$grpid.'" class="fas fa-user"  title="'.$grpusr->firstname.' '.$grpusr->lastname.'"></i>';
    }
    // Implication
    $snif         = dynamo_get_snif($dynamo, $grpusrs, $grpusr->id);
    $color        = dynamo_get_color_snif($snif[0]);
    
    if($snif[0] == $preSnif && $preSnif != -1 && $snifStatus != 'notfan') {
      $snifStatus = 'fan';
    }

    if($snif[0] != $preSnif && $preSnif != -1) {
      $snifStatus = 'notfan';
    }    
    
    $preSnif = $snif[0];
  
    if($color != 'green') $notperfect++;
    
    if($color == 'black') $worstColor = 'black';
    if($color == 'red' &&  $worstColor != 'black') $worstColor = 'red';
    if($color == 'orange' &&  $worstColor == '')   $worstColor = 'orange';
    
    $implication  = $implication . '<i style="font-size:1.2em;margin-left:2px;color:'.$color.'" data-id="'.$grpusr->id.'" data-group="'.$grpid.'" class="fas fa-user"  title="'.$grpusr->firstname.' '.$grpusr->lastname.'"></i>';
    // Confiance
    $conf       = dynamo_get_conf($dynamo, $grpusrs, $grpusr->id);
    $color      = dynamo_get_color_conf($conf);
    if($color != 'green') $notperfect++;

    if($color == 'black') $worstColor = 'black';
    if($color == 'red' &&  $worstColor != 'black') $worstColor = 'red';
    if($color == 'orange' &&  $worstColor == '')   $worstColor = 'orange';

    
    $confiance  = $confiance . '<i style="font-size:1.2em;margin-left:2px;color:'.$color.'" data-id="'.$grpusr->id.'" data-group="'.$grpid.'" class="fas fa-user"  title="'.$grpusr->firstname.' '.$grpusr->lastname.'"></i>';
  }
  
  $groupstat->participation = $participation;
  $groupstat->implication   = $implication;
  $groupstat->confiance     = $confiance;
  $groupstat->remark        = "";
  if($notperfect == 0 ) {
    $groupstat->remark      = '<i style="font-size:1.0em;color:green;" class="fas fa-check"></i>';
    if($snifStatus  == 'fan') $groupstat->remark = '<i style="font-size:1.0em;color:green;" class="far fa-handshake"></i>';
  } else {
    $groupstat->remark      = '<i style="font-size:1.'.($notperfect*1.5).'em;color:'.$worstColor.';" class="fas fa-times"></i>';
  }
  
  return $groupstat;
}  

/**
 * Return the list of participants that do not participate !
 *
 * @param object $dynamo record dynamo.
 * @return recordset.
 */
function dynamo_get_report_001($dynamo) {
  global $CFG, $DB;
  
  $sql = " 
    SELECT t4.id, t4.firstname, t4.lastname, t4.email, t4.idnumber, t2.name
      FROM mdl_groupings_groups t1
          ,mdl_groups           t2
          ,mdl_groups_members   t3
          ,mdl_user             t4
     WHERE groupingid = :param1
       AND t1.groupid = t2.id
       AND t3.groupid = t1.groupid
       AND t3.userid  = t4.id
       AND t4.id   not in (SELECT distinct(t5.evalbyid)
                             FROM mdl_dynamo_eval t5
                            WHERE t5.builder = :param2
                          )  
     ORDER BY t2.name, t4.firstname, t4.lastname
      ";

  $params     = array('param1' => $dynamo->groupementid, 'param2' => $dynamo->id);
  $result     = $DB->get_records_sql($sql, $params);
  
  return $result;
}  
/**
 * Return the evaluation of a participant !
 *
 * @param object $builder, $evalbyid, $usrid.
 
 * @return object.
 */
function dynamo_get_evaluation($builder, $evalbyid, $usrid) {
  global $CFG, $DB;
  
  if (!$dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $builder, 'evalbyid' =>$evalbyid, 'userid' =>$usrid  ))) {
    $dynamoeval = new stdClass();
    $dynamoeval->crit1 = 0;
    $dynamoeval->crit2 = 0;
    $dynamoeval->crit3 = 0;
    $dynamoeval->crit4 = 0;
    $dynamoeval->crit5 = 0;
    $dynamoeval->crit6 = 0;
  }          
  
  return $dynamoeval;
}

function dynamo_get_groupement_stat($dynamo) {
  global $CFG, $DB;

  $stat = new stdClass();
  
  $sql = " 
    SELECT count(t2.id) nb_group
      FROM mdl_groupings_groups t1
          ,mdl_groups           t2
     WHERE groupingid = :param1
       AND t1.groupid = t2.id
      ";

  $params     = array('param1' => $dynamo->groupementid);
  $result     = $DB->get_record_sql($sql, $params);
  $stat->nb_group = $result->nb_group;    
  
  $sql = " 
    SELECT count(t4.id) nb_participant
      FROM mdl_groupings_groups t1
          ,mdl_groups           t2
          ,mdl_groups_members   t3
          ,mdl_user             t4
     WHERE groupingid = :param1
       AND t1.groupid = t2.id
       AND t3.groupid = t1.groupid
       AND t3.userid  = t4.id
      ";

  $params     = array('param1' => $dynamo->groupementid, 'param2' => $dynamo->id);
  $result     = $DB->get_record_sql($sql, $params);
  $stat->nb_participant = $result->nb_participant;
  
  $sql = " 
    SELECT count(t4.id) nb_no_answer
      FROM mdl_groupings_groups t1
          ,mdl_groups           t2
          ,mdl_groups_members   t3
          ,mdl_user             t4
     WHERE groupingid = :param1
       AND t1.groupid = t2.id
       AND t3.groupid = t1.groupid
       AND t3.userid  = t4.id
       AND t4.id   not in (SELECT distinct(t5.evalbyid)
                             FROM mdl_dynamo_eval t5
                            WHERE t5.builder = :param2
                          )  
      ";

  $params     = array('param1' => $dynamo->groupementid, 'param2' => $dynamo->id);
  $result     = $DB->get_record_sql($sql, $params);
  $stat->nb_no_answer = $result->nb_no_answer;
  
  return $stat;
}  

function dynamo_get_graph_radar($jscript, $usrid, $pairevalstr, $autoevalstr, $allgroupevalstr, $labels, $firstname, $lastname) {
  
  if($allgroupevalstr == "") {
    $title        = get_string('dynamoradar01title2', 'mod_dynamo');
    $strokestyle  = "['rgba(230,159,0,0.8)', 'rgba(0,0,255,0.5)']";
    $keyColors    = "['#FFA500', 'blue']";
    $keys         = "['".get_string('dynamogroupevaluatedby', 'mod_dynamo')."','".htmlspecialchars($firstname,ENT_QUOTES)." ".htmlspecialchars($lastname,ENT_QUOTES)."']";
    $data         = "[".str_replace ("NAN","0",$pairevalstr).", ".str_replace (",,,,","0,0,0,0,0",$autoevalstr)."];";
  } else {
    $title        = get_string('dynamoradar01title3', 'mod_dynamo');
    $strokestyle  = "['rgba(230,159,0,0.8)', 'rgba(0,0,255,0.5)', 'rgba(0,255,255,0.5)']";
    $keyColors    = "['#FFA500', 'blue', '#00FFFF']";
    $keys         = "['".get_string('dynamogroupevaluatedby', 'mod_dynamo')."','".htmlspecialchars($firstname,ENT_QUOTES)." ".htmlspecialchars($lastname,ENT_QUOTES)."','".get_string('dynamogroupevalby', 'mod_dynamo')."']";
    $data         = "[".str_replace ("NAN","0",$pairevalstr).", ".str_replace (",,,,","0,0,0,0,0",$autoevalstr).", ".str_replace (",,,,","0,0,0,0,0",$allgroupevalstr)."];";
  }
 
  $jscript = $jscript.'
    var data'.$usrid.' = '.$data.'
    
    var radar'.$usrid.' = new RGraph.Radar({
        id: \'cvs_'.$usrid.'\',
        data: data'.$usrid.',
        options: {
            title : \''.$title.'\',
            titleY : - 10,
            labels: '.$labels.',
            labelsAxes: \'n\',
            textSize: 10,
            clearto: \'white\',
            labelsAxesBoxed: false,
            labelsAxesBoxedZero: false,
            textAccessible: true,
            labelsOffset : 20,
            colors: [\'rgba(0,0,0,0)\'],
            colorsAlpha: 0.8,
            strokestyle: '.$strokestyle.',
            linewidth: 3,
            key: '.$keys.' ,
            keyColors: '.$keyColors.' ,
            keyInteractive: true,
            backgroundCirclesPoly: true
        }            
    }).draw();';
  
 return $jscript;
}  

function dynamo_get_graph_radar_report($jscript, $usrid, $pairevalstr, $autoevalstr, $allgroupevalstr, $labels, $firstname, $lastname) {
  
  if($allgroupevalstr == "") {
    $title        = get_string('dynamoradar01title2', 'mod_dynamo');
    $strokestyle  = "['rgba(230,159,0,0.8)', 'rgba(0,0,255,0.5)']";
    $keyColors    = "['#FFA500', 'blue']";
    $keys         = "['".get_string('dynamogroupevaluatedby', 'mod_dynamo')."','".htmlspecialchars($firstname,ENT_QUOTES)." ".htmlspecialchars($lastname,ENT_QUOTES)."']";
    $data         = "[".str_replace ("NAN","0",$pairevalstr).", ".str_replace (",,,,","0,0,0,0,0",$autoevalstr)."];";
  } else {
    $title        = get_string('dynamoradar01title3', 'mod_dynamo');
    $strokestyle  = "['rgba(230,159,0,0.8)', 'rgba(0,0,255,0.5)', 'rgba(0,255,255,0.5)']";
    $keyColors    = "['#FFA500', 'blue', '#00FFFF']";
    $keys         = "['".get_string('dynamogroupevaluatedby', 'mod_dynamo')."','".htmlspecialchars($firstname,ENT_QUOTES)." ".htmlspecialchars($lastname,ENT_QUOTES)."','".get_string('dynamogroupevalby', 'mod_dynamo')."']";
    $data         = "[".str_replace ("NAN","0",$pairevalstr).", ".str_replace (",,,,","0,0,0,0,0",$autoevalstr).", ".str_replace (",,,,","0,0,0,0,0",$allgroupevalstr)."];";
  }
 
  $jscript = $jscript.'
    var data'.$usrid.' = '.$data.'
    
    var radar'.$usrid.' = new RGraph.Radar({
        id: \'cvs_'.$usrid.'\',
        data: data'.$usrid.',
        options: {
            title : \''.$title.'\',
            titleY : - 10,
            labels: '.$labels.',
            labelsAxes: \'n\',
            textSize: 10,
            labelsOffset : 20,
            colors: [\'rgba(0,0,0,0)\'],
            strokestyle: '.$strokestyle.',
            linewidth: 3,
            key: '.$keys.' ,
            keyColors: '.$keyColors.' ,
            backgroundCirclesPoly: true
        }            
    }).draw();';
  
 return $jscript;
}  

function dynamo_get_all_eval_by_student($dynamo, $display6) {
  global $CFG, $DB;
  
  $ret = new stdClass();

  $div = 5;
  if($display6 == '' ) $div = 6;
  
  $sql = " 
    SELECT userid, firstname, lastname, sum(total)/count(userid)/".$div." eval,  groupid, name
     FROM (   
            SELECT t1.* FROM (
            SELECT t4.id groupid , t4.name ,t1.userid, t1.evalbyid,  sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5 + t1.crit6) total, t3.*
              FROM mdl_dynamo_eval      t1
                  ,mdl_user             t3
                  ,mdl_groups           t4
                  ,mdl_groups_members   t5
                  ,mdl_groupings_groups t6
             WHERE t1.builder   = :param1
               AND t1.critgrp   = 0
               AND t1.userid   != t1.evalbyid
               AND t1.userid    = t3.id
               AND t5.userid    = t1.userid
               AND t5.groupid   = t4.id
               AND t6.groupingid  = :param2
               AND t6.groupid   = t5.groupid
             GROUP BY t1.userid, t1.evalbyid) t1
           ) t2
    GROUP BY userid";

  $params = array('param1' => $dynamo->id, 'param2' => $dynamo->groupementid);
  $result = $DB->get_records_sql($sql, $params);

  $sql = " 
    SELECT userid, sum(total)/".$div." autoeval
     FROM (   
            SELECT t1.* FROM (
            SELECT t1.userid, t1.evalbyid,  sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5 + t1.crit6) total
              FROM mdl_dynamo_eval t1
             WHERE t1.builder   = :param1
               AND t1.critgrp   = 0
               AND t1.userid    = t1.evalbyid
              GROUP BY t1.userid, t1.evalbyid) t1
           ) t2
    GROUP BY userid";

  $params = array('param1' => $dynamo->id);
  $result2 = $DB->get_records_sql($sql, $params);

  foreach ($result as $i => $value) {
    $result[$i]->autoeval = $result2[$i]->autoeval;
    $idx = Round($result[$i]->eval,2).'_'.Round($result[$i]->autoeval,2);
    $tooltips[$idx] = $tooltips[$idx].htmlspecialchars($result[$i]->firstname,ENT_QUOTES).' '.htmlspecialchars($result[$i]->lastname,ENT_QUOTES).',';
  } 
  
  $ret->tooltips = $tooltips;
  $ret->result   = $result;

  return $ret;
}  