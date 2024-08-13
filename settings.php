<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // Check if the user has permission to configure the site.
    $settings = new admin_settingpage('local_e_library', get_string('pluginname', 'local_e_library'));

    // Example of a text setting.
    $settings->add(new admin_setting_configtext(
        'local_e_library/some_setting',
        get_string('somesetting', 'local_e_library'),
        get_string('somesetting_desc', 'local_e_library'),
        '', // Default value
        PARAM_TEXT
    ));

    // Add the settings page to the local plugins menu.
    $ADMIN->add('localplugins', $settings);
}
