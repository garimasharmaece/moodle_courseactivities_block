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
 * This file contains the course modules block.
 *
 * @package    block_test_block
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_test_block extends block_list {

    /**
     * Core function used to initialize the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_test_block');
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * This block returns activities of course modules
     */
    public function get_content() {
        global $USER, $CFG, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->footer = '';

        $course = $this->page->course;

        $modinfo = get_fast_modinfo($course->id);
        foreach ($modinfo->cms as $cm) {
            // Exclude activities which are not visible or not available.
            if (!$cm->uservisible && $cminfo->available || !$cm->has_view()) {
                continue;
            }
            $modfullnames[$cm->modname] = $cm->modplural;
        }

        foreach ($modfullnames as $modname => $modfullname) {

            $instances = $modinfo->get_instances_of($modname);
            foreach ($instances as $cminfo) {
                // Don't list instances that are not visible or available to the user.
                if ($cminfo->uservisible && $cminfo->available) {
                    $completioninfo = new completion_info($course);
                    $completiondata = $completioninfo->get_data($cminfo, true);
                    $formatname = $cminfo->get_formatted_name().'&instance='.$cminfo->instance;
                    $options[$cminfo->id.'-'.$modname.'_'.$completiondata->completionstate] = $formatname;
                }
            }
        }
        if (!empty($options)) {
            foreach ($options as $optionkey => $name) {
                $explodename = explode("-", $optionkey);
                $status = explode("_", $optionkey);
                $modulename = str_replace('_'.$status[1], '', $explodename[1]);
                $activityname = explode("&instance=", $name);
                $activityinfo = $DB->get_record($modulename, array("id" => $activityname[1]));
                $activitytime = (isset($activityinfo->timecreated) && $activityinfo->timecreated > 0 ) ? $activityinfo->timecreated : $activityinfo->timemodified;
                $creationtime = $activityname[0].' - '.date("d-M-Y",$activitytime).($status[1] > 0 ? " - Completed" : "");
                $this->content->items[] = '<a href="'.$CFG->wwwroot.'/mod/'.$modulename.'/view.php?id='.$explodename[0].'">'.$explodename[0].' - '.$creationtime.'</a>';
            }
        }
        return $this->content;
    }

    /**
     * Core function, specifies where the block can be used.
     * @return array
     */
    public function applicable_formats() {
        return array('all' => false, 'course' => true, 'course-view' => true);
    }
}
