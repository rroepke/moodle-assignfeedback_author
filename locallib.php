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
 * This file contains the definition for the library class for author feedback plugin
 *
 *
 * @package     assignfeedback_author
 * @author      Rene Roepke
 * @author      Guido Roessling
 * @copyright   2013 Rene Roepke
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

define('ASSIGNFEEDBACK_COMMENTS', 'comments');

require_once($CFG->dirroot . '/mod/assign/feedback/author/classes/controllers/feedback_controller.php');
require_once($CFG->dirroot . '/mod/assign/feedback/author/classes/utilities.php');

use assign_feedback_author\utilities;
use assign_feedback_author\feedback_controller;

/**
 * Library class for author feedback plugin extending feedback plugin base class.
 *
 * @package assignfeedback_author
 * @copyright 2013 Rene Roepke
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_feedback_author extends assign_feedback_plugin {

    /**
     * Get the name of the author feedback plugin.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_name() {
        return get_string('author', 'assignfeedback_author');
    }

    /**
     * Get the default setting for author feedback plugin
     *
     * @param MoodleQuickForm $mform
     *            The form to add elements to
     * @return void
     * @throws coding_exception
     */
    public function get_settings(MoodleQuickForm $mform) {
        $defaultnotification = $this->get_config('notification');

        $name = get_string('notification', 'assignfeedback_author');
        $mform->addElement('checkbox', 'assignfeedbackauthor_notification', $name, '', 0);
        $mform->setDefault('assignfeedbackauthor_notification', $defaultnotification);
        $mform->addHelpButton('assignfeedbackauthor_notification', 'notification', 'assignfeedback_author');

        $mform->disabledIf('assignfeedbackauthor_notification', 'assignfeedback_author_enabled', 'notchecked');
    }

    /**
     * Save the settings for file submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        $checknotification = isset($data->assignfeedbackauthor_notification);
        $this->set_config('notification', $checknotification ? $data->assignfeedbackauthor_notification : 0);
        return true;
    }

    /**
     * Look up the plugin status 'enabled'
     *
     * @param string $name
     *            name of the plugin
     * @param string $subtype
     *            subtype of the plugin
     * @return boolean true if plugin is enabled
     * @throws coding_exception
     * @throws dml_exception
     */
    private function is_plugin_enabled($name, $subtype) {
        global $DB;
        $rec = $DB->get_record('assign_plugin_config', array(
            'assignment' => $this->assignment->get_instance()->id,
            'subtype' => $subtype,
            'plugin' => $name,
            'name' => 'enabled'
        ));
        if ($rec) {
            return $rec->value == 1;
        }
        return false;
    }

    /**
     * Get form elements for grading form.
     *
     * @param stdClass $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @param int $userid
     *            The userid we are currently grading
     * @return bool true if elements were added to the form
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {

        if (!$this->is_plugin_enabled('author', 'assignsubmission')) {
            $mform->addElement('static', '', '', get_string('submissionpluginmissing', 'assignfeedback_author'));
            return true;
        }

        $assignment = $this->assignment->get_instance();
        $feedbackcontroller = new feedback_controller($this->assignment);

        $submission = $this->get_submission($userid, $assignment->id);
        if ($submission) {
            $authorsubmission = $feedbackcontroller->get_author_submission($assignment->id, $submission->id);
            if ($authorsubmission) {
                $coauthors = $authorsubmission->author . ',' . $authorsubmission->authorlist;
                $coauthors = utilities::get_author_array($coauthors, true, $this->assignment);
                $userarr[$userid] = '';
                $coauthors = array_diff_key($coauthors, $userarr);
                $mform->addElement('checkbox', 'assignfeedbackauthor_feedbackforall', '',
                    get_string('feedbackforall', 'assignfeedback_author'), 1);
                $mform->addElement('static', '', '', implode(', ', $coauthors));
                $mform->addElement('static', '', '', '');
                $mform->addElement('checkbox', 'assignfeedbackauthor_feedbackforsel', '',
                    get_string('feedbackforsel', 'assignfeedback_author'), 1);

                $objs = array();
                foreach ($coauthors as $key => $value) {
                    $objs[$key] = &$mform->createElement('checkbox', 'assignfeedbackauthor_coauthors[' . $key . ']', '',
                        $value, null);
                    $mform->disabledIf('assignfeedbackauthor_coauthors[' . $key . ']',
                        'assignfeedbackauthor_feedbackforsel', 'notchecked');
                }
                $mform->addElement('group', '', get_string('coauthors', 'assignfeedback_author'), $objs, ' ', false);
                $mform->addElement('static', '', '', '');
                $mform->addElement('checkbox', 'assignfeedbackauthor_feedbackforno', '',
                    get_string('feedbackforno', 'assignfeedback_author'), 1);

                $mform->disabledIf('assignfeedbackauthor_feedbackforall',
                    'assignfeedbackauthor_feedbackforsel',
                    'checked');
                $mform->disabledIf('assignfeedbackauthor_feedbackforall',
                    'assignfeedbackauthor_feedbackforno',
                    'checked');
                $mform->disabledIf('assignfeedbackauthor_feedbackforsel',
                    'assignfeedbackauthor_feedbackforall',
                    'checked');
                $mform->disabledIf('assignfeedbackauthor_feedbackforsel',
                    'assignfeedbackauthor_feedbackforno',
                    'checked');
                $mform->disabledIf('assignfeedbackauthor_feedbackforno',
                    'assignfeedbackauthor_feedbackforall',
                    'checked');
                $mform->disabledIf('assignfeedbackauthor_feedbackforno',
                    'assignfeedbackauthor_feedbackforsel',
                    'checked');
            }
            if ($grade) {
                $authorfeedback = $feedbackcontroller->get_author_feedback($assignment->id, $grade->id);
                if ($authorfeedback) {
                    $coauthors = utilities::get_author_array($authorfeedback->coauthors, true, $this->assignment);
                    foreach ($coauthors as $key => $value) {
                        $mform->setDefault('assignfeedbackauthor_coauthors[' . $key . ']', 'checked');
                    }
                    switch ($authorfeedback->mode) {
                        case 0 :
                            $mform->setDefault('assignfeedbackauthor_feedbackforall', 'checked');
                            break;
                        case 1 :
                            $mform->setDefault('assignfeedbackauthor_feedbackforsel', 'checked');
                            break;
                        case 2 :
                            $mform->setDefault('assignfeedbackauthor_feedbackforno', 'checked');
                            break;
                        default :
                            $mform->setDefault('assignfeedbackauthor_feedbackforno', 'checked');
                            break;
                    }
                }
            } else {
                $mform->setDefault('assignfeedbackauthor_feedbackforno', 'checked');
            }
        }
        if (!$submission || !$authorsubmission) {
            return false;
        }
        return true;
    }

    /**
     * Save the author feedback.
     *
     * @param stdClass $grade
     * @param stdClass $data
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public function save(stdClass $grade, stdClass $data) {
        $feedbackcontroller = new feedback_controller($this->assignment);
        $notification = $this->get_config('notification');
        if ($grade) {
            if (isset($data->assignfeedbackauthor_feedbackforall) && $data->assignfeedbackauthor_feedbackforall == 1) {
                $submission = $this->get_submission($grade->userid, $grade->assignment);
                if ($submission) {
                    $authorsubmission = $feedbackcontroller->get_author_submission($grade->assignment, $submission->id);
                    if ($authorsubmission) {
                        $mode = 0;
                        $coauthors = $authorsubmission->author . ',' . $authorsubmission->authorlist;
                        $coauthors = explode(',', $coauthors);
                        $userarr = array(
                            $grade->userid
                        );
                        $coauthors = array_diff($coauthors, $userarr);
                        $this->set_assign_grade_for_coauthors($coauthors, $grade);
                        $assign = $this->assignment->get_instance();
                        $assign->cmidnumber = $this->assignment->get_course_module()->idnumber;
                        assign_update_grades($assign);
                        if ($notification) {
                            $this->send_notifications($grade->userid, $coauthors);
                        }
                        if ($this->assignment->get_instance()->markingworkflow == 1) {
                            $feedbackcontroller->set_user_flag($coauthors, $data->workflowstate, $data->allocatedmarker);
                        }
                        if ($this->is_plugin_enabled(ASSIGNFEEDBACK_COMMENTS, 'assignfeedback')) {
                            $feedbackcontroller->set_comments_feedback_for_coauthors($coauthors, $data, $authorsubmission->author);
                        }
                        $coauthors[] = $grade->userid;
                        $feedbackcontroller->set_author_feedback_for_coauthors($coauthors, $mode, $grade);
                        return true;
                    }
                }
            } else if (isset($data->assignfeedbackauthor_feedbackforsel) && $data->assignfeedbackauthor_feedbackforsel == 1) {
                $mode = 1;
                $coauthors = array();
                if (isset($data->assignfeedbackauthor_coauthors)) {
                    $coauthors = $data->assignfeedbackauthor_coauthors;
                }
                $array = array();
                foreach (array_keys($coauthors) as $key) {
                    $array[] = $key;
                }
                $coauthors = $array;
                $this->set_assign_grade_for_coauthors($coauthors, $grade);
                $assign = $this->assignment->get_instance();
                $assign->cmidnumber = $this->assignment->get_course_module()->idnumber;
                assign_update_grades($assign);
                if ($this->is_plugin_enabled(ASSIGNFEEDBACK_COMMENTS, 'assignfeedback')) {
                    $feedbackcontroller->set_comments_feedback_for_coauthors($coauthors, $data, $grade->userid);
                }
                if ($this->assignment->get_instance()->markingworkflow == 1) {
                    $feedbackcontroller->set_user_flag($coauthors, $data->workflowstate, $data->allocatedmarker);
                }
                $coauthors[] = $grade->userid;
                $feedbackcontroller->set_author_feedback_for_coauthors($coauthors, $mode, $grade);
                return true;
            } else if (isset($data->assignfeedbackauthor_feedbackforno) && $data->assignfeedbackauthor_feedbackforno == 1) {
                $mode = 2;
                $feedbackcontroller->set_author_feedback($grade->userid, '', $mode, $grade);
                return true;
            }
        }
        return true;
    }

    /**
     * Send notifications to all coauthors
     *
     * @param int $author
     * @param int[] $coauthors
     * @throws coding_exception
     * @throws dml_exception
     */
    private function send_notifications($author, $coauthors) {
        global $CFG, $USER;
        $user = core_user::get_user($author);
        $course = $this->assignment->get_course();
        $a = new stdClass();
        $a->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
        $a->coursename = $course->fullname;
        $a->assignmentname = format_string($this->assignment->get_instance()->name,
            true,
            array('context' => $this->assignment->get_context()));
        $a->assignmenturl = $CFG->wwwroot . '/mod/assign/view.php?id=' . $this->assignment->get_course_module()->id;
        $a->grader = fullname($USER);
        $subject = get_string('subject', 'assignfeedback_author', $a);

        foreach ($coauthors as $coauthor) {
            $userto = core_user::get_user($coauthor);
            $a->coauthors = implode(', ', utilities::get_author_array(implode(',', $coauthors),
                    false,
                    $this->assignment));
            $message = $subject . ': ' . get_string('message', 'assignfeedback_author', $a);
            $eventdata = new \core\message\message;
            $eventdata->modulename = 'assign';
            $eventdata->userfrom = $user;
            $eventdata->userto = $userto;
            $eventdata->subject = $subject;
            $eventdata->fullmessage = $message;
            $eventdata->fullmessageformat = FORMAT_HTML;
            $eventdata->fullmessagehtml = $message;
            $eventdata->smallmessage = '';
            $eventdata->name = 'assign_notification';
            $eventdata->component = 'mod_assign';
            $eventdata->notification = 1;
            $eventdata->contexturl = $CFG->wwwroot . '/mod/assign/view.php?id=' . $this->assignment->get_course_module()->id;
            $eventdata->contexturlname = format_string($this->assignment->get_instance()->name, true, array(
                'context' => $this->assignment->get_context()
            ));
            message_send($eventdata);
        }
    }

    /**
     * Set assign grade records for all coauthors
     *
     * @param int[] $coauthors
     * @param stdClass $grade
     * @throws dml_exception
     */
    private function set_assign_grade_for_coauthors($coauthors, $grade) {
        foreach ($coauthors as $coauthor) {
            $this->set_assign_grade($coauthor, $grade);
        }
    }

    /**
     * Get the assign grade record of a user for an assignment
     *
     * @param int $assignment
     * @param int $userid
     * @return mixed <stdClass or false>
     * @throws dml_exception
     */
    private function get_assign_grade($assignment, $userid) {
        global $DB;
        return $DB->get_record('assign_grades', array(
            'assignment' => $assignment,
            'userid' => $userid
        ));
    }

    /**
     * Set the assign grade record of a user for an assignment
     *
     * @param int $userid
     * @param stdClass $grade
     * @throws dml_exception
     */
    private function set_assign_grade($userid, $grade) {
        global $DB;
        $assigngrade = $this->get_assign_grade($grade->assignment, $userid);
        if ($assigngrade) {
            $assigngrade->timecreated = $grade->timecreated;
            $assigngrade->timemodified = time();
            $assigngrade->grader = $grade->grader;
            $assigngrade->grade = $grade->grade;
            $DB->update_record('assign_grades', $assigngrade);
        } else {
            $assigngrade = new stdClass();
            $assigngrade->assignment = $grade->assignment;
            $assigngrade->userid = $userid;
            $assigngrade->timecreated = time();
            $assigngrade->timemodified = time();
            $assigngrade->grader = $grade->grader;
            $assigngrade->grade = $grade->grade;
            $DB->insert_record('assign_grades', $assigngrade);
        }
        $assigngrade = $this->get_assign_grade($grade->assignment, $userid);
        $this->assignment->notify_grade_modified($assigngrade);
    }

    /**
     * Display the author and coauthors in the feedback status table.
     *
     * @param stdClass $grade
     * @param bool $showviewlink
     *            - Set to true to show a link to see the full list of files
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function view_summary(stdClass $grade, & $showviewlink) {
        $feedbackcontroller = new feedback_controller($this->assignment);
        $authorfeedback = $feedbackcontroller->get_author_feedback($grade->assignment, $grade->id);
        // Always show the view link.
        $showviewlink = false;

        if ($authorfeedback) {
            if ($authorfeedback->coauthors == '') {
                return get_string('summary_nocoauthors', 'assignfeedback_author');
            }
            $author = implode(', ', utilities::get_author_array($grade->userid, true, $this->assignment));
            $coauthors = implode(', ', utilities::get_author_array($authorfeedback->coauthors, true, $this->assignment));
            $summary = get_string('summary_graded', 'assignfeedback_author');
            $summary .= ': ';
            $summary .= $author;
            if ($coauthors != '') {
                $summary .= ', ';
                $summary .= $coauthors;
            }
            return $summary;
        }
        return get_string('summary_nocoauthors', 'assignfeedback_author');
    }

    /**
     * Display the author and coauthors in the feedback status table.
     *
     * @param stdClass $grade
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function view(stdClass $grade) {
        $showviewlink = true;
        return $this->view_summary($grade, $showviewlink);
    }

    /**
     * The assignment has been deleted - cleanup.
     *
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public function delete_instance() {
        global $DB;
        // Will throw exception on failure.
        $DB->delete_records('assignfeedback_author', array(
            'assignment' => $this->assignment->get_instance()->id
        ));

        return true;
    }

    /**
     * Return true if there is no author submission.
     *
     * @param stdClass $grade
     * @return bool
     * @throws dml_exception
     */
    public function is_empty(stdClass $grade) {
        $feedbackcontroller = new feedback_controller($this->assignment);
        $submission = $this->get_submission($grade->userid, $grade->assignment);
        if ($submission) {
            $authorsubmission = $feedbackcontroller->get_author_submission($grade->assignment, $submission->id);
            $authorfeedback = $feedbackcontroller->get_author_feedback($grade->assignment, $grade->id);
            return !($authorfeedback);
        }
        return true;
    }

    /**
     * Get the submission record of a user for an assignment
     *
     * @param int $userid
     * @param int $assignment
     * @return mixed <stdClass or false>
     * @throws dml_exception
     */
    private function get_submission($userid, $assignment) {
        global $DB;
        return $DB->get_record('assign_submission', array(
            'userid' => $userid,
            'assignment' => $assignment
        ));
    }
}
