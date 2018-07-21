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
 * Restore subplugin class.
 *
 * @package   assignfeedback_author
 * @copyright 2017 Rene Roepke
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restore subplugin class.
 *
 * @package   assignfeedback_author
 * @copyright 2017 Rene Roepke
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_assignfeedback_author_subplugin extends restore_subplugin {

    /**
     * Returns the paths to be handled by the subplugin at workshop level
     * @return array
     */
    protected function define_grade_subplugin_structure() {

        $paths = array();

        $name = $this->get_namefor('grade');
        $path = $this->get_pathfor('/feedback_comments');

        $paths[] = new restore_path_element($name, $path);

        return $paths;
    }

    /**
     * Processes one feedback_author element.
     *
     * @param mixed $data
     * @throws dml_exception
     */
    public function process_assignfeedback_author_grade($data) {
        global $DB;

        $data = (object)$data;
        $data->assignment = $this->get_new_parentid('assign');
        $oldgradeid = $data->grade;
        // The mapping is set in the restore for the core assign activity
        // when a grade node is processed.
        $data->grade = $this->get_mappingid('grade', $data->grade);

        $DB->insert_record('assignfeedback_author', $data);
    }
}
