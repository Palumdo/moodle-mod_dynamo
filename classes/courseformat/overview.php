<?php
namespace mod_dynamo\courseformat;

use core_courseformat\activityoverviewbase;
use core_courseformat\local\overview\overviewitem;

class overview extends activityoverviewbase {

    /**
     * Adds custom items to the course overview table.
     * Use this method to display Dynamo-specific data,
     * such as the students' response rate.
     */
    #[\Override]
    public function get_extra_overview_items(): array {
        return [
            'submitted' => $this->get_extra_submitted_overview(),
            // You can add other indicators here.
        ];
    }

    /**
     * Sets up the "Submitted" overview item.
     *
     * - Student (mod/dynamo:respond): shows their own progress, in
     *   THEIR group, found directly (not via the session's "active group",
     *   which does not exist on the /course/overview.php page).
     * - Teacher (mod/dynamo:view): shows an aggregate summary over ALL the
     *   groups concerned by the activity (not a single arbitrary group).
     *
     * @return overviewitem|null
     */
    private function get_extra_submitted_overview(): ?overviewitem {
        global $DB, $USER;

        // The user must be able to respond (student) or view (teacher).
        $canrespond = has_capability('mod/dynamo:respond', $this->context);
        $cancreate = has_capability('mod/dynamo:create', $this->context);

        if (!$canrespond && !$cancreate) {
            return null;
        }

        // Fetch the Dynamo instance.
        $dynamo = $DB->get_record('dynamo', ['id' => $this->cm->instance], '*', MUST_EXIST);
        // ------------------------------------------------------------------
        // TEACHER VIEW: aggregate summary over all the activity's groups.
        // ------------------------------------------------------------------
        if ($cancreate) {
            // Cheap emptiness check: if no student has started answering,
            // there is nothing to report. This avoids loading every group
            // (and possibly every user) from the database.
            if (!$DB->record_exists('dynamo_user', ['builder' => $dynamo->id])) {
                return null; // No response started => nothing to report.
            }

            $stats = dynamo_get_grouping_users_stats($dynamo->groupingid, $dynamo->id);
            $totalusers = $stats->total;
            $completedusers = $stats->done;

            $content = $completedusers.'/'.$totalusers;

            return new overviewitem(
                name: get_string('dynamoresponded', 'mod_dynamo'),
                value: $content,
                content: $content
            );
        }

        // ------------------------------------------------------------------
        // STUDENT VIEW: their own progress, in their own group.
        // ------------------------------------------------------------------
        if ($canrespond) {
            $builderid = $dynamo->id;
            $userid    = $USER->id;

            // Saving on the student side is only possible when ALL evaluations
            // are filled in (all-or-nothing): so it is enough to check that
            // at least one record exists to know it is complete.
            $params = [
                'builder' => $builderid,
                'userid'  => $userid,
            ];

            $completed = $DB->record_exists_select(
                'dynamo_eval',
                "builder = :builder AND evalbyid = :userid",
                $params
            );

            $content = $completed
                ? get_string('dynamocompleted', 'mod_dynamo')
                : get_string('dynamonotcompleted', 'mod_dynamo');

            return new overviewitem(
                name: get_string('dynamoresponded', 'mod_dynamo'),
                value: $completed,
                content: $content
            );
        }


        return null;
    }

    /**
     * Returns the end date (the "until" constraint) as the due date for the overview.
     *
     * @return overviewitem|null
     */
    #[\Override]
    public function get_due_date_overview(): ?overviewitem {
        // $this->cm (cm_info) already exposes the availability restrictions JSON,
        // no need to query the DB again.
        $availabilityjson = $this->cm->availability;

        if (empty($availabilityjson)) {
            return null; // No restriction => no end date.
        }

        $availability = json_decode($availabilityjson, true);
        if (!is_array($availability)) {
            return null;
        }

        // Recursive search (the tree can combine several conditions via AND/OR)
        // for a condition of type "date" with the "until" direction.
        //
        // NOTE: the 'd' field contains an operator, NOT the word "until":
        //   '>' = available from
        //   '<' = available until
        $enddate = $this->find_date_until_timestamp($availability);

        if (empty($enddate)) {
            return null;
        }

        // If the end time is at the very end of the day (23:59, as Moodle's
        // default date picker does), show the date only, with the label
        // "until the end of [date]" rather than the date AND the time.
        $time = usergetdate($enddate);
        $isendofday = ($time['hours'] == 23 && $time['minutes'] == 59);

        if ($isendofday) {
            $content = get_string('dynamoavailabilityuntilend', 'mod_dynamo',
                userdate($enddate, get_string('strftimedatefullshort', 'langconfig')));
        } else {
            $content = get_string('dynamoavailabilityuntil', 'mod_dynamo', userdate($enddate));
        }

        return new overviewitem(
            name: get_string('dynamoavailabilityuntil_label', 'mod_dynamo'),
            value: $enddate,
            content: $content
        );
    }

    /**
     * Recursively walks the access restrictions tree (decoded JSON structure)
     * looking for date conditions with the "until" direction (d === '<').
     *
     * All "until" dates found in the tree are collected and the earliest one
     * is returned, so that an OR combination like "until D1 OR until D2"
     * yields the first (most restrictive) end date, not just the first
     * match in walk order.
     *
     * @param array $node Current node of the tree.
     * @return int|null Earliest end timestamp found, or null if none.
     */
    private function find_date_until_timestamp(array $node): ?int {
        $dates = [];

        if (($node['type'] ?? null) === 'date' && ($node['d'] ?? null) === '<') {
            if (isset($node['t'])) {
                $dates[] = (int) $node['t'];
            }
        }

        if (isset($node['c']) && is_array($node['c'])) {
            foreach ($node['c'] as $child) {
                if (is_array($child)) {
                    $dates = array_merge($dates, $this->find_date_until_timestamp($child));
                }
            }
        }

        if ($dates === []) {
            return null;
        }

        sort($dates);
        return (int) $dates[0];
    }


    /**
     * Sets up the main action for the activity (for example, a link to "View").
     */
    #[\Override]
    public function get_actions_overview(): ?overviewitem {
        // Logic to generate the link to the Dynamo activity.
        // Return null if no action is available.
        return new overviewitem(
            name: get_string('view'),
            value: null,
            content: \html_writer::link(
                new \moodle_url('/mod/dynamo/view.php', ['id' => $this->cm->id]),
                get_string('view')
            )
        );
    }
}
