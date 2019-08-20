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
 * This file contains the definition for the library class for author submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package assignsubmission_author
 * @copyright 2013 Rene Roepke
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace assign_feedback_author;

defined('MOODLE_INTERNAL') || die();

use stdClass;

class feedback_controller {

    private $assignment;

    public function __construct($assignment) {
        $this->assignment = $assignment;
    }

    /**
     * Set author feedback record for user
     *
     * @param int $userid
     * @param string $coauthors
     * @param int $mode
     * @param stdClass $grade
     * @throws \dml_exception
     */
    public function set_author_feedback($userid, $coauthors, $mode, $grade) {
        global $DB;
        $assigngrade = $DB->get_record('assign_grades', array(
                'assignment' => $grade->assignment,
                'userid' => $userid
        ));
        $authorfeedback = $this->get_author_feedback($grade->assignment, $assigngrade->id);
        if ($authorfeedback) {
            $authorfeedback->mode = $mode;
            $authorfeedback->coauthors = $coauthors;
            $DB->update_record('assignfeedback_author', $authorfeedback);
        } else {
            $authorfeedback = new stdClass();
            $authorfeedback->assignment = $grade->assignment;
            $authorfeedback->grade = $assigngrade->id;
            $authorfeedback->mode = $mode;
            $authorfeedback->coauthors = $coauthors;
            $DB->insert_record('assignfeedback_author', $authorfeedback);
        }
    }

    /**
     * Get the author feedback record of a grade for an assignment
     *
     * @param int $assignment
     * @param int $grade
     * @return <stdClass or false>
     * @throws \dml_exception
     */
    public function get_author_feedback($assignment, $grade) {
        global $DB;
        return $DB->get_record('assignfeedback_author', array(
                'assignment' => $assignment,
                'grade' => $grade
        ));
    }

    /**
     * Set author feedback records for all coauthors
     *
     * @param int[] $coauthors
     * @param int $mode
     * @param stdClass $grade
     * @throws \dml_exception
     */
    public function set_author_feedback_for_coauthors($coauthors, $mode, $grade) {
        foreach (array_values($coauthors) as $coauthor) {
            $userarr = array(
                    $coauthor
            );
            $users = array_diff($coauthors, $userarr);
            $this->set_author_feedback($coauthor, implode(',', $users), $mode, $grade);
        }
    }

    /**
     * Set the user flags for the grading workflow
     *
     * @param int[] $coauthors
     * @param string $workflowstate
     */
    public function set_user_flag($coauthors, $workflowstate, $allocatedmarker) {
        foreach ($coauthors as $coauthor) {
            $flags = $this->assignment->get_user_flags($coauthor, true);
            $flags->workflowstate = $workflowstate;
            $flags->allocatedmarker = $allocatedmarker;
            $this->assignment->update_user_flags($flags);
        }
    }

    /**
     * Set comment feedback records for all coauthors
     *
     * @param int[] $coauthors
     * @param stdClass $data
     * @param $gradeuserid
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function set_comments_feedback_for_coauthors($coauthors, $data, $gradeuserid) {
        global $DB;
        if (isset($data->assignfeedbackcomments_editor)) {
            $assignment = $this->assignment->get_instance()->id;
            $text = $data->assignfeedbackcomments_editor['text'];
            $format = $data->assignfeedbackcomments_editor['format'];
            foreach ($coauthors as $coauthor) {
                assign_update_grades($this->assignment->get_instance(), $coauthor);
                $grade = $DB->get_record('assign_grades', array(
                        'assignment' => $assignment,
                        'userid' => $coauthor
                ));
                $commentsfeedback = $DB->get_record('assignfeedback_' . ASSIGNFEEDBACK_COMMENTS, array(
                        'assignment' => $assignment,
                        'grade' => $grade->id
                ));
                if ($commentsfeedback) {
                    $commentsfeedback->commenttext = $text;
                    $commentsfeedback->commentformat = $format;
                    $DB->update_record('assignfeedback_' . ASSIGNFEEDBACK_COMMENTS, $commentsfeedback);
                } else {
                    $commentsfeedback = new stdClass();
                    $commentsfeedback->assignment = $assignment;
                    $commentsfeedback->grade = $grade->id;
                    $commentsfeedback->commenttext = $text;
                    $commentsfeedback->commentformat = $format;
                    $DB->insert_record('assignfeedback_' . ASSIGNFEEDBACK_COMMENTS, $commentsfeedback);
                }
                $gradeitem = $DB->get_record('grade_items', array(
                        'iteminstance' => $assignment,
                        'itemmodule' => 'assign'
                ));
                if ($DB->count_records('grade_grades', array(
                                'itemid' => $gradeitem->id,
                                'userid' => $coauthor
                        )) == 0
                ) {
                    $record = $DB->get_record('grade_grades', array(
                            'itemid' => $gradeitem->id,
                            'userid' => $gradeuserid
                    ));
                    $record->id = null;
                    $record->userid = $coauthor;
                    $record->feedback = $text;
                    $record->feedbackformat = $format;
                    $DB->insert_record('grade_grades', $record);
                } else {
                    $entry = $DB->get_record('grade_grades', array(
                            'itemid' => $gradeitem->id,
                            'userid' => $coauthor
                    ));
                    $entry->feedback = $text;
                    $entry->feedbackformat = $format;
                    $DB->update_record('grade_grades', $entry);
                }
            }
        }
    }

    /**
     * Get the author submission record of a submission for an assignment
     *
     * @param int $assignment
     * @param int $submission
     * @return <stdClass or false>
     * @throws \dml_exception
     */
    public function get_author_submission($assignment, $submission) {
        global $DB;
        return $DB->get_record('assignsubmission_author', array(
                'assignment' => $assignment,
                'submission' => $submission
        ));
    }
}