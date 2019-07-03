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
 * The main mod_dynamo configuration form.
 * For each criteria used for the peer evaluation the teacher can add additional info on it.
 * They've default text for the tooltips and here the teacher can add more info.
 * He can also add is own sixth criteria.
 * Evaluation is based on (Participation Responsability Scientific Expertise Technical Expertise General Attitude).
 *
 * @package    mod_dynamo
 * @copyright  2019 UCLouvain
 * @author     Dominique Palumbo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_dynamo
 * @copyright  2019 UCLouvain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_dynamo_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $COURSE, $PAGE;

        $PAGE->requires->jquery();
        $PAGE->requires->js('/mod/dynamo/js/mod.js');

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('dynamoname', 'mod_dynamo'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'dynamoname', 'mod_dynamo');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }
        // Adding the rest of mod_dynamo settings, spreading all them into this fieldset.
        $mform->addElement('hidden', 'dynamo_auto', 1);
        $mform->addElement('advcheckbox', 'dynamo_group_eval', get_string('dynamoautotitle', 'mod_dynamo'),
            get_string('dynamogroupeval', 'mod_dynamo'), array('group' => 1), array(0, 1));
        $mform->addElement('static', 'label',  get_string('dynamochoice', 'mod_dynamo'));
        $agrouping = groups_get_all_groupings($COURSE->id);

        // List of grouping select one for pairs evaluation.
        foreach ($agrouping as $grouping) {
            if ($grouping->name != '') {
                $options[$grouping->id] = $grouping->name;
            }
        }
        $mform->addElement('select', 'dynamo_grouping_id', get_string('dynamoheadgrouping', 'mod_dynamo'), $options);

        // Additional information in the tooltips for the students. These text is added to the defaul it doesn't replace it.
        $mform->addElement('header', 'dynamofieldset', get_string('dynamocrit1', 'mod_dynamo').' : '
            .get_string('dynamoparticipation', 'mod_dynamo'));
        $mform->addElement('static', 'label', get_string('description'),
            get_string('dynamocritparticipationdefault', 'mod_dynamo'));
        $mform->addElement('text', 'dynamo_participation',
            get_string('dynamocritparticipation', 'mod_dynamo'), array('size' => '80', 'maxlength' => '200'));
        $mform->addElement('header', 'dynamofieldset', get_string('dynamocrit2', 'mod_dynamo').' : '
            .get_string('dynamoresponsabilite', 'mod_dynamo'));
        $mform->addElement('static', 'label', get_string('description'),
            get_string('dynamocritresponsabilitedefault', 'mod_dynamo'));
        $mform->addElement('text', 'dynamo_responsability',
            get_string('dynamocritresponsabilite', 'mod_dynamo'), array('size' => '80', 'maxlength' => '200'));
        $mform->addElement('header', 'dynamofieldset', 
            get_string('dynamocrit3', 'mod_dynamo').' : '.get_string('dynamoscientifique', 'mod_dynamo'));
        $mform->addElement('static', 'label', get_string('description'), get_string('dynamocritscientifiquedefault', 'mod_dynamo'));
        $mform->addElement('text', 'dynamo_science',
            get_string('dynamocritscientifique', 'mod_dynamo'), array('size' => '80', 'maxlength' => '200'));
        $mform->addElement('header', 'dynamofieldset', get_string('dynamocrit4', 'mod_dynamo').' : '
            .get_string('dynamotechnique', 'mod_dynamo'));
        $mform->addElement('static', 'label', get_string('description'), get_string('dynamocrittechniquedefault', 'mod_dynamo'));
        $mform->addElement('text', 'dynamo_technical', 
            get_string('dynamocrittechnique', 'mod_dynamo'), array('size' => '80', 'maxlength' => '200'));
        $mform->addElement('header', 'dynamofieldset', 
            get_string('dynamocrit5', 'mod_dynamo').' : '.get_string('dynamoattitude', 'mod_dynamo'));
        $mform->addElement('static', 'label', get_string('description'), get_string('dynamocritattitudedefault', 'mod_dynamo'));
        $mform->addElement('text', 'dynamo_attitude', 
            get_string('dynamocritattitude', 'mod_dynamo'), array('size' => '80', 'maxlength' => '200'));
        // The teacher can add a sixth critria but with no pedagogic influence on the 5 others.
        $mform->addElement('header', 'dynamofieldset', get_string('dynamocritoptname', 'mod_dynamo'));
        $mform->addElement('text', 'dynamo_optional_name', get_string('dynamocrit6', 'mod_dynamo'),
            array('size' => '25', 'maxlength' => '30'));
        $mform->setType('dynamo_optional_name', PARAM_TEXT);
        $mform->addElement('text', 'dynamo_optional', get_string('dynamocritoptnamedescr', 'mod_dynamo'),
            array('size' => '80', 'maxlength' => '200'));
        // Tooltips for the two comments asked to the students.
        $mform->addElement('header', 'dynamofieldset', get_string('dynamocommentcontr', 'mod_dynamo').' (1)');
        $mform->addElement('text', 'dynamo_comment1', get_string('dynamocommentcontr', 'mod_dynamo'),
            array('size' => '80', 'maxlength' => '200'));
        $mform->addElement('header', 'dynamofieldset', get_string('dynamocommentfonction', 'mod_dynamo').' (2)');
        $mform->addElement('text', 'dynamo_comment2', get_string('dynamocommentfonction', 'mod_dynamo'), 
            array('size' => '80', 'maxlength' => '200'));
        // Hidden tranlated text for javascript mod.js.
        $mform->addElement('hidden', 'dynamo_newtext', get_string('dynamoactivityview', 'mod_dynamo'));
        // Add standard elements.
        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();
        // Add standard buttons.
        $this->add_action_buttons();
    }
    // Load all the values from dynamo of the current activity.
    public function data_preprocessing(&$defaultvalues) {
        global $DB;

        $dynamo = $DB->get_record('dynamo', array('id' => $this->current->id), '*', IGNORE_MISSING);
        if ($dynamo != false) {
            $defaultvalues['dynamo_participation'] = $dynamo->crit1;
            $defaultvalues['dynamo_responsability'] = $dynamo->crit2;
            $defaultvalues['dynamo_science'] = $dynamo->crit3;
            $defaultvalues['dynamo_technical'] = $dynamo->crit4;
            $defaultvalues['dynamo_attitude'] = $dynamo->crit5;
            $defaultvalues['dynamo_optional_name'] = $dynamo->critoptname;
            $defaultvalues['dynamo_optional'] = $dynamo->critopt;
            $defaultvalues['dynamo_auto'] = $dynamo->autoeval;
            $defaultvalues['dynamo_group_eval'] = $dynamo->groupeval;
            $defaultvalues['dynamo_grouping_id'] = $dynamo->groupingid;
            $defaultvalues['dynamo_comment1'] = $dynamo->comment1;
            $defaultvalues['dynamo_comment2'] = $dynamo->comment2;
        } else {
            $defaultvalues['dynamo_auto'] = 1;
            $defaultvalues['dynamo_group_eval'] = 1;
        }
    }
}
