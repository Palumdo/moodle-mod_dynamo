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
 * Internal library of functions for dynamo module.
 *
 * All the dynamo specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_dynamo
 * @copyright 2019 Palumbo Dominique
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This creates new calendar events given close by $dynamo.
 *
 * @param stdClass $dynamo
 * @return void
 */
function dynamo_set_events($dynamo) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/calendar/lib.php');

    // Get CMID if not sent as part of $dynamo.
    if (!isset($dynamo->coursemodule)) {
        $cm = get_coursemodule_from_instance('dynamo', $dynamo->id, $dynamo->course);
        $dynamo->coursemodule = $cm->id;
    }

    // Dynamo start calendar events.
    $event = new stdClass();
    $event->eventtype = DYNAMO_EVENT_TYPE_OPEN;
    // The dynamo_EVENT_TYPE_OPEN event should only be an action event if no close time is specified.
    $event->type = empty($dynamo->close) ? CALENDAR_EVENT_TYPE_ACTION : CALENDAR_EVENT_TYPE_STANDARD;
    if ($event->id = $DB->get_field('event', 'id',
            array('modulename' => 'dynamo', 'instance' => $dynamo->id, 'eventtype' => $event->eventtype))) {
        if ((!empty($dynamo->open)) && ($dynamo->open > 0)) {
            // Calendar event exists so update it.
            $event->name         = get_string('calendarstart', 'dynamo', $dynamo->name);
            $event->description  = format_module_intro('dynamo', $dynamo, $dynamo->coursemodule);
            $event->timestart    = $dynamo->open;
            $event->timesort     = $dynamo->open;
            $event->visible      = instance_is_visible('dynamo', $dynamo);
            $event->timeduration = 0;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event, false);
        } else {
            // Calendar event is on longer needed.
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->delete();
        }
    } else {
        // Event doesn't exist so create one.
        if ((!empty($dynamo->open)) && ($dynamo->open > 0)) {
            $event->name         = get_string('calendarstart', 'dynamo', $dynamo->name);
            $event->description  = format_module_intro('dynamo', $dynamo, $dynamo->coursemodule);
            $event->courseid     = $dynamo->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'dynamo';
            $event->instance     = $dynamo->id;
            $event->timestart    = $dynamo->open;
            $event->timesort     = $dynamo->open;
            $event->visible      = instance_is_visible('dynamo', $dynamo);
            $event->timeduration = 0;
            calendar_event::create($event, false);
        }
    }

    // Dynamo end calendar events.
    $event = new stdClass();
    $event->type = CALENDAR_EVENT_TYPE_ACTION;
    $event->eventtype = DYNAMO_EVENT_TYPE_CLOSE;
    if ($event->id = $DB->get_field('event', 'id',
            array('modulename' => 'dynamo', 'instance' => $dynamo->id, 'eventtype' => $event->eventtype))) {
        if ((!empty($dynamo->close)) && ($dynamo->close > 0)) {
            // Calendar event exists so update it.
            $event->name         = get_string('calendarend', 'dynamo', $dynamo->name);
            $event->description  = format_module_intro('dynamo', $dynamo, $dynamo->coursemodule);
            $event->timestart    = $dynamo->close;
            $event->timesort     = $dynamo->close;
            $event->visible      = instance_is_visible('dynamo', $dynamo);
            $event->timeduration = 0;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event, false);
        } else {
            // Calendar event is on longer needed.
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->delete();
        }
    } else {
        // Event doesn't exist so create one.
        if ((!empty($dynamo->close)) && ($dynamo->close > 0)) {
            $event->name         = get_string('calendarend', 'dynamo', $dynamo->name);
            $event->description  = format_module_intro('dynamo', $dynamo, $dynamo->coursemodule);
            $event->courseid     = $dynamo->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'dynamo';
            $event->instance     = $dynamo->id;
            $event->timestart    = $dynamo->close;
            $event->timesort     = $dynamo->close;
            $event->visible      = instance_is_visible('dynamo', $dynamo);
            $event->timeduration = 0;
            calendar_event::create($event, false);
        }
    }
}
