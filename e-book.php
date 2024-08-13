<?php
require_once('../../config.php');
//require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/e_library/e-book.php'));
$PAGE->set_title('E-Library');
$PAGE->set_heading('E-Library');
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

global $DB, $CFG;
require_once($CFG->dirroot . '/local/course_format/course_format.php');

// Define the category ID for "E-library"
$elibrary_category_id = 14; // Replace with the actual ID of the E-library category

// Get all sub-categories of the E-library category
$subcategories = $DB->get_records_sql('
    SELECT id, name
    FROM {course_categories}
    WHERE parent = ?
    ORDER BY name ASC',
    [$elibrary_category_id]
);

// Get the selected sub-category ID from the URL if available
$selected_subcategory_id = optional_param('subcategory', 'all', PARAM_INT);

// Get courses from the E-library category and its sub-categories
$sql = '
    SELECT c.id, c.fullname, c.summary, c.category, c.idnumber
    FROM {course} c
    JOIN {course_categories} cc ON c.category = cc.id
    WHERE c.visible = 1
    AND (cc.path LIKE ? OR cc.path LIKE ?)';
$params = [
    '%/'.$elibrary_category_id.'%',
    '%/'.$selected_subcategory_id.'%'
];

$sql .= ' ORDER BY c.fullname ASC';
$courses = $DB->get_records_sql($sql, $params);


// Function to get course image without '0/' in the path
function get_course_image($course) {
    global $CFG;
    $fs = get_file_storage();
    $context = context_course::instance($course->id);
    $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0, 'itemid, filepath, filename', false);

    if (count($files) > 0) {
        $file = reset($files);
        $url = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        );
        $urlstr = $url->out();

        // Debug: Output URL for inspection
        error_log("Debug: URL - " . $urlstr);

        // Manually adjust URL if necessary
        $urlstr = str_replace('/0/', '/', $urlstr);

        return $urlstr;
    } else {
        // Return a default image if no course image is available
        return $CFG->wwwroot . '/path/to/default/image.png'; // Replace with your default image path
    }
}
?>

<div class="tabs-elibrary">
    <ul id="subCategoryFilterTabs">
        <li><a href="https://mmyouth.net/local/e_library/e-book.php">E-Book</a></li>
        <li><a href="https://mmyouth.net/local/e_library/audio.php">Audio</a></li>
        <li><a href="https://mmyouth.net/local/e_library/video.php">Video</a></li>
        <li><a href="https://mmyouth.net/local/e_library/articles.php">Articles</a></li>
    </ul>
</div>

<div class="elibrary-search">
    <div class="cat-filter">
        <label for="subCategoryFilter">Category</label>
        <select id="subCategoryFilter" onchange="filterCoursesByCategory()">
            <option value="all" <?php echo $selected_subcategory_id === 'all' ? 'selected' : ''; ?>>All</option>
            <?php foreach ($subcategories as $subcategory): ?>
                <option value="<?php echo $subcategory->id; ?>" <?php echo $selected_subcategory_id == $subcategory->id ? 'selected' : ''; ?>><?php echo $subcategory->name; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="search-box">
        <label for="search">Search</label>
        <input type="text" id="courseSearch" placeholder="">
        <button onclick="filterCourses()">Search</button>
    </div>
</div>

<div id="courseList">
    <?php $counter = 0; ?>
    <?php foreach ($courses as $course): ?>
        <?php $courseurl = get_first_activity($course); ?>
        <?php if ($counter % 2 == 0): // Even index, image on the left ?>
            <div class="course" data-category="<?php echo $course->category; ?>">
                <div class="theme-col-6">
                    <img src="<?php echo get_course_image($course); ?>" alt="Course Image" style="width:150px; height:auto;">
                </div>
                <div class="theme-col-6"">
                    <h4><?php echo $course->fullname; ?></h4>
                    <p><?php echo format_text($course->summary, FORMAT_HTML); ?></p>
                    <p><a href="<?php echo $courseurl; ?>">Start Reading</a></p>
                </div>
            </div>
        <?php else: // Odd index, image on the right ?>
            <div class="course" data-category="<?php echo $course->category; ?>">
                <div class="theme-col-6">
                    <h4><?php echo $course->fullname; ?></h4>
                    <p><?php echo format_text($course->summary, FORMAT_HTML); ?></p>
                    <p><a href="<?php echo $courseurl; ?>">Start Reading</a></p>
                </div>
                <div class="theme-col-6">
                    <img src="<?php echo get_course_image($course); ?>" alt="Course Image" style="width:150px; height:auto;">
                </div>
            </div>
        <?php endif; ?>
        <?php $counter++; ?>
    <?php endforeach; ?>
</div>


<script>
function filterCourses() {
    let input = document.getElementById('courseSearch').value.toLowerCase();
    let categoryFilter = document.getElementById('subCategoryFilter').value;
    let courses = document.getElementsByClassName('course');

    for (let i = 0; i < courses.length; i++) {
        let course = courses[i];
        let courseTitle = course.getElementsByTagName('h4')[0].innerHTML.toLowerCase();
        let courseCategory = course.getAttribute('data-category');

        if ((courseTitle.indexOf(input) > -1 || input === '') &&
            (categoryFilter === 'all' || courseCategory == categoryFilter)) {
            course.style.display = "";
        } else {
            course.style.display = "none";
        }
    }
}



function filterCoursesByCategory() {
    let categoryFilter = document.getElementById('subCategoryFilter').value;
    // Update the URL to reflect the selected sub-category
    let newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?subcategory=' + categoryFilter;
    window.history.pushState({path: newUrl}, '', newUrl);

    filterCourses(); // Trigger the same filtering when the category is changed
}

</script>

<?php
echo $OUTPUT->footer();
