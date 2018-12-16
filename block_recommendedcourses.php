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
 * Block Recommended Courses
 *
 * @package     block_recommendedcourses
 * @copyright   Solin 2018 <martijn@solin.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_recommendedcourses extends block_base {

    /**
     * This function initialises the block and set's the title
     *
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_recommendedcourses');
    }

    /**
     * This function generates the content for the block.
     *
     * @return object - the content object.
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        if (!$courses = $this->get_available_courses()) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $this->page->requires->jquery();
        $this->page->requires->js('/blocks/recommendedcourses/bootstrap337/js/bootstrap.min.js');
        $this->page->requires->css('/blocks/recommendedcourses/bootstrap337/css/bootstrap.min.css');
        $output = html_writer::start_tag('div', array('id' => 'rc_carousel', 'class' => 'carousel slide',
                                                        'data-ride' => 'carousel'));

        $indicators = '';
        $items = '';
        $counter = 0;
        foreach ($courses as $course) {
            $class = '';
            if ($counter === 0) {
                $class = ' active';
            }
            $indicators .= html_writer::tag('li', '', array('data-target' => '#rc_carousel', 'data-slide-to' => $counter,
                                                            'class' => ltrim($class)));
            $image = $this->get_course_content_image($course);
            $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
            $coursename = html_writer::tag('div', html_writer::link($courseurl, $course->fullname),
                                            array('class' => 'rc_coursename'));
            $items .= html_writer::tag('div', $coursename . $image, array('class' => 'item' . $class));
            $counter++;
        }

        // Carousel items.
        $output .= html_writer::start_tag('div', array('class' => 'carousel-inner'));
        $output .= $items;
        $output .= html_writer::end_tag('div');

        // Carousel indicators.
        $output .= html_writer::start_tag('ol', array('class' => 'carousel-indicators'));
        $output .= $indicators;
        $output .= html_writer::end_tag('ol');

        // Carousel controls.
        $spanleft = html_writer::tag('span', '', array('class' => 'glyphicon glyphicon-chevron-left'));
        $output .= html_writer::link('#rc_carousel', $spanleft, array('data-slide' => 'prev', 'class' => 'rc_nav'));
        $spanright = html_writer::tag('span', '', array('class' => 'glyphicon glyphicon-chevron-right'));
        $output .= html_writer::link('#rc_carousel', $spanright,
                                        array('data-slide' => 'next', 'class' => 'rc_nav rc_carousel_next'));

        $output .= html_writer::end_tag('div');

        $this->content->text = $output;

        return $this->content;
    }

    /**
     * This function gets the available courses to display in the block.
     *
     * @return array - the list with courses.
     */
    private function get_available_courses() {
        global $DB;

        if (!$mycourses = enrol_get_my_courses()) {
            return array();
        }

        $coursekeys = array_keys($mycourses);

        list($where, $params) = $DB->get_in_or_equal($coursekeys);
        $select = "component = 'core' AND itemtype = 'course' AND itemid " . $where;
        if (!$taginstances = $DB->get_records_select('tag_instance', $select, $params)) {
            return array();
        }

        $tagids = array();
        foreach ($taginstances as $instance) {
            $tagids[] = $instance->tagid;
        }
        array_unique($tagids);

        list($wheretag, $whereparams) = $DB->get_in_or_equal($tagids, SQL_PARAMS_NAMED);
        list($wherecourse, $courseparams) = $DB->get_in_or_equal($coursekeys, SQL_PARAMS_NAMED, 'param', false);
        $sql = "SELECT c.*
        FROM {course} c
        JOIN {tag_instance} ti ON c.id = ti.itemid AND component = 'core' AND itemtype = 'course'
        WHERE ti.tagid " . $wheretag . "
        AND c.id " . $wherecourse . "
        ORDER BY c.sortorder ASC";
        $params = array_merge($whereparams, $courseparams);
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * This function gets the course content image.
     *
     * @param object - the course object
     * @return string - the html of the course image
     */
    private function get_course_content_image($course) {
        global $CFG, $OUTPUT;

        if ($course instanceof stdClass) {
            require_once($CFG->libdir. '/coursecatlib.php');
            $courselist = new course_in_list($course);
        }

        // Display course overview files.
        $contentimages = $contentfiles = '';
        foreach ($courselist->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url($CFG->wwwroot . "/pluginfile.php",
                    '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if ($isimage) {
                $contentimages .= html_writer::tag('div',
                        html_writer::empty_tag('img', array('src' => $url)),
                        array('class' => 'rc_courseimage'));
            }
        }
        if (empty($contentimages)) {
            $contentimages .= '';
        }

        return $contentimages;
    }
}
