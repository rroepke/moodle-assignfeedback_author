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
 * Strings for component 'feedback_author', language 'en'
 *
 * @package     assignfeedback_author
 * @author      Rene Roepke
 * @author      Guido Roessling
 * @copyright   2013 Rene Roepke
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['author'] = 'Author groups';
$string['coauthors'] = 'Co-authors';
$string['feedbackforall'] = 'Feedback for all members of the author group';
$string['feedbackforsel'] = 'Feedback for selected members of the author group';
$string['feedbackforno'] = 'No feedback for other members of the author group';
$string['summary_graded'] = 'Same grade';
$string['summary_nocoauthors'] = 'No same grade with others.';
$string['notification'] = 'Inform all graded co-authors';
$string['notification_help'] = 'If enabled, then all graded co-authors will get a message after feedback';
$string['submissionpluginmissing'] = 'To use this feedback type, the submission type "Author groups" should be enabled. Please contact the course manager if you have any questions.';

$string['subject'] = 'Author group feedback in course <a href="{$a->courseurl}">{$a->coursename}</a>';
$string['message'] = '{$a->grader} has given the same feedback to you and others for the assignment <a href="{$a->assignmenturl}">{$a->assignmentname}</a>.';

$string['default'] = 'Enabled by default';
$string['default_help'] = 'If set, this feedback method will be enabled by default for all new assignments.';
$string['enabled'] = 'File feedback';
$string['enabled_help'] = 'If enabled, the teacher will be able to upload files with feedback when marking the assignments. These files may be, but are not limited to marked up student submissions, documents with comments or spoken audio feedback. ';

$string['pluginname'] = 'Author groups feedback';


