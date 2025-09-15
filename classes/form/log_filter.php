<?php

namespace local_ftptransfer\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class log_filter extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'settingsheader', get_string('filters', 'local_ftptransfer'));

        // Add a text field for searching by message.
        $mform->addElement('text', 'message', get_string('message', 'local_ftptransfer'));
        $mform->setType('message', PARAM_TEXT);

        // Add a date selector for filtering by start date.
        $mform->addElement('date_time_selector', 'startdate', get_string('fromdate', 'local_ftptransfer'));
        $mform->setType('startdate', PARAM_INT);

        // Add a date selector for filtering by end date.
        $mform->addElement('date_time_selector', 'enddate', get_string('todate', 'local_ftptransfer'));
        $mform->setType('enddate', PARAM_INT);

        // Add a select for page size.
        $mform->addElement('select', 'perpage', get_string('perpage', 'local_ftptransfer'), [10 => 10, 25 => 25, 50 => 50, 100 => 100, 150 => 150, 200 => 200]);
        $mform->setType('perpage', PARAM_INT);

        // Add action buttons.
        $this->add_action_buttons(false, get_string('filter', 'local_ftptransfer'));
    }
}
