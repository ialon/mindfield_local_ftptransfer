<?php

/**
 * Log report for FTP Transfer task
 *
 * @package    local_ftptransfer
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Pagination
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 100, PARAM_INT);
$message = optional_param('message', '', PARAM_TEXT);
// Date filters
$startdate = optional_param_array('startdate', [], PARAM_ALPHANUMEXT);
$enddate = optional_param_array('enddate', [], PARAM_ALPHANUMEXT);

if (is_array($startdate) && !empty($startdate)) {
    $startdate = mktime(
        $startdate['hour'] ?? 0,
        $startdate['minute'] ?? 0,
        0,
        $startdate['month'] ?? 1,
        $startdate['day'] ?? 1,
        $startdate['year'] ?? date('Y')
    );
} else {
    $startdate = strtotime('today 00:00');
}

if (is_array($enddate) && !empty($enddate)) {
    $enddate = mktime(
        $enddate['hour'] ?? 23,
        $enddate['minute'] ?? 59,
        59,
        $enddate['month'] ?? 12,
        $enddate['day'] ?? 31,
        $enddate['year'] ?? date('Y')
    );
} else {
    $enddate = strtotime('today 23:59');
}

$url = new moodle_url('/local/ftptransfer/log.php', ['page' => $page]);

if ($perpage) {
    $url->param('perpage', $perpage);
}
if ($message) {
    $url->param('message', $message);
}
if ($startdate) {
    if (is_numeric($startdate)) {
        $startdatearray = [
            'year' => date('Y', $startdate),
            'month' => date('n', $startdate),
            'day' => date('j', $startdate),
            'hour' => date('G', $startdate),
            'minute' => date('i', $startdate)
        ];
    }
    foreach ($startdatearray as $key => $value) {
        $url->param("startdate[$key]", $value);
    }
}
if ($enddate) {
    if (is_numeric($enddate)) {
        $enddatearray = [
            'year' => date('Y', $enddate),
            'month' => date('n', $enddate),
            'day' => date('j', $enddate),
            'hour' => date('G', $enddate),
            'minute' => date('i', $enddate)
        ];
    }
    foreach ($enddatearray as $key => $value) {
        $url->param("enddate[$key]", $value);
    }
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$title = get_string('ftptransfer_log', 'local_ftptransfer');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$limitfrom = $page * $perpage;

// Get all logs from the database.
$fieldssql = "SELECT * ";
$countsql = "SELECT COUNT(id) ";
$sql = " FROM {local_ftptransfer_log} log
        WHERE 1=1";

$params = [];
if ($message) {
    $sql .= " AND " . $DB->sql_like('log.message', ':message', false);
    $params['message'] = '%' . $DB->sql_like_escape($message) . '%';
}
if ($startdate) {
    $sql .= " AND log.timecreated >= :startdate";
    $params['startdate'] = $startdate;
}
if ($enddate) {
    $sql .= " AND log.timecreated <= :enddate";
    $params['enddate'] = $enddate;
}

$sql .= " ORDER BY log.id DESC";

$logs = $DB->get_records_sql($fieldssql . $sql, $params, $limitfrom, $perpage);

echo $OUTPUT->header();

// Display the filters
$filterform = new \local_ftptransfer\form\log_filter();
$filterform->set_data([
    'message' => $message,
    'startdate' => $startdate,
    'enddate' => $enddate,
]);
$filterform->display();

if (empty($logs)) {
    echo get_string('ftptransfer_no_logs', 'local_ftptransfer');
} else {
    $table = new html_table();

    $table->head[] = get_string('message', 'local_ftptransfer');
    $table->head[] = get_string('timecreated', 'local_ftptransfer');

    $table->data = [];
    foreach ($logs as $log) {
        $row = [
            $log->message ?? '',
            userdate($log->timecreated)
        ];
        $table->data[] = $row;
    }

    $paginationbar = $OUTPUT->paging_bar($DB->count_records_sql($countsql . $sql, $params), $page, $perpage, $PAGE->url);

    echo $paginationbar;
    echo html_writer::table($table);
    echo $paginationbar;
}

echo $OUTPUT->footer();
