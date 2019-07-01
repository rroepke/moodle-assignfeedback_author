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
 * Strings for component 'feedback_author', language 'de'
 *
 * @package     assignfeedback_author
 * @author      Rene Roepke
 * @author      Guido Roessling
 * @copyright   2013 Rene Roepke
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['author'] = 'Autorengruppen';
$string['coauthors'] = 'Co-Autoren';
$string['feedbackforall'] = 'Feedback für alle Mitglieder der Autorengruppe';
$string['feedbackforsel'] = 'Feedback für ausgewählte Mitglieder der Autorengruppe';
$string['feedbackforno'] = 'Kein Feedback für andere Mitglieder der Autorengruppe';
$string['summary_graded'] = 'Gleiche Bewertung';
$string['summary_nocoauthors'] = 'Keine gemeinsame Bewertung mit anderen.';
$string['notification'] = 'Benachrichtung aller Co-Autoren';
$string['notification_help'] = 'Wenn diese Option ausgewählt ist, werden alle Co-Autoren bei Abgabe benachrichtigt';

$string['subject'] = 'Feedback für Autorengruppe im Kurs <a href="{$a->courseurl}">{$a->coursename}</a>';
$string['message'] = '{$a->grader} hat Ihnen und anderen aus der Autorengruppe Feedback zur Aufgabe <a href="{$a->assignmenturl}">{$a->assignmentname}</a> gegeben.';

$string['submissionpluginmissing'] = 'Um diesen Feedbacktyp nutzen zu können, muss der gleichnamige Abgabetyp "Autorengruppen" aktiviert sein. Bitte kontaktieren Sie bei weiteren Fragen den Veranstalter des Kurses.';

$string['default'] = 'Standardmäßig aktiviert';
$string['default_help'] = 'Die gewählte Feedback-Methode wird für alle neuen Aufgaben voreingestellt.';
$string['enabled'] = 'Feedback als Kommentar';
$string['enabled_help'] = 'Bewerter/innen können Feedback auf Co-Autoren übertragen, wenn die Funktion aktiviert wird.';

$string['pluginname'] = 'Feedback für Autorengruppen';

// Privacy API.
$string['privacy:metadata:assignfeedback_author'] = 'Speichert Feedback für eine Einreichung durch eine selbst gewählte Gruppe von Studenten';
$string['privacy:assignfeedback_author:id'] = 'Eindeutige Kennung der Zeile';
$string['privacy:assignfeedback_author:assignment'] = 'ID der entsprechenden Zuordnung in der Tabelle "assign"';
$string['privacy:assignfeedback_author:grade'] = 'ID der entsprechenden Zuordnung in der Tabelle "assign_grades"';
$string['privacy:assignfeedback_author:mode'] = 'Der Modus, in dem das Feedback gegeben wurde. 0 ist für alle, 1 ist für ausgewählte, 2 ist nur für einen Student.';
$string['privacy:assignfeedback_author:coauthors'] = 'Kommagetrennte Liste der Studenten-IDs, die der Student als Co-Autoren deklariert hat';