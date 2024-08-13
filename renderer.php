<?php

class local_e_library_renderer extends plugin_renderer_base {
    public function render_course_summary($course) {
        $output = html_writer::start_tag('div', array('class' => 'course-summary'));
        $output .= html_writer::tag('h2', $course->fullname);
        $output .= html_writer::tag('p', $course->summary);
        $output .= html_writer::end_tag('div');
        return $output;
    }
}
