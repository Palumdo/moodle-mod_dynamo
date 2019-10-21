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
 * @package     mod_dynamo
 * @copyright   2019 UCLouvain
 * @author      Dominique Palumbo
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
define('DYNAMO_EVENT_TYPE_OPEN', 'open');
define('DYNAMO_EVENT_TYPE_CLOSE', 'close');


/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function dynamo_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
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
    global $DB, $CFG;

    $dynamo->timecreated = time();
    $formdata = $mform->get_data();
    $dynamo = dynamo_fill_data($formdata, $dynamo);

    $id = $DB->insert_record('dynamo', $dynamo);

    dynamo_grade_item_update($dynamo);
    // Add calendar events if necessary.
    if (!empty($dynamo->completionexpected)) {
        \core_completion\api::update_completion_date_event($dynamo->coursemodule, 'dynamo', $dynamo->id,
            $dynamo->completionexpected);
    }
    return $id;
}
/**
 * Set the data form to a dynamo object.
 *
 * @param object $dynamo An object from the form.
 * @param dynamo_mod_form $mform The form.
 * @return dynamo object
 */
function dynamo_fill_data($formdata, $dynamo) {
    $dynamo->crit1 = $formdata->dynamo_participation;
    $dynamo->crit2 = $formdata->dynamo_responsability;
    $dynamo->crit3 = $formdata->dynamo_science;
    $dynamo->crit4 = $formdata->dynamo_technical;
    $dynamo->crit5 = $formdata->dynamo_attitude;
    $dynamo->critopt = $formdata->dynamo_optional;
    $dynamo->critoptname = $formdata->dynamo_optional_name;
    $dynamo->groupingid = $formdata->dynamo_grouping_id;
    $dynamo->autoeval = $formdata->dynamo_auto;
    $dynamo->groupeval = $formdata->dynamo_group_eval;
    $dynamo->comment1 = $formdata->dynamo_comment1;
    $dynamo->comment2 = $formdata->dynamo_comment2;

    return $dynamo;
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
    global $DB, $CFG;

    $dynamo->timemodified = time();
    $dynamo->id = $dynamo->instance;

    $formdata = $mform->get_data();
    $dynamo = dynamo_fill_data($formdata, $dynamo);

    dynamo_grade_item_update($dynamo);

    // Add calendar events if necessary.
    $completionexpected = (!empty($dynamo->completionexpected)) ? $dynamo->completionexpected : null;
    \core_completion\api::update_completion_date_event($dynamo->coursemodule, 'dynamo', $dynamo->id, $completionexpected);

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
    $result = true;
    $exists = $DB->get_record('dynamo', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('dynamo_eval', array('builder' => $id));
    $DB->delete_records('dynamo', array('id' => $id));

    // Remove old calendar events.
    if (!$DB->delete_records('event', array('modulename' => 'dynamo', 'instance' => $dynamo->id))) {
        $result = false;
    }

    return $result;
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
    global $PAGE;

    $cm = $PAGE->cm;
    if (!$cm) {
        return;
    }

    $context = $cm->context;
    $course = $PAGE->course;

    if (!$course) {
        return;
    }

    // We want to add these new nodes after the Edit settings node, and before the.
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    // Code come from mod_quiz.
    $keys = $navref->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);

    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    $groupid = optional_param('groupid', 0, PARAM_INT);
    $usrid = optional_param('usrid', 0, PARAM_INT);

    if (has_capability('mod/dynamo:create', $context)) {
        // Preview student tabs.
        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'tab' => 1));
        $node = navigation_node::create(get_string('dynamotab1', 'dynamo'), $url,  navigation_node::TYPE_SETTING, null, null
                                         , new pix_icon('i/preview', ''));
        $navref->add_node($node, $beforekey);

        // Results tabs.
        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'tab' => 2));
        $resultnode = $navref->add_node(navigation_node::create(get_string('dynamomenuresults', 'dynamo'), $url,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', '')), $beforekey);

        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'tab' => 2, 'results' => 1));
        $resultnode->add_node(navigation_node::create(get_string('dynamoresults1', 'dynamo'), $url, navigation_node::TYPE_SETTING,
                                null, null, new pix_icon('i/item', '')));

        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'tab' => 2, 'results' => 2));
        $resultnode->add_node(navigation_node::create(get_string('dynamoresults2', 'dynamo'), $url, navigation_node::TYPE_SETTING,
                                null, null, new pix_icon('i/item', '')));

        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'tab' => 2, 'results' => 3));
        $resultnode->add_node(navigation_node::create(get_string('dynamoresults3', 'dynamo'), $url, navigation_node::TYPE_SETTING,
                                null, null, new pix_icon('i/item', '')));

        // Reports tab.
        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'tab' => 3));
        $reportnode = $navref->add_node(navigation_node::create(get_string('dynamomenureports', 'dynamo'), $url,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', '')), $beforekey);

        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'report' => 1, 'tab' => 3));
        $reportnode->add_node(navigation_node::create(get_string('dynamoreport01', 'dynamo'), $url, navigation_node::TYPE_SETTING,
                                null, null, new pix_icon('i/item', '')));

        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'report' => 5, 'tab' => 3));
        $reportnode->add_node(navigation_node::create(get_string('dynamoreport05', 'dynamo'), $url, navigation_node::TYPE_SETTING,
                                null, null, new pix_icon('i/item', '')));

        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'report' => 2, 'tab' => 3));
        $reportnode->add_node(navigation_node::create(get_string('dynamoreport02', 'dynamo'), $url, navigation_node::TYPE_SETTING,
                                null, null, new pix_icon('i/item', '')));

        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'report' => 3, 'tab' => 3));
        $reportnode->add_node(navigation_node::create(get_string('dynamoreport03', 'dynamo'), $url, navigation_node::TYPE_SETTING,
                                null, null, new pix_icon('i/item', '')));

        $url = new moodle_url('/mod/dynamo/view.php', array('id' => $PAGE->cm->id, 'groupid' => $groupid
                                , 'usrid' => $usrid, 'report' => 4, 'tab' => 3));
        $reportnode->add_node(navigation_node::create(get_string('dynamoreport04', 'dynamo'), $url, navigation_node::TYPE_SETTING,
                                null, null, new pix_icon('i/item', '')));
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
        $item['grademax'] = $dynamo->grade;
        $item['grademin'] = 0;
    } else if ($dynamo->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid'] = -$dynamo->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }

    if (isset($dynamo->id)) {
        grade_update('/mod/dynamo', $dynamo->course, 'mod', 'dynamo', $dynamo->id, 0, null, $item);
    }
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
    return grade_update('/mod/dynamo', $dynamo->course, 'mod', 'dynamo', $dynamo->id, 0, null, array('deleted' => 1));
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
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();
    grade_update('/mod/dynamo', $dynamo->course, 'mod', 'dynamo', $dynamo->id, 0, $grades);
}
/**
 * Get the group of a specific user.
 *
 *
 * @param int $grouping id of the grouping.
 * @param int $userid id of the user.
 *
 * return a recordset
 */
function dynamo_get_group($grouping, $userid) {
    global $DB;

    $sql = "
        SELECT t2.id,t2.name
          FROM {groups_members} t1
              ,{groups}         t2
         WHERE groupid in (
                            SELECT groupid
                              FROM {groupings_groups}
                             WHERE groupingid = :param1
                        )
          AND t1.groupid = t2.id
          AND t1.userid = :param2
    ";

    $params = array('param1' => $grouping, 'param2' => $userid);
    $result = $DB->get_record_sql($sql, $params);

    if ($result == false) {
        return null;
    }
    return $result;
}
/**
 * Get all the groups of a specific grouping.
 *
 *
 * @param int $grouping id of the grouping.
 *
 * return a recordset
 */
function dynamo_get_groups($grouping) {
    global $DB;

    $sql = "
        SELECT t2.*
          FROM {groupings_groups} t1
              ,{groups} t2
         WHERE groupingid = :param1
           AND t1.groupid = t2.id
    ";

    $params = array('param1' => $grouping);
    $result = $DB->get_records_sql($sql, $params);

    if ($result == false) {
        return null;
    }
    return $result;
}
/**
 * Get all the users of a specific group.
 *
 *
 * @param int $groupid id of the group.
 *
 * return a recordset
 */
function dynamo_get_group_users($groupid) {
    global $DB;
    $sql = "
        SELECT t2.*
          FROM {groups_members} t1
              ,{user}          t2
         WHERE t1.groupid = :param1
           AND t2.id = t1.userid
         ORDER BY t2.firstname,t2.lastname
    ";

    $params = array('param1' => $groupid);
    $result = $DB->get_records_sql($sql, $params);

    return $result;
}
/**
 * Get all the users of a specific grouping.
 *
 *
 * @param int $groupingid id of the grouping.
 *
 * return a recordset
 */
function dynamo_get_grouping_users($groupingid) {
    global $DB;
    $sql = "
        SELECT t4.id, t4.firstname,t4.lastname,t4.email,t4.idnumber
          FROM {groupings_groups} t1
              ,{groups}           t2
              ,{groups_members}   t3
              ,{user}             t4
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
 * Get a formatted HTML string with a table of student survey answers.
 *
 *
 * @param array $groupusers array of users
 * @param int $userid id of the user
 * @param record $dynamo configuration of the activity
 * @param int $groupid id of the group.
 *
 * return a formatted HTML string with a table of users data
 */
function dynamo_get_body_table($groupusers, $userid, $dynamo, $groupid) {
    global $DB;

    $icons = ['fa-user-clock', 'fa-medal', 'fa-lightbulb', 'fa-wrench', 'fa-smile', 'fa-star'];
    $values = [];
    $bodytable = '';
    $display6 = '';
    if ($dynamo->critoptname == '') {
        $display6 = 'none';
    }
    foreach ($groupusers as $user) {
        $color = '';
        if ($userid == $user->id) {
            $color = '#9cb7d4';
        }
        if ($userid == $user->id && $dynamo->autoeval == 0) {
            $color = '';
        } else {
            if (!$dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' => $userid
                , 'userid' => $user->id ))) {
                $dynamoeval = dynamo_to_zero();
            }
            $values = [$dynamoeval->crit1, $dynamoeval->crit2, $dynamoeval->crit3, $dynamoeval->crit4, $dynamoeval->crit5
                        , $dynamoeval->crit6];
            $bodytable = $bodytable.'
                <tr>
                    <td style="color:'.$color.'">'.$user->firstname.' '.$user->lastname.'</td>';
            for ($i = 0; $i < count($icons); $i++) {
                $val = $i + 1;
                $style = '';
                if ($val == 6) {
                    $style = 'style="display:'.$display6.'"';
                }
                $bodytable = $bodytable.'
              <td '.$style.'>
                  <input class="saveme hiddenval" name="'.$user->id.'_'.$val.'" id="'.$user->id.'_'.$val.'" value="'.$values[$i].'">
                  <i data-id="'.$user->id.'_'.$val.'" data-value="1" class="mystar fa '.$icons[$i].'"></i>
                  <i data-id="'.$user->id.'_'.$val.'" data-value="2" class="mystar fa '.$icons[$i].'"></i>
                  <i data-id="'.$user->id.'_'.$val.'" data-value="3" class="mystar fa '.$icons[$i].'"></i>
                  <i data-id="'.$user->id.'_'.$val.'" data-value="4" class="mystar fa '.$icons[$i].'"></i>
                  <i data-id="'.$user->id.'_'.$val.'" data-value="5" class="mystar fa '.$icons[$i].'"></i>
              </td>';
            }
            $bodytable = $bodytable.'
                </tr>';
        }
    }

    if ($dynamo->groupeval == 1) {
        if (!$dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' => $userid
            , 'userid' => $groupid ))) {
            $dynamoeval = dynamo_to_zero();
        }

        $bodytable = $bodytable.'
                <table class="table" style="border:1px solid #000;">
                    <thead><th colspan="6" style="padding:0;"></th></thead>
                    <tbody>
                <tr>
                    <td style="min-width:200px;font-weight:bold;">Groupe</td>
                    <td style="min-width:160px;">
                        <input class="savemegrp hiddenval" name="'.$groupid.'_g1"  id="'.$groupid.'_1" value="'
                        .$dynamoeval->crit1.'"><i data-id="'.$groupid
                        .'_1" data-criteria="1" data-value="1" class="mystar fa fa-user-clock"></i>
                        <i data-id="'.$groupid.'_1" data-criteria="1" data-value="2" class="mystar fa fa-user-clock"></i>
                        <i data-id="'.$groupid.'_1" data-criteria="1" data-value="3" class="mystar fa fa-user-clock"></i>
                        <i data-id="'.$groupid.'_1" data-criteria="1" data-value="4" class="mystar fa fa-user-clock"></i>
                        <i data-id="'.$groupid.'_1" data-criteria="1" data-value="5" class="mystar fa fa-user-clock"></i>
                    </td>
                    <td style="min-width:160px;">
                        <input class="savemegrp hiddenval" name="'.$groupid.'_g2"  id="'.$groupid
                        .'_2" value="'.$dynamoeval->crit2.'">
                        <i data-id="'.$groupid.'_2" data-criteria="2" data-value="1" class="mystar fa fa-medal"></i>
                        <i data-id="'.$groupid.'_2" data-criteria="2" data-value="2" class="mystar fa fa-medal"></i>
                        <i data-id="'.$groupid.'_2" data-criteria="2" data-value="3" class="mystar fa fa-medal"></i>
                        <i data-id="'.$groupid.'_2" data-criteria="2" data-value="4" class="mystar fa fa-medal"></i>
                        <i data-id="'.$groupid.'_2" data-criteria="2" data-value="5" class="mystar fa fa-medal"></i>
                    </td>
                    <td style="min-width:150px;">
                        <input class="savemegrp hiddenval" name="'.$groupid.'_g3"  id="'.$groupid.'_3" value="'
                        .$dynamoeval->crit3.'">
                        <i data-id="'.$groupid.'_3" data-criteria="2" data-value="1" class="mystar fa fa-lightbulb"></i>
                        <i data-id="'.$groupid.'_3" data-criteria="2" data-value="2" class="mystar fa fa-lightbulb"></i>
                        <i data-id="'.$groupid.'_3" data-criteria="2" data-value="3" class="mystar fa fa-lightbulb"></i>
                        <i data-id="'.$groupid.'_3" data-criteria="2" data-value="4" class="mystar fa fa-lightbulb"></i>
                        <i data-id="'.$groupid.'_3" data-criteria="2" data-value="5" class="mystar fa fa-lightbulb"></i>
                    </td>
                    <td style="min-width:150px;">
                        <input class="savemegrp hiddenval" name="'.$groupid.'_g4"  id="'.$groupid.'_4" value="'
                        .$dynamoeval->crit4.'">
                        <i data-id="'.$groupid.'_4" data-criteria="2" data-value="1" class="mystar fa fa-wrench"></i>
                        <i data-id="'.$groupid.'_4" data-criteria="2" data-value="2" class="mystar fa fa-wrench"></i>
                        <i data-id="'.$groupid.'_4" data-criteria="2" data-value="3" class="mystar fa fa-wrench"></i>
                        <i data-id="'.$groupid.'_4" data-criteria="2" data-value="4" class="mystar fa fa-wrench"></i>
                        <i data-id="'.$groupid.'_4" data-criteria="2" data-value="5" class="mystar fa fa-wrench"></i>
                    </td>
                    <td style="min-width:130px;">
                        <input class="savemegrp hiddenval" name="'.$groupid.'_g5"  id="'.$groupid.'_5" value="'
                        .$dynamoeval->crit5.'">
                        <i data-id="'.$groupid.'_5" data-criteria="2" data-value="1" class="mystar fa fa-smile"></i>
                        <i data-id="'.$groupid.'_5" data-criteria="2" data-value="2" class="mystar fa fa-smile"></i>
                        <i data-id="'.$groupid.'_5" data-criteria="2" data-value="3" class="mystar fa fa-smile"></i>
                        <i data-id="'.$groupid.'_5" data-criteria="2" data-value="4" class="mystar fa fa-smile"></i>
                        <i data-id="'.$groupid.'_5" data-criteria="2" data-value="5" class="mystar fa fa-smile"></i>
                    </td>
                    <td style="min-width:200px;display:'.$display6.'">
                        <input class="savemegrp hiddenval" name="'.$groupid.'_g6" id="'.$groupid.'_6" value="'
                        .$dynamoeval->crit6.'">
                        <i data-id="'.$groupid.'_6" data-criteria="2" data-value="1" class="mystar fa fa-star"></i>
                        <i data-id="'.$groupid.'_6" data-criteria="2" data-value="2" class="mystar fa fa-star"></i>
                        <i data-id="'.$groupid.'_6" data-criteria="2" data-value="3" class="mystar fa fa-star"></i>
                        <i data-id="'.$groupid.'_6" data-criteria="2" data-value="4" class="mystar fa fa-star"></i>
                        <i data-id="'.$groupid.'_6" data-criteria="2" data-value="5" class="mystar fa fa-star"></i>
                    </td>
                </tr>
                    </tbody>
                </table>';
    }

    return $bodytable;
}
/**
 * Get the comments of a specific  user.
 *
 *
 * @param int evalbyid id of the user that made the evaluation
 * @param record $dynamo configuration of the activity
 *
 * return the user comments
 */
function dynamo_get_comment($evalbyid, $dynamo) {
    global $DB;
    if (!$dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' => $evalbyid))) {
        $dynamoeval = new stdClass();
        $dynamoeval->comment1 = '';
        $dynamoeval->comment2 = '';
    }

    return $dynamoeval;
}
/**
 * Compute the sum and average of a specific evaluation.
 *
 *
 * @param record $dynamoeval evaluation data
 * @param int $crit6 5 or 6 criterion
 *
 * return an object with the sum and average in it
 */
function dynamo_compute_basis($dynamoeval, $crit6) {
    $result = new stdClass();
    $nbcrit = 6;
    if ($crit6 != '') {
        $nbcrit--;
    }

    $result->sum = $dynamoeval->crit1
        + $dynamoeval->crit2
        + $dynamoeval->crit3
        + $dynamoeval->crit4
        + $dynamoeval->crit5
        + $dynamoeval->crit6;
    $result->avg = round($result->sum / $nbcrit, 2);

    return $result;
}
/**
 * Compute multiple values used for the student evaluation.
 *
 * @param record $dynamo configuration of the evaluation
 * @param int $userid the user that have to be evaluated
 *
 * return an object with the sum, autosum and number of evaluator
 */
function dynamo_compute_advanced($userid, $dynamo) {
    global $DB;
    $result = new stdClass();

    $sql = "
        SELECT sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5 + t1.crit6) total
          FROM {dynamo_eval} t1
         WHERE t1.userid    = :param1
           AND t1.evalbyid != :param2
           AND t1.builder   = :param3
           AND t1.critgrp   = 0
    ";

    $params = array('param1' => $userid, 'param2' => $userid, 'param3' => $dynamo->id);
    $resultsum = $DB->get_record_sql($sql, $params);

    $sql = "
        SELECT sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5 + t1.crit6) total
          FROM {dynamo_eval} t1
         WHERE t1.userid    = :param1
           AND t1.evalbyid  = :param2
           AND t1.builder   = :param3
    ";

    $params = array('param1' => $userid, 'param2' => $userid, 'param3' => $dynamo->id);
    $resultautosum = $DB->get_record_sql($sql, $params);

    $sql = "
        SELECT count(userid) nbeval
          FROM {dynamo_eval} t1
         WHERE t1.userid    = :param1
           AND t1.evalbyid != :param2
           AND t1.builder   = :param3
           AND t1.critgrp   = 0
    ";
    $params = array('param1' => $userid, 'param2' => $userid, 'param3' => $dynamo->id);
    $resultnbeval = $DB->get_record_sql($sql, $params);

    $sql = "
        SELECT COALESCE(sum(t1.crit1),0) total1, COALESCE(sum(t1.crit2),0) total2, COALESCE(sum(t1.crit3),0) total3
              ,COALESCE(sum(t1.crit4),0) total4, COALESCE(sum(t1.crit5),0) total5, COALESCE(sum(t1.crit6),0) total6
          FROM {dynamo_eval} t1
         WHERE t1.userid   = :param1
           AND t1.evalbyid != :param2
           AND t1.builder   = :param3
           AND t1.critgrp   = 0
    ";
    $params = array('param1' => $userid, 'param2' => $userid, 'param3' => $dynamo->id);
    $resultautocritsum = $DB->get_record_sql($sql, $params);

    $result->sum = $resultsum->total;
    $result->autosum = $resultautosum->total;
    $result->nbeval = $resultnbeval->nbeval;
    $result->autocritsum = $resultautocritsum;
    if ($dynamo->critoptname == '') {
        $result->nbcrit = 5;
    } else {
        $result->nbcrit = 6;
    }

    return $result;
}
/**
 * Compute all the students evaluation sum by student.
 *
 *
 * @param record $dynamo configuration of the evaluation
 *
 * return a dataset with all evaluations
 */
function dynamo_get_grid($dynamo) {
    global $DB;
    $result = new stdClass();

    $sql = "
        SELECT concat(t1.userid , t1.evalbyid) mainid, t1.*
          FROM (
                SELECT t1.userid, t1.evalbyid,  sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5 + t1.crit6) total
                  FROM {dynamo_eval} t1
                 WHERE t1.builder   = :param1
                   AND t1.critgrp   = 0
                 GROUP BY t1.userid, t1.evalbyid) t1
    ";

    $params = array('param1' => $dynamo->id);
    $result = $DB->get_records_sql($sql, $params);
    return $result;
}
/**
 * Compute the total of the auto evaluation of a specific student
 *
 *
 * @param array $arrayofobjects evaluations
 *
 * return the total
 */
function dynamo_get_total($arrayofobjects, $id, $by ) {
    $ok = 0;
    // Validate if the student do the evaluation of the other.
    foreach ($arrayofobjects as $e) {
        if ($e->evalbyid == $id) {
            $ok = 1;
        }
    }

    if ($ok == 0) {
        return 0;
    }

    foreach ($arrayofobjects as $e) {
        if ($e->userid == $id && $e->evalbyid == $by) {
            return $e->total;
        }
    }
}
/**
 * Get the group of a specific user.
 *
 *
 * @param int $groupingid id of the grouping.
 * @param int $userid id of the user.
 *
 * return a recordset
 */
function dynamo_get_group_from_user ($groupingid, $usrid) {
    global $DB;

    $sql = "
    SELECT t2.id, t2.name
      FROM {groupings_groups} t1
          ,{groups} t2
          ,{groups_members} t3
     WHERE groupingid = :param1
       AND t1.groupid = t2.id
       AND t3.groupid = t1.groupid
       AND t3.userid  = :param2
    ";

    $params = array('param1' => $groupingid, 'param2' => $usrid);
    $result = $DB->get_records_sql($sql, $params);

    if ($result == false) {
        return null;
    }

    foreach ($result as $grp) {
        return  $grp; // Return first one for sure...
    }
}
/**
 * Get the niwf of a specific user and the string whith how it's computed.
 *
 * @param object dynamo  $dynamo
 * @param object of user $grpusrs.
 * @param int $userid id of the user.
 *
 * return an array
 */
function dynamo_get_niwf($dynamo, $grpusrs, $usrid) {
    $agrid = [];
    $i = 0;
    $j = 0;
    $ki = 0;
    $evals = dynamo_get_grid($dynamo);
    $calcul = 'NIWF<br>';

    foreach ($grpusrs as $grpusr) { // Loop to all students of  groups.
        $totals = 0;
        $agrid[$i] = [];
        $j = 0;
        foreach ($grpusrs as $grpusrev) {
            if ($grpusrev->id != $grpusr->id) {
                $total = dynamo_get_total($evals, $grpusrev->id, $grpusr->id);
                $totals += $total;
                $calcul .= ' + '.$total;
                $agrid[$i][$j] = $total;
            } else {
                $agrid[$i][$j] = 0;
            }
            $j++;
        }
        $agrid[$i][$j] = $totals;

        $calcul .= ' = '.$totals.'<br>';

        if ($usrid == $grpusr->id) {
            $ki = $i;
        }
        $i++;
    }

    $niwf = 0;
    for ($j = 0; $j < count($agrid); $j++) {
        if ($agrid[$j][count($agrid[$j]) - 1] > 0) {
            $niwf += $agrid[$j][$ki] / $agrid[$j][count($agrid[$j]) - 1];
            $calcul .= ' + ('.$agrid[$j][$ki].'/'.$agrid[$j][count($agrid[$j]) - 1].')';
        }
    }

    return [$niwf, str_replace('<br> + ', '&#xa;', $calcul)];
}

/**
 * Get the a matrix of a specific group with all evaluation sum and niwf.
 *
 * @param object dynamo  $dynamo
 * @param object of user $grpusrs.
 *
 * return an array
 */
function dynamo_get_matrix($dynamo, $grpusrs) {
    $agrid = [];
    $agridauto = [];
    $i = 0;
    $j = 0;
    $niwf = 0;
    $evals = dynamo_get_grid($dynamo);
    $nbuserpart = 0;

    foreach ($grpusrs as $grpusr) { // Loop to all students of  groups.
        $totals = 0;
        $agrid[$i] = [];
        $agridauto[$i] = [];
        $j = 0;
        foreach ($grpusrs as $grpusrev) {
            $total = dynamo_get_total($evals, $grpusrev->id, $grpusr->id);
            if ($grpusrev->id != $grpusr->id) {
                $totals += $total;
                $agrid[$i][$j] = $total;
                $agridauto[$i][$j] = 0;
            } else { // AUTO EVAL.
                $agrid[$i][$j] = 0;
                $agridauto[$i][$j] = $total;
            }
            $j++;
        }
        $agrid[$i][$j] = $totals; // Last column for totals.
        if ($totals > 0) {
            $nbuserpart++;
        }
        $i++;
    }

    // Add NIWF at the last line.
    $agrid[count($grpusrs)] = [];
    for ($i = 0; $i < count($grpusrs); $i++) {
        $niwf = 0;
        for ($j = 0; $j < count($agrid) - 1; $j++) {
            if ($agrid[$j][count($agrid[$j]) - 1] > 0) {
                $niwf += $agrid[$j][$i] / $agrid[$j][count($agrid[$j]) - 1];
            }

            if ($agrid[$j][$i] == 0) {
                $agrid[$j][$i] = $agridauto[$i][$j]; // Put back the auto eval in the matrix.
            }
        }
        $agrid[count($grpusrs)][$i] = $niwf;
    }

    return $agrid;
}
/**
 * Get the niwf of a specific user and the string with how it's computed.
 *
 * @param int $userid id of the user.
 * @param object dynamo  $dynamo
 *
 * return an object
 */
function dynamo_get_autoeval($userid, $dynamo) {
    global $DB;

    if (!$dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' => $userid
        , 'userid' => $userid ))) {
        $dynamoeval = dynamo_to_zero();
    }
    return $dynamoeval;
}
/**
 * Get the self confidence/assurance of a specific user
 * (autoeval  / SumofEvaluation) * (nbStudent-1)/NIWF
 *
 * @param object dynamo  $dynamo
 * @param object of user $grpusrs.
 * @param int $userid id of the user.
 *
 * return an integer
 */
function dynamo_get_conf($dynamo, $grpusrs, $usrid) {
    $agrid = [];
    $i = 0;
    $j = 0;
    $ki = 0;
    $autoeval = 0;
    $conf = 0;
    $evals = dynamo_get_grid($dynamo);

    foreach ($grpusrs as $grpusr) { // Loop to all students of  groups.
        $totals = 0;
        $agrid[$i] = [];
        $j = 0;
        foreach ($grpusrs as $grpusrev) {
            if ($grpusrev->id != $grpusr->id) {
                $total = dynamo_get_total($evals, $grpusrev->id, $grpusr->id);
                $totals += $total;
                $agrid[$i][$j] = $total;
            } else {
                $agrid[$i][$j] = 0;
                $autoeval = dynamo_get_total($evals, $usrid, $usrid);
            }
            $j++;
        }

        $agrid[$i][$j] = $totals;
        if ($usrid == $grpusr->id) {
            $ki = $i;
        }
        $i++;
    }

    $niwf = 0;
    $nbstudent = 0;

    for ($j = 0; $j < count($agrid); $j++) {
        if ($agrid[$j][count($agrid[$j]) - 1] > 0) {
            $niwf += $agrid[$j][$ki] / $agrid[$j][count($agrid[$j]) - 1];
            $nbstudent++; // Count only student that answers.
        }
    }
    if ($autoeval == 0) {
        return 10; // Student that don't answers can a high arbitrary score.
    }
    $sum = $agrid[$ki][count($agrid[$ki]) - 1];
    $nsa = ($autoeval / $sum) * ($nbstudent - 1);
    $conf = $nsa / $niwf;

    return $conf;
}
/**
 * Get the color to display the NIWF based on his value
 * The threshold values can be calibrated
 *
 * @param float that contain the NIWF $val
 * return a string with the color...
 */
function dynamo_get_color_niwf($val) {
    if ($val < 0.65) {
        return 'black';
    }
    if ($val < 0.80) {
        return 'red';
    }
    if ($val < 0.90) {
        return 'orange';
    }

    return 'green';
}
/**
 * Get the color to display the self confidence based on his value
 * The threshold values can be calibrated
 *
 * @param float that contain the self confidence/assurance
 * return a string with the color...
 */
function dynamo_get_color_conf($val) {
    if ($val > 1.50) {
        return 'black';
    }
    if ($val > 1.25) {
        return 'red';
    }
    if ($val > 1.10) {
        return 'orange';
    }

    return 'green';
}

/**
 * return the preview of what student see (the survey) to teacher
 *
 * @param object dynamo  $dynamo
 *
 * return a string with HTML
 */
function dynamo_get_body_table_teacher($dynamo) {
    global $DB;

    $sql = "
        SELECT t2.id, t2.name
          FROM {groupings_groups} t1
              ,{groups} t2
         WHERE groupingid = :param1
           AND t1.groupid = t2.id
    ";

    $params = array('param1' => $dynamo->groupingid);
    $result = $DB->get_records_sql($sql, $params);
    $groupid = reset($result)->id;
    $groupusers = dynamo_get_group_users($groupid);

    $sql = "
        SELECT t2.id,t2.firstname,t2.lastname
          FROM {groups_members} t1
              ,{user} t2
         WHERE t1.groupid = :param1
           AND t2.id = t1.userid
         ORDER BY t2.firstname, t2.lastname
    ";

    $params = array('param1' => $groupid);
    $result = $DB->get_records_sql($sql, $params);
    $userid = reset($result)->id;

    return dynamo_get_body_table($groupusers, $userid, $dynamo, $groupid);
}

/**
 * Return the avg evaluation of all the students on the group evaluation
 *
 * @param object dynamo  $dynamo
 * @param array $grpusrs array of user
 * @param integer $grpid id of the group
 *
 * return an object with the avg for all criterias on the group evaluation
 */
function dynamo_get_group_eval_avg($dynamo, $grpusrs, $grpid) {
    global $DB;

    $allgroupeval = new stdClass();
    $allgroupeval->crit1 = 0;
    $allgroupeval->crit2 = 0;
    $allgroupeval->crit3 = 0;
    $allgroupeval->crit4 = 0;
    $allgroupeval->crit5 = 0;
    $allgroupeval->crit6 = 0;
    $i = 0;
    foreach ($grpusrs as $grpusr) { // Loop to all students of  groups.
        if ($dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' => $grpusr->id
            , 'userid' => $grpid ))) {
            $i++;
            $allgroupeval->crit1 += (int)$dynamoeval->crit1;
            $allgroupeval->crit2 += (int)$dynamoeval->crit2;
            $allgroupeval->crit3 += (int)$dynamoeval->crit3;
            $allgroupeval->crit4 += (int)$dynamoeval->crit4;
            $allgroupeval->crit5 += (int)$dynamoeval->crit5;
            $allgroupeval->crit6 += (int)$dynamoeval->crit6;
        }
    }

    if ($i > 0) {
        $allgroupeval->crit1 = round($allgroupeval->crit1 / $i, 2);
        $allgroupeval->crit2 = round($allgroupeval->crit2 / $i, 2);
        $allgroupeval->crit3 = round($allgroupeval->crit3 / $i, 2);
        $allgroupeval->crit4 = round($allgroupeval->crit4 / $i, 2);
        $allgroupeval->crit5 = round($allgroupeval->crit5 / $i, 2);
        $allgroupeval->crit6 = round($allgroupeval->crit6 / $i, 2);
    }

    return $allgroupeval;
}

/**
 * return some indicators about how the group working
 * indicators are : response or not to the survey !
 * Participation
 * Implication
 * Self-confidence
 * Conflict
 * If student talk about each others (experimental)
 *
 * @param object dynamo  $dynamo
 * @param array $grpusrs array of user
 * @param integer $grpid id of the group
 * @param integer $notperfect value from the cohesion to compute the climat
 *
 * return an object with all indicators packed in HTML (display for teacher in global view)
 */
function dynamo_get_group_stat($dynamo, $grpusrs, $grpid, $notperfect) {
    global $DB, $OUTPUT;

    $groupstat = new stdClass();
    $participation = "";
    $implication = "";
    $confiance = "";
    $tooltips = "";
    $conflit = "";
    $nbuser = 0;
    $names = "";

    $aweight = ['#006DCC' => 0, 'orange' => 1, 'red' => 2, 'black' => 3];
    // Fontawsome icons use for showing the average climat inside the group from thunder to full sun.
    $aicon = ['fa-sun', 'fa-cloud-sun', 'fa-cloud-sun-rain ', 'fa-cloud-showers-heavy' , 'fa-bolt'];
    $aicolor = ['ca-sun', 'ca-cloud-sun', 'ca-cloud-sun-rain ', 'ca-cloud-showers-heavy' , 'ca-bolt'];

    foreach ($grpusrs as $grpusr) {
        $nbuser++;
        $avatar = new user_picture($grpusr);
        $avatar->courseid = $dynamo->course;
        $avatar->link = true;

        $tooltips .= $OUTPUT->render($avatar).' '.$grpusr->firstname.' '.$grpusr->lastname.'&#xa;<br>';

        // Participation/ as answered.
        if ($dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $dynamo->id, 'evalbyid' => $grpusr->id))) {
            $participation = $participation.'<i style="color:#006DCC;" data-id="'.$grpusr->id.'" data-group="'.$grpid.'"
                                                class="fas fa-user" title="'.$grpusr->firstname.' '.$grpusr->lastname.'"></i>';
        } else {
            $participation = $participation.'<i style="color:#ccc;" data-id="'.$grpusr->id.'" data-group="'.$grpid.'"
                                                class="fas fa-user" title="'.$grpusr->firstname.' '.$grpusr->lastname.'"></i>';
        }
        $names .= $grpusr->firstname.' '.$grpusr->lastname.'&#10;';
        // Implication.
        $niwf = dynamo_get_niwf($dynamo, $grpusrs, $grpusr->id);
        $color = dynamo_get_color_niwf($niwf[0]);

        if ($color == 'green') {
            $color = '#006DCC';
        }
        $notperfect += $aweight[$color];

        $implication = $implication . '<i style="color:'.$color.'" data-id="'.$grpusr->id.'" data-group="'.$grpid.'"
                                        class="fas fa-user" title="'.$grpusr->firstname.' '.$grpusr->lastname.'"></i>';
        // Self-insurance.
        $conf = dynamo_get_conf($dynamo, $grpusrs, $grpusr->id);
        $color = dynamo_get_color_conf($conf);
        if ($color == 'green') {
            $color = '#006DCC';
        }
        $notperfect += $aweight[$color];

        $confiance = $confiance . '<i style="color:'.$color.'" data-id="'.$grpusr->id.'" data-group="'.$grpid.'"
                                    class="fas fa-user" title="'.$grpusr->firstname.' '.$grpusr->lastname.'"></i>';

        // Find firstname lastname in comments about the group.
        foreach ($grpusrs as $grpusrname) {
            $text = preg_replace('/[^a-z\s]/', '', strtolower($dynamoeval->comment2));
            $text = preg_split('/\s+/', $text, null, PREG_SPLIT_NO_EMPTY);
            $text = array_flip($text);
            $firstname = strtolower($grpusrname->firstname);
            $lastname = strtolower($grpusrname->lastname);
            if (isset($text[$firstname]) || isset($text[$lastname])) {
                $conflit = '<i style="font-size:1.2em;color:#006DCC;" class="fas fa-comment"></i>';
            }
        }
    }

    $groupstat->participation = $participation;
    $groupstat->implication = $implication;
    $groupstat->confiance = $confiance;
    $groupstat->conflit = $conflit;
    $groupstat->remark = "";
    $groupstat->tooltips = $tooltips;
    $groupstat->names = $names;

    if ($notperfect == 0 ) {
        $groupstat->conflit = '';
    }

    $idico = round($notperfect / $nbuser / 2, 0, PHP_ROUND_HALF_DOWN);
    $groupstat->remark = '<span class="hiddenidx">'.round($notperfect / $nbuser / 2, 2)
        .'</span><i title="'.get_string('dynamoaclimate'.$idico, 'dynamo')
        .' ('.round($notperfect / $nbuser / 2, 2).')" class="fas '.$aicon[$idico].' '.$aicolor[$idico].'"></i>';

    return $groupstat;
}

/**
 * Return the list of participants that do not participate (not answer at the survey)
 *
 * @param object $dynamo record dynamo.
 *
 * @return recordset with the name and email of the non-participant.
 */
function dynamo_get_report_001($dynamo) {
    global $DB;

    $sql = "
        SELECT t4.id, t4.firstname, t4.lastname, t4.email, t4.idnumber, t2.name
          FROM {groupings_groups} t1
              ,{groups}           t2
              ,{groups_members}   t3
              ,{user}             t4
         WHERE groupingid = :param1
           AND t1.groupid = t2.id
           AND t3.groupid = t1.groupid
           AND t3.userid  = t4.id
           AND t4.id   not in (SELECT distinct(t5.evalbyid)
                                 FROM {dynamo_eval} t5
                                WHERE t5.builder = :param2
                            )
         ORDER BY t2.name, t4.firstname, t4.lastname
        ";

    $params = array('param1' => $dynamo->groupingid, 'param2' => $dynamo->id);
    $result = $DB->get_records_sql($sql, $params);

    return $result;
}
/**
 * Return the evaluation of a participant !
 *
 * @param int $builder  the builder (parent id)
 * @param int $evalbyid id of the user that do evaluation
 * @param int $usrid id of the evaluated user
 *
 * @return object.
 */
function dynamo_get_evaluation($builder, $evalbyid, $usrid) {
    global $DB;

    if (!$dynamoeval = $DB->get_record('dynamo_eval', array('builder' => $builder, 'evalbyid' => $evalbyid, 'userid' => $usrid))) {
        $dynamoeval = dynamo_to_zero();
    }

    return $dynamoeval;
}

/**
 * Return an object with some info about the grouping like number of groups, number of participants, number of participants
 * that not answsers
 *
 * @param object $dynamo record dynamo.
 *
 * @return an object with basic grouping stat
 */
function dynamo_get_grouping_stat($dynamo) {
    global $DB;

    $stat = new stdClass();

    $sql = "
        SELECT count(t2.id) nb_group
          FROM {groupings_groups} t1
              ,{groups}           t2
         WHERE groupingid = :param1
           AND t1.groupid = t2.id
        ";

    $params = array('param1' => $dynamo->groupingid);
    $result = $DB->get_record_sql($sql, $params);
    $stat->nb_group = $result->nb_group;

    $sql = "
        SELECT count(t4.id) nb_participant
         FROM {groupings_groups} t1
             ,{groups}           t2
             ,{groups_members}   t3
             ,{user}             t4
        WHERE groupingid = :param1
          AND t1.groupid = t2.id
          AND t3.groupid = t1.groupid
          AND t3.userid  = t4.id
        ";

    $params = array('param1' => $dynamo->groupingid, 'param2' => $dynamo->id);
    $result = $DB->get_record_sql($sql, $params);
    $stat->nb_participant = $result->nb_participant;

    $sql = "
        SELECT count(t4.id) nb_no_answer
          FROM {groupings_groups} t1
              ,{groups}           t2
              ,{groups_members}   t3
              ,{user}             t4
         WHERE groupingid = :param1
           AND t1.groupid = t2.id
           AND t3.groupid = t1.groupid
           AND t3.userid  = t4.id
           AND t4.id NOT IN (SELECT distinct(t5.evalbyid)
                               FROM {dynamo_eval} t5
                              WHERE t5.builder = :param2
                            )
        ";

    $params = array('param1' => $dynamo->groupingid, 'param2' => $dynamo->id);
    $result = $DB->get_record_sql($sql, $params);
    $stat->nb_no_answer = $result->nb_no_answer;

    $sql = "
        SELECT t1.*
          FROM {groupings} t1
         WHERE id = :param1
        ";

    $params = array('param1' => $dynamo->groupingid);
    $result = $DB->get_record_sql($sql, $params);
    $stat->grouping = $result;

    return $stat;
}
/**
 * Return javascript to create a radar graphic with Rgraph library for a specific student
 *
 * @param string $jscript the javascript string to return can already contain javascript
 * @param int $usrid id of the user that will be at the heart of the graph
 * @param string  $pairevalstr string that contain  javascript arrays of pair evaluation
 * @param string  $autoevalstr string that contain javascript array of self-evaluation
 * @param string $allgroupevalstr  string that contain javascript arrays with group evaluation
 * @param string $labels label for the grapth
 * @param $firstname firsname of the student at the center of the graph
 * @param $lastname lastname of the student at the center of the graph
 *
 * @return a string with javascript
 */
function dynamo_get_graph_radar($jscript, $usrid, $pairevalstr, $autoevalstr, $allgroupevalstr, $labels, $firstname, $lastname) {
    if ($allgroupevalstr == "") {
        $strokestyle = "['rgba(230,159,0,0.8)', 'rgba(0,0,255,0.5)']";
        $title = get_string('dynamoradar01title2', 'mod_dynamo');
        $keycolors = "['#FFA500', 'blue']";
        $data = "[".str_replace ("NAN", "0", $pairevalstr).", ".str_replace (",,,,", "0,0,0,0,0", $autoevalstr)."];";
        $keys = "['".get_string('dynamogroupevaluatedby', 'mod_dynamo')."','".htmlspecialchars($firstname, ENT_QUOTES)." ".
                            htmlspecialchars($lastname, ENT_QUOTES)."']";
    } else {
        $strokestyle = "['rgba(230,159,0,0.8)', 'rgba(0,0,255,0.5)', 'rgba(0,255,255,0.5)']";
        $title = get_string('dynamoradar01title3', 'mod_dynamo');
        $keycolors = "['#FFA500', 'blue', '#00FFFF']";
        $data = "[".str_replace ("NAN", "0", $pairevalstr).", ".str_replace (",,,,", "0,0,0,0,0", $autoevalstr).", ".
                            str_replace (",,,,", "0,0,0,0,0", $allgroupevalstr)."];";
        $keys = "['".get_string('dynamogroupevaluatedby', 'mod_dynamo')."','".htmlspecialchars($firstname, ENT_QUOTES)." ".
                            htmlspecialchars($lastname, ENT_QUOTES)."','".get_string('dynamogroupevalby', 'mod_dynamo')."']";
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
            keyColors: '.$keycolors.' ,
            keyInteractive: true,
            backgroundCirclesPoly: true
        }
    }).draw();';

    return $jscript;
}
/**
 * Return javascript to create a radar graphic with Rgraph library for all students of a group
 *
 * @param string $jscript the javascript string to return can already contain javascript
 * @param int    $grpid id of the group
 * @param string $datagrp a string that is a javascript arrays with self evaluation of all sudents of the group
 * @param string $title title of the graphic
 * @param string $labels label for the grapth
 * @param string $strokestyle a string that is a javascript array with the colors of the lines one by student
 * @param string $keys a string that is a javascript array with the name of all students of the groups
 * @param string $keycolors a string that is a javascript array of the colors of the key
 *
 * @return a string with javascript
 */
function dynamo_get_graph_radar_all($jscript, $grpid, $datagrp, $title,  $labels, $strokestyle, $keys, $keycolors) {
    $jscript = $jscript.'
        var data'.$grpid.' = '.$datagrp.'
        var radar'.$grpid.' = new RGraph.Radar({
            id: \'cvs_'.$grpid.'\',
            data: data'.$grpid.',
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
                keyColors: '.$keycolors.' ,
                keyInteractive: true,
                backgroundCirclesPoly: true
            }
        }).draw();';

    return $jscript;
}
/**
 * Return javascript to create a radar graphic with Rgraph library for a specific student on report
 *
 * @param string $jscript the javascript string to return can already contain javascript
 * @param int    $usrid of the user that will be at the heart of the graph
 * @param string $pairevalstr string that contain  javascript arrays of pair evaluation
 * @param string $autoevalstr string that contain javascript array of self-evaluation
 * @param string $allgroupevalstr  string that contain javascript arrays with group evaluation
 * @param string $labels label for the grapth
 * @param $firstname firsname of the student at the center of the graph
 * @param $lastname lastname of the student at the center of the graph
 *
 * @return a string with javascript
 */
function dynamo_get_graph_radar_report($jscript, $usrid, $pairevalstr, $autoevalstr, $allgroupevalstr, $labels, $firstname
    , $lastname) {
    if ($allgroupevalstr == "") {
        $title = get_string('dynamoradar01title2', 'mod_dynamo');
        $strokestyle = "['rgba(230,159,0,0.8)', 'rgba(0,0,255,0.5)']";
        $keycolors = "['#FFA500', 'blue']";
        $keys = "['".get_string('dynamogroupevaluatedby', 'mod_dynamo')."','".htmlspecialchars($firstname, ENT_QUOTES)." ".
                            htmlspecialchars($lastname, ENT_QUOTES)."']";
        $data = "[".str_replace ("NAN", "0", $pairevalstr).", ".str_replace (",,,,", "0,0,0,0,0", $autoevalstr)."];";
    } else {
        $title = get_string('dynamoradar01title3', 'mod_dynamo');
        $strokestyle = "['rgba(230,159,0,0.8)', 'rgba(0,0,255,0.5)', 'rgba(0,255,255,0.5)']";
        $keycolors = "['#FFA500', 'blue', '#00FFFF']";
        $keys = "['".get_string('dynamogroupevaluatedby', 'mod_dynamo')."','".htmlspecialchars($firstname, ENT_QUOTES)." ".
                            htmlspecialchars($lastname, ENT_QUOTES)."','".get_string('dynamogroupevalby', 'mod_dynamo')."']";
        $data = "[".str_replace ("NAN", "0", $pairevalstr).", ".str_replace (",,,,", "0,0,0,0,0", $autoevalstr).", ".
                            str_replace (",,,,", "0,0,0,0,0", $allgroupevalstr)."];";
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
            keyColors: '.$keycolors.' ,
            backgroundCirclesPoly: true
        }
    }).draw();';

    return $jscript;
}
/**
 * Return javascript to create a bar chart with Rgraph library for a specific student on report
 *
 * @param string $jscript the javascript string to return can already contain javascript
 * @param string $allgroupevalstr  string that contain  javascript arrays of group evaluation
 * @param int $usrid of the user that will be at the heart of the graph
 * @param $multievalsr  that contain javascript array of the average of the self-evaluation of other students
 * @param string $labels label for the grapth
 * @param object $usr with firstname and lastname of the student at the center of the graph

 *
 * @return a string with javascript
 */
function dynamo_get_graph_bar_report($jscript, $allgroupevalstr, $usrid, $multievalsr, $labels, $usr) {
    if ($allgroupevalstr == "") {
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
                    key: [\''.htmlspecialchars($usr->firstname, ENT_QUOTES)
                        .' '.htmlspecialchars($usr->lastname, ENT_QUOTES).'\',\''
                        .get_string('dynamogroupevaluatedby', 'mod_dynamo').'\'],
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
                    colors: [\'Gradient(white:blue:blue:blue:blue)\',\'Gradient(white:#FFA500:#FFA500:#FFA500:#FFA500)\'
                    ,\'Gradient(white:#aff:#aff:#aff:#aff)\'],
                    backgroundGridVlines: false,
                    backgroundGridBorder: false,
                    textColor: \'black\',
                    labels: '.$labels.',
                    textSize: 8,
                    marginLeft: 35,
                    marginBottom: 35,
                    marginTop: 15,
                    marginRight: 5,
                    key: [\''.htmlspecialchars($usr->firstname, ENT_QUOTES).' '.htmlspecialchars($usr->lastname, ENT_QUOTES)
                        .'\',\''.get_string('dynamogroupevaluatedby', 'mod_dynamo').
                        '\',\''.get_string('dynamogroupevalby', 'mod_dynamo').'\'],
                    keyPositionX : 700,
                    keyPositionY : 25,
                    keyColors: [\'blue\', \'#FFA500\', \'#aff\'],
                    keyBackground: \'rgba(255,255,255,0.5)\'
                }
            }).draw();';
    }
    return $jscript;
}
/**
 * Give all the sum of the evaluations normalized done by all students of the grouping
 *
 * @param object $dynamo An object from the form.
 * @param string $display6 if empty 6 criteria on this survey on not only 5
 *
 * @return object with firstname, lastname and the average of the evaluation
 */
function dynamo_get_all_eval_by_student($dynamo, $display6) {
    global $DB;

    $ret = new stdClass();
    $div = 5;
    if ($display6 == '') {
        $div = 6;
    }
    $sql = "
        SELECT userid, firstname, lastname, sum(total)/count(userid)/".$div." eval,  groupid, name
          FROM (
                SELECT t1.* FROM (
                    SELECT t4.id groupid, t4.name, t1.userid, t1.evalbyid
                          , sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5 + t1.crit6) total, t3.*
                      FROM {dynamo_eval}      t1
                          ,{user}             t3
                          ,{groups}           t4
                          ,{groups_members}   t5
                          ,{groupings_groups} t6
                     WHERE t1.builder = :param1
                       AND t1.critgrp = 0
                       AND t1.userid != t1.evalbyid
                       AND t1.userid = t3.id
                       AND t5.userid = t1.userid
                       AND t5.groupid = t4.id
                       AND t6.groupingid = :param2
                       AND t6.groupid = t5.groupid
                     GROUP BY t1.userid, t1.evalbyid) t1
               ) t2
         GROUP BY userid";

    $params = array('param1' => $dynamo->id, 'param2' => $dynamo->groupingid);
    $result = $DB->get_records_sql($sql, $params);

    $sql = "
        SELECT userid, sum(total)/".$div." autoeval
          FROM (
                SELECT t1.* FROM (
                    SELECT t1.userid, t1.evalbyid,  sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5 + t1.crit6) total
                      FROM {dynamo_eval} t1
                     WHERE t1.builder = :param1
                       AND t1.critgrp = 0
                      AND t1.userid = t1.evalbyid
                    GROUP BY t1.userid, t1.evalbyid) t1
               ) t2
         GROUP BY userid";

    $params = array('param1' => $dynamo->id);
    $result2 = $DB->get_records_sql($sql, $params);

    foreach ($result as $i => $value) {
        $result[$i]->autoeval = $result2[$i]->autoeval;
        $idx = round($result[$i]->eval, 2).'_'.round($result[$i]->autoeval, 2);
        $tooltips[$idx] = $tooltips[$idx]
            .htmlspecialchars($result[$i]->firstname, ENT_QUOTES).' '
            .htmlspecialchars($result[$i]->lastname, ENT_QUOTES).',';
    }

    $ret->tooltips = $tooltips;
    $ret->result = $result;

    return $ret;
}
/**
 * Give the quatric gap between each students of a group
 *
 * @param object $dynamo An object from the form.
 * @param array of object of users $grpusrs. all the student of a group
 * @param boolean $debug dispaly or not debug info...
 *
 * @return object with mathematical information and the type of group (homogenic,tap the hand,clustering, band)
 */
function dynamo_get_consistency($dynamo, $grpusrs) {
    $grp = [];
    $list = [];
    $cnt = 0;
    // Get the list of students that have answer. The other are not take in computing and evaluation.
    $agrpusrs = [];
    foreach ($grpusrs as $usr) {
        $autoeval = dynamo_get_autoeval($usr->id, $dynamo);
        if ($autoeval->crit1 > 0) {
            $agrpusrs[] = $usr;
        }
    }
    $grpusrs = $agrpusrs;
    // Create a list with all quatric gap ($diff) between each student of a group.
    for ($i = 0; $i < count($grpusrs); $i++) { // Loop to all students of the group.
        $usr1 = $grpusrs[$i];
        for ($j = $i + 1; $j < count($grpusrs); $j++) { // Compare to all the ohers in the group.
            $usr2 = $grpusrs[$j];
            $diff = dynamo_get_ecart_quadrique($dynamo, $usr1->id, $usr2->id)->ecart;

            if ($diff != 1000 && $diff != '') {
                $list[$cnt] = new stdClass();
                $list[$cnt]->diff = round($diff, 2);
                $list[$cnt]->user1 = $usr1->id;
                $list[$cnt]->user2 = $usr2->id;
                $cnt++;
            }
        }
    }
    // Sort the list of student by the smallest difference (ecart quadrique) first.
    usort($list, "cmp");

    $sumdiff = 0;
    $maxdiff = 0;
    $maxsize = 0;
    for ($i = 0; $i < count($list); $i++) {
        $diff = $list[$i]->diff;
        $usr1 = $list[$i]->user1;
        $usr2 = $list[$i]->user2;
        $sumdiff += $diff;

        if ($maxdiff < $diff) {
            $maxdiff = $diff;
        }

        $grpid1 = dynamo_get_group_consistency($grp, $usr1);
        $grpid2 = dynamo_get_group_consistency($grp, $usr2);
        if ($grpid1 > -1) { // Stud1 has group.
            if ($grpid2 > -1) { // Stud2 has group.
                // Nothing to do next line for test.
                $grpid2 = dynamo_get_group_consistency($grp, $usr2);
            } else { // Stud2 has no group.
                if ($diff <= 0.04) { // Add to group stud1.
                    $grp[$grpid1][count($grp[$grpid1])] = $usr2;
                } else { // Add to a new group.
                    $idx = count($grp);
                    $grp[$idx] = array($usr2);
                }
            }
        } else { // Stud1 has no group.
            if ($grpid2 > -1) { // Stud2 has group.
                if ($diff <= 0.04) { // Add to  group stud2.
                    $grp[$grpid2][count($grp[$grpid2])] = $usr1;
                } else { // Add to a new group.
                    $idx = count($grp);
                    $grp[$idx] = array($usr1);
                }
            } else { // Stud1/2 has no group.
                if ($diff <= 0.04) { // Add both to new grp.
                    $idx = count($grp);
                    $grp[$idx] = array($usr1, $usr2);
                } else {
                    $idx = count($grp); // Add stud1 new grp.
                    $grp[$idx] = array($usr1);
                    $idx = count($grp); // Add stud2 new grp.
                    $grp[$idx] = array($usr2);
                }
            }
        } // Biggest sub group.
        if ($maxsize < count($grp[$idx])) {
            $maxsize = count($grp[$idx]);
        }
    }
    $result = new stdClass();
    $result->grp = $grp;
    $result->type = 0;
    $result->list = $list;
    $result->max = $maxdiff;

    if ($maxdiff < 0.05) {
        $result->type = 2; // Homogenic.
    }
    if ($maxdiff < 0.02) {
        $result->type = 1; // Tap the hand.
    }
    if ($maxdiff >= 0.05) {
        $result->type = 3; // Clustering.
    }
    if ($maxdiff > 0.15) { // Real value should be 0.05 /!\.
        $result->type = 4; // Band.
    }
    if ($maxsize == 1 && $result->type > 2) {
        $result->type = 5;
    }
    if (count($grp) == 1 && $result->type == 3) {
        $result->type = 2;
    }

    // Ghosts too much absent or less than 3 students.
    if (count($grp) == 0 || (count($grp) == 1 && count($grp[0]) < 3 ) ) {
        $result->type = 6;
    }
    if (count($grp) == 2 && count($grp[0]) == 1 && count($grp[1]) == 1) {
        $result->type = 6;
    }

    return $result;
}
/**
 * Subfunction to simplify  dynamo_get_consistency
 * the aim is just to see if a user is already on an array fi not the array is created
 *
 * @param arrays $grp array of user group with similar quatric gap
 * @param integer $usr id of the user
 *
 * @return the id of the array if the user is already in an array if not -1
 */
function dynamo_get_group_consistency($grp, $usr) {
    for ($i = 0; $i < count($grp); $i++) {
        for ($j = 0; $j < count($grp[$i]); $j++) {
            if ($grp[$i][$j] == $usr) {
                return $i;
            }
        }
    }

    return -1;
}

/**
 * subfunction to simplify  dynamo_get_consistency
 * the aim is just to sort array
 *
 * @param float $a first value
 * @param float $b second value
 *
 * @return wich element is the biggest to sort it
 */
function cmp($a, $b) {
    return $a->diff > $b->diff;
}

/**
 * Give the quatric gap between two students of a group
 *
 * @param object $dynamo An object from the form.
 * @param int $usr1 id of the first user
 * @param int $usr2 id of the second user
 *
 * @return float the quatric gap between these two students
 */
function dynamo_get_ecart_quadrique($dynamo, $usr1, $usr2) {
    global $DB;

    $avg = 0;
    $nbeval = 0;
    $sumeval = 0;
    $similitude = 0;

    // Sum of evaluation.
    $sql = "
        SELECT t1.evalbyid,  sum(t1.crit1 + t1.crit2 + t1.crit3 + t1.crit4 + t1.crit5) total
          FROM {dynamo_eval} t1
          WHERE t1.builder   = :param1
            AND t1.critgrp   = 0
            AND evalbyid     = :param2
    ";

    $params = array('param1' => $dynamo->id, 'param2' => $usr1);
    $result = $DB->get_record_sql($sql, $params);
    $sumeval = $result->total;

    if ($sumeval == 0) {
        $result = new stdClass();
        $result->ecart = 1000;
        $result->similitude = 1000;

        return $result;
    }

    // Number of evaluation.
    $sql = "
        SELECT (count(t1.crit1) + count(t1.crit2) + count(t1.crit3) + count(t1.crit4) + count(t1.crit5)) nbeval
          FROM {dynamo_eval} t1
         WHERE t1.builder   = :param1
           AND t1.critgrp   = 0
           AND evalbyid     = :param2
    ";

    $params = array('param1' => $dynamo->id, 'param2' => $usr1);
    $result = $DB->get_record_sql($sql, $params);
    $nbeval = $result->nbeval;

    $avg = round($sumeval / $nbeval, 3);

    // Sum of the 6 criteria of student 1 minus the same criteria of student2 put at POW2 and after  to square2.
    $sql = "
    SELECT sum(t1.crit1/".$nbeval." + t1.crit2/".$nbeval." + t1.crit3/".$nbeval." + t1.crit4/".$nbeval." + t1.crit5/"
        .$nbeval.") ecart
        FROM (
              SELECT SQRT(POW(((t1.crit1/".$avg.") - (t2.crit1/".$avg.")),2)) crit1
                    ,SQRT(POW(((t1.crit2/".$avg.") - (t2.crit2/".$avg.")),2)) crit2
                    ,SQRT(POW(((t1.crit3/".$avg.") - (t2.crit3/".$avg.")),2)) crit3
                    ,SQRT(POW(((t1.crit4/".$avg.") - (t2.crit4/".$avg.")),2)) crit4
                    ,SQRT(POW(((t1.crit5/".$avg.") - (t2.crit5/".$avg.")),2)) crit5
                FROM {dynamo_eval} t1
                    ,(SELECT t1.userid, t1.crit1 , t1.crit2 , t1.crit3 , t1.crit4 , t1.crit5
                        FROM {dynamo_eval} t1
                       WHERE t1.builder   = :param1
                         AND t1.critgrp   = 0
                         AND t1.evalbyid  = :param2
                     ) t2
               WHERE t1.builder   = :param3
                 AND t1.critgrp   = 0
                 AND evalbyid     = :param4
                 AND t1.userid    = t2.userid
            ) t1
    ";

    $params = array('param1' => $dynamo->id, 'param2' => $usr2, 'param3' => $dynamo->id, 'param4' => $usr1);
    $result = $DB->get_record_sql($sql, $params);
    $result->similitude = $similitude;
    return $result;
}

/**
 * Give the html(specific icon with a specific color) that represent the type of group in the global view on groups of the teacher
 *
 * @param int $type the type of the group
 * @param int $grpid id of the group
 *
 * @return html fontawesome ico...
 */
function dynamo_get_group_type($type, $grpid, $max) {
    switch($type) {
        case 1:
            return ' '.'<div style="float:left;color:#006DCC;">
                        <i class="fas fa-heart" data-id="'.$grpid.'" data-group="'.$grpid.'"
                            title="'.get_string('dynamogroupetypefan', 'mod_dynamo').' ('.$max.')"></i>'
                      .'<i class="fas fa-heart"     data-id="'.$grpid.'" data-group="'.$grpid.'"
                            title="'.get_string('dynamogroupetypefan', 'mod_dynamo').' ('.$max.')"></i>'
                      .'<i class="fas fa-heart"     data-id="'.$grpid.'" data-group="'.$grpid.'"
                            title="'.get_string('dynamogroupetypefan', 'mod_dynamo').' ('.$max.')"></i></div>';
            break;
        case 2:
            return ' '.'<div style="float:left;color:#006DCC;">
                        <i class="fas fa-heart" data-id="'.$grpid.'" data-group="'.$grpid.'"
                            title="'.get_string('dynamogroupetyperas', 'mod_dynamo').' ('.$max.')"></i>'
                      .'<i class="fas fa-heart"     data-id="'.$grpid.'" data-group="'.$grpid.'"
                            title="'.get_string('dynamogroupetyperas', 'mod_dynamo').' ('.$max.')"></i></div>';
            break;
        case 3:
            return ' '.'<div style="float:left;color:red;">
                        <i class="fas fa-heart-broken" data-id="'.$grpid.'" data-group="'.$grpid.'"
                            title="'.get_string('dynamogroupetypeclustering', 'mod_dynamo').' ('.$max.')"></i></div>';
            break;
        case 4:
            return ' '.'<div style="float:left;color:black;">
                        <i class="fas fa-heart-broken" data-id="'.$grpd.'" data-group="'.$grpid.'"
                            title="'.get_string('dynamogroupetypeclique', 'mod_dynamo').' ('.$max.')"></i>'
                      .'<i class="fas fa-heart-broken"         data-id="'.$grpd.'"  data-group="'.$grpid.'"
                            title="'.get_string('dynamogroupetypeclique', 'mod_dynamo').' ('.$max.')"></i></div>';
            break;
        case 5:
            return ' '.'<div style="float:left;color:orange;">
                        <i class="fas fa-heart" data-id="'.$grpid.'" data-group="'.$grpid.'"
                            title="'.get_string('dynamogroupetypeheterogene', 'mod_dynamo').' ('.$max.')"></i></div>';
            break;
        case 6:
            return ' '.'<div style="float:left;color:gold;">
                        <i class="fas fa-exclamation-triangle" data-id="'.$grpid.'" data-group="'.$grpid.'"
                            title="'.get_string('dynamogroupetypeghost', 'mod_dynamo').' ('.$max.')"></i></div>';
            break;
    }
    return '';
}

/**
 * Give the group cohesion type
 *
 * @param int $type the type of the group
 * @param int $grpid id of the group
 *
 * @return string...
 */
function dynamo_get_group_type_txt($type) {
    switch($type) {
        case 1:
            return get_string('dynamogroupetypefan', 'mod_dynamo');
            break;
        case 2:
            return get_string('dynamogroupetyperas', 'mod_dynamo');
            break;
        case 3:
            return get_string('dynamogroupetypeclustering', 'mod_dynamo');
            break;
        case 4:
            return get_string('dynamogroupetypeclique', 'mod_dynamo');
            break;
        case 5:
            return get_string('dynamogroupetypeheterogene', 'mod_dynamo');
            break;
        case 6:
            return get_string('dynamogroupetypeghost', 'mod_dynamo');
            break;
    }
    return '';
}

/**
 * Give the html(specific icon with a specific color) that represent the climate inside the group
 * it's base on a simple computing from the cohesion, implication and self confidence
 * green = 0, orange = 1, red = 2, black = 3
 * it's added, divided by the number of student and divide by two. It give a value from 0 to 4.5 and rounded
 * to get the climate 0 sun - 1 cloud sun - 2  cloud sun with rain - 3 dark cloud with heavy rain - 4 bold
 *
 * @param object dynamo  $dynamo
 * @param object of user $grpusrs
 * @param integer $notperfect value is use to compute the climate. It's initialize with the cohesion value
 *
 * @return html font awsome ico... from full sun to thunderbold (5 levels)
 */
function dynamo_get_group_climat($dynamo, $grpusrs, $notperfect) {
    $nbuser = 0;

    $aweight = ['#006DCC' => 0, 'orange' => 1, 'red' => 2, 'black' => 3];
    $aicon = ['fa-sun', 'fa-cloud-sun', 'fa-cloud-sun-rain ', 'fa-cloud-showers-heavy' , 'fa-bolt'];
    $aicolor = ['ca-sun', 'ca-cloud-sun', 'ca-cloud-sun-rain ', 'ca-cloud-showers-heavy' , 'ca-bolt'];

    foreach ($grpusrs as $grpusr) {
        $nbuser++;

        // Implication.
        $niwf = dynamo_get_niwf($dynamo, $grpusrs, $grpusr->id);
        $color = dynamo_get_color_niwf($niwf[0]);
        $notperfect += $aweight[$color];
        // Confidence.
        $conf = dynamo_get_conf($dynamo, $grpusrs, $grpusr->id);
        $color = dynamo_get_color_conf($conf);
        if ($color == 'green') {
            $color = '#006DCC';
        }
        $notperfect += $aweight[$color];
    }

    $idico = round($notperfect / $nbuser / 2, 0, PHP_ROUND_HALF_DOWN);
    $climat = '<i class="fas '.$aicon[$idico].' '.$aicolor[$idico].'"></i>';

    return [$climat, $idico];
}

/**
 * return an initialized to zero Dynamo object
 *
 * @return an initialized to zero Dynamo object
 */
function dynamo_to_zero() {
    $dynamoeval = new stdClass();
    $dynamoeval->crit1 = 0;
    $dynamoeval->crit2 = 0;
    $dynamoeval->crit3 = 0;
    $dynamoeval->crit4 = 0;
    $dynamoeval->crit5 = 0;
    $dynamoeval->crit6 = 0;

    return $dynamoeval;
}


/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all posts from the specified forum
 * and clean up any related data.
 *
 * @global object
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function dynamo_reset_userdata($data) {
    global $DB;

    if (empty($data)) {
        return;
    }

    if (!isset($data->courseid)) {
        return;
    }

    $sql = "SELECT id
              FROM {dynamo} t1
             WHERE course = :param1
           ";
    $params = array('param1' => $data->courseid);
    $responses = $DB->get_records_sql($sql, $params);

    foreach ($responses as &$res) {
        $DB->delete_records('dynamo_eval', array('builder' => $res->id));
    }
}