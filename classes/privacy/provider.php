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
 * Privacy class for requesting user data.
 *
 * @package    assignfeedback_author
 * @copyright  2019 Benedikt Schneider (@Nullmann)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignfeedback_author\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\userlist;
use \mod_assign\privacy\assign_plugin_request_data;
use mod_assign\privacy\useridlist;
use \core_privacy\local\request\writer;

/**
 * Privacy class for requesting user data.
 *
 * @package    assignsubmission_author
 * @copyright  2019 Benedikt Schneider (@Nullmann)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \mod_assign\privacy\assignfeedback_provider,
    \mod_assign\privacy\assignfeedback_user_provider {

    // Legacy support for Moodle versions 3.3 and older.
    use \core_privacy\local\legacy_polyfill;

    /**
     * Return meta data about this plugin.
     *
     * @param  collection $collection A list of information to add to.
     * @return collection Return the collection after adding to it.
     */
    public static function _get_metadata(collection $collection) {
        $collection->add_database_table(
            'assignsubmission_author',
            [
                'id' => 'privacy:assignfeedback_author:id',
                'assignment' => 'privacy:assignfeedback_author:assignment',
                'grade' => 'privacy:assignfeedback_author:grade',
                'mode' => 'privacy:assignfeedback_author:mode',
                'coauthors' => 'privacy:assignfeedback_author:coauthors',
            ],
            'privacy:metadata:assignfeedback_author'
            );

        return $collection;
    }

    /**
     * @see \mod_assign\privacy\assignfeedback_provider::get_student_user_ids()
     * @param useridlist $useridlist
     */
    public static function get_student_user_ids(useridlist $useridlist) {
        // There is no need to implement this, because there is an entry in "assign_grades" for this to work.
    }

    /**
     * @see \mod_assign\privacy\assignfeedback_provider::get_context_for_userid_within_feedback()
     * @param int $userid
     * @param contextlist $contextlist
     */
    public static function get_context_for_userid_within_feedback(int $userid, contextlist $contextlist) {
        // This is already fetched from mod_assign.
    }

    /**
     * @see \mod_assign\privacy\assignfeedback_provider::export_feedback_user_data()
     * @param assign_plugin_request_data $exportdata
     * @return NULL
     */
    public static function export_feedback_user_data(assign_plugin_request_data $exportdata) {
        global $DB;

        if ($exportdata->get_user() != null) {
            return null;
        }

        $context = $exportdata->get_context();
        $grade = $exportdata->get_pluginobject();
        $assignmentid = $exportdata->get_assignid();

        $params = [
            'gradeid' => $grade->id,
            'assignid' => $assignmentid
        ];
        $sql = "SELECT * FROM {assignfeedback_author}
                  WHERE grade = :gradeid
                  AND assignment = :assignid";
        $result = $DB->get_record_sql($sql, $params);

        $path = array_merge($exportdata->get_subcontext(), [get_string('pluginname', 'assignfeedback_author')]);

        writer::with_context($context)->export_data($path, (object)$result);
    }

    /**
     * @see \mod_assign\privacy\assignfeedback_provider::delete_feedback_for_context()
     * @param assign_plugin_request_data $requestdata
     */
    public static function delete_feedback_for_context(assign_plugin_request_data $requestdata) {
        global $DB;

        $assignid = $requestdata->get_assignid();
        $DB->delete_records('assignfeedback_author', array ('assignment' => $assignid));
    }

    /**
     * @see \mod_assign\privacy\assignfeedback_provider::delete_feedback_for_grade()
     * @param assign_plugin_request_data $requestdata
     */
    public static function delete_feedback_for_grade(assign_plugin_request_data $requestdata) {
        global $DB;

        $gradeid = $requestdata->get_pluginobject()->id;
        $DB->delete_records('assignfeedback_author', array ('grade' => $gradeid));

    }

    /**
     * @see \mod_assign\privacy\assignfeedback_user_provider::delete_feedback_for_grades()
     * @param assign_plugin_request_data $deletedata
     */
    public static function delete_feedback_for_grades(assign_plugin_request_data $deletedata) {
        global $DB;
        if (empty($deletedata->get_gradeids())) {
            return;
        }

        $gradeids = $deletedata->get_gradeids();
        list($sql, $params) = $DB->get_in_or_equal($gradeids, SQL_PARAMS_NAMED);

        // Bulk delete for faster execution, instead of simple foreach.
        $params['assignment'] = $deletedata->get_assignid();
        $DB->delete_records_select('assignfeedback_author', "assignment = :assignment AND grade $sql", $params);
    }

    /**
     * @see \mod_assign\privacy\assignfeedback_user_provider::get_userids_from_context()
     * @param userlist $userlist
     */
    public static function get_userids_from_context(userlist $userlist) {
        // There is no need to implement this, because there is an entry in "assign_grades" for this to work.
    }

}
