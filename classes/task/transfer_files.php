<?php

namespace local_ftptransfer\task;

use local_ftptransfer\ftp;

/**
 * Task function to transfer files from FTP.
 *
 * @package    local_ftptransfer
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transfer_files extends \core\task\scheduled_task {
    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('pluginname', 'local_ftptransfer');
    }

    /**
     * Execute the task
     */
    public function execute() {
        global $DB;

        $config = get_config('local_ftptransfer');

        if (empty($config->enable_ftptransfer)) {
            return;
        }

        $messages = [];
        $messages[] = 'Executing FTP Transfer task';

        try {
            $ftp = new ftp();

            // Test connection.
            if (!$ftp->connection) {
                $message = get_string('ftp_connection_failed', 'local_ftptransfer');
                throw new \Exception($message);
            } else {
                // List files.
                $files = $ftp->list_files();

                if (empty($files)) {
                    $messages[] = get_string('no_files_found', 'local_ftptransfer');
                } else {
                    // Transfer files.
                    foreach ($files as $file) {
                        $file = basename($file);
                        if ($ftp->transfer($file)) {
                            $messages[] = get_string('transfer_successful', 'local_ftptransfer', [
                                'filename' => $file,
                                'filesize' => $ftp->get_filesize($file),
                            ]);

                            if ($ftp->config->ftp_delete_after_transfer) {
                                if ($ftp->delete($file)) {
                                    $messages[] = get_string('deleted_file', 'local_ftptransfer', [
                                        'filename' => $file,
                                    ]);
                                } else {
                                    $messages[] = get_string('error_deleting_file', 'local_ftptransfer', [
                                        'filename' => $file,
                                    ]);
                                }
                            }
                        } else {
                            $messages[] = get_string('error_transferring_file', 'local_ftptransfer', [
                                'filename' => $file,
                                'filesize' => $ftp->get_filesize($file),
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $messages[] = 'Error during FTP Transfer task: ' . $e->getMessage();
        }

        $messages[] = 'FTP Transfer task completed successfully';

        foreach ($messages as $message) {
            mtrace($message);
            $DB->insert_record('local_ftptransfer_log', [
                'message' => $message,
                'timecreated' => time(),
            ]);
        }
    }
}
