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

class utilities {

    /**
     * Get the author ids and names as an array
     *
     * @param string $ids
     * @param bool $link
     * @return array
     * @throws \dml_exception
     */
    public static function get_author_array($ids, $link = false, $assignment) {
        global $DB, $CFG;
        if ($ids != '') {
            $ids = explode(',', $ids);
            $selectedauthors = array();
            foreach ($ids as $id) {
                $userrec = $DB->get_record('user', array(
                        'id' => $id
                ));
                if ($link) {
                    $url = $CFG->wwwroot . '/user/view.php?id=' . $userrec->id . '&course=' . $assignment->get_course()->id;
                    $selectedauthors[$userrec->id] = "<a href='" . $url . "'>" . fullname($userrec) . "</a>";
                } else {
                    $selectedauthors[$userrec->id] = fullname($userrec);
                }
            }
            return $selectedauthors;
        } else {
            return array();
        }
    }
}