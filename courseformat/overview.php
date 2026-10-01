<?php
namespace mod_dynamo\courseformat;

use core_courseformat\activityoverviewbase;
use core_courseformat\local\overview\overviewitem;

class overview extends activityoverviewbase {

    /**
     * Adds custom items to the overview table.
     * Use this method to display data specific to Dynamo,
     * such as the student response rate.
     */
    #[\Override]
    public function get_extra_overview_items(): array {
        return [
            'submitted' => $this->get_extra_submitted_overview(),
            // You can add other indicators here.
        ];
    }

    /**
     * Defines the "Submitted" item for the overview.
     *
     * - Student (mod/dynamo:respond): displays their own progress, in
     *   THEIR group, retrieved directly (not via the session's "active"
     *   group, which does not exist on the /course/overview.php page).
     * - Teacher (mod/dynamo:view): displays an aggregated summary across ALL
     *   groups concerned by the activity (and not a single arbitrary group).
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

        // Retrieve the Dynamo instance.
        $dynamo = $DB->get_record('dynamo', ['id' => $this->cm->instance], '*', MUST_EXIST);
        // ------------------------------------------------------------------
        // TEACHER VIEW: aggregated summary across all groups of the activity.
        // ------------------------------------------------------------------
        if ($cancreate) {
            $groups = $this->get_relevant_groups($dynamo, 0);

            if (empty($groups)) {
                return null; // No group => no evaluation possible.
            }

            $groupingid     = $dynamo->groupingid;
            $builderid      = $dynamo->id;
            $totalusers     = 0;
            $completedusers = 0;

            $stats = dynamo_get_grouping_users_stats($groupingid, $builderid);
            $totalusers= $stats->total;
            $completedusers = $stats->done;

            return new overviewitem(
                name: get_string('dynamoresponded', 'mod_dynamo'),
                value: $completedusers.'/'.$totalusers,
                content: $content
            );
        }

        // ------------------------------------------------------------------
        // STUDENT VIEW: their own progress, in their own group.
        // ------------------------------------------------------------------
        if ($canrespond) {
            global $DB, $USER;

            $groupingid = $dynamo->groupingid;
            $builderid = $dynamo->id;
            $userid = $USER->id;

            // Saving on the student side is only possible when ALL
            // evaluations are filled in (all or nothing): it is therefore enough
            // to check that at least one record exists to know that it is complete.
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

            $group = dynamo_get_group_from_user($groupingid, $userid);
            $stat = $this->get_group_respondent_stats($builderid, $group->id);


            return new overviewitem(
                name: get_string('dynamoresponded', 'mod_dynamo'),
                value: $completed,
                content: $content.'('.$stat['done'].'/'.$stat['total'].')'
            );
        }


        return null;
    }

    /**
     * Determines the group(s) concerned by this activity, without
     * depending on the session's "active group" (unavailable on the
     * course overview page).
     *
     * - If the Dynamo instance has a fixed configured group (groupid), it
     *   returns only that one.
     * - Otherwise, it returns the course groups (filtered by the activity's
     *   possible grouping); if $userid is provided, only the groups of
     *   that user.
     *
     * @param \stdClass $dynamo Dynamo instance.
     * @param int $userid 0 for all groups, otherwise the groups of that user.
     * @return array Array of group objects (id, name, ...), potentially empty.
     */
    private function get_relevant_groups(\stdClass $dynamo, int $userid): array {
        global $DB;

        if (!empty($dynamo->groupid)) {
            $group = $DB->get_record('groups', ['id' => $dynamo->groupid]);
            if (!$group) {
                return [];
            }
            // If searching for a specific user, check that they are a member.
            if ($userid && !groups_is_member($group->id, $userid)) {
                return [];
            }
            return [$group->id => $group];
        }

        return groups_get_all_groups($this->cm->course, $userid, $this->cm->groupingid);
    }

    /**
     * Returns the end date ("until" constraint) as a deadline for the overview.
     *
     * @return overviewitem|null
     */
    #[\Override]
    public function get_due_date_overview(): ?overviewitem {
        // $this->cm (cm_info) already exposes the restrictions JSON, no need to re-query the DB.
        $availabilityjson = $this->cm->availability;
 
        if (empty($availabilityjson)) {
            return null; // No restriction => no end date.
        }
 
        $availability = json_decode($availabilityjson, true);
        if (!is_array($availability)) {
            return null;
        }
 
        // Recursive search (the tree may combine several conditions via AND/OR),
        // for a "date" type condition with the "until" direction.
        //
        // WARNING: the 'd' field contains an operator, NOT the word "until":
        //   '>' = available from (from)
        //   '<' = available until (until)
        $enddate = $this->find_date_until_timestamp($availability);
 
        if (empty($enddate)) {
            return null;
        }
 
        // If the end time corresponds to the very end of the day (23:59:59, as the
        // default Moodle date picker does), we display just the date, with
        // the label "until the end of [date]" rather than the date AND time.
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
     * Recursively traverses an access restriction tree (structure JSON
     * decoded into an array) looking for a "date" type condition with
     * the "until" direction (d === '<').
     *
     * @param array $node Current node of the tree.
     * @return int|null End timestamp found, or null if absent.
     */
    private function find_date_until_timestamp(array $node): ?int {
        if (($node['type'] ?? null) === 'date' && ($node['d'] ?? null) === '<') {
            return isset($node['t']) ? (int) $node['t'] : null;
        }
 
        if (isset($node['c']) && is_array($node['c'])) {
            foreach ($node['c'] as $child) {
                if (is_array($child)) {
                    $found = $this->find_date_until_timestamp($child);
                    if ($found !== null) {
                        return $found;
                    }
                }
            }
        }
 
        return null;
    }


    /**
     * Count the number of students who answered in the group.
     */
    private function get_group_respondent_stats(int $builderid, int $groupid): array {
        global $DB;

        if (empty($groupid)) {
            return ['total' => 0, 'done' => 0];
        }

        $sql = "SELECT
                   (SELECT COUNT(DISTINCT u.id)
                      FROM {groups_members} gm
                      JOIN {user} u ON u.id = gm.userid AND u.deleted = 0
                     WHERE gm.groupid = :groupid) AS total,
                   (SELECT COUNT(DISTINCT d.evalbyid)
                      FROM {dynamo_eval} d
                      JOIN {groups_members} gm ON gm.userid = d.evalbyid
                     WHERE d.builder = :builderid
                       AND gm.groupid = :groupid2) AS done";

        $result = $DB->get_record_sql($sql, [
            'builderid' => $builderid,
            'groupid'   => $groupid,
            'groupid2'   => $groupid,
        ]);

        return [
            'total' => (int) $result->total,
            'done'  => (int) $result->done,
        ];
    }
    /**
     * Defines the main action for the activity (for example, a link to "View").
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