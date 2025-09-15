<?php

/**
 * Strings for component 'local_ftptransfer'
 *
 * @package    local_ftptransfer
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['pluginname'] = 'FTP Transfer';
$string['ftptransfer_log'] = 'FTP Transfer Log';
$string['ftptransfer_no_logs'] = 'No FTP Transfer Logs Found';
$string['timecreated'] = 'Time Created';
$string['fromdate'] = 'From';
$string['todate'] = 'To';
$string['message'] = 'Message';
$string['perpage'] = 'Results per page';
$string['filter'] = 'Filter';
$string['filters'] = 'Filters';
$string['enable_ftptransfer'] = 'Enable FTP Transfer';
$string['ftp_host'] = 'FTP Host';
$string['ftp_host_desc'] = 'Enter the IP address or hostname of the desired FTP server. If the FTP server port is different from 21, specify it by adding a colon (:) followed by the port number, e.g., 127.0.0.1:29. If your FTP uses SSL, add ftps:// before the domain.';
$string['ftp_username'] = 'FTP Username';
$string['ftp_username_desc'] = '';
$string['ftp_password'] = 'FTP Password';
$string['ftp_password_desc'] = '';
$string['ftp_passive'] = 'FTP Passive Mode';
$string['ftp_passive_desc'] = 'The default FTP mode in PHP is active mode. Active mode rarely works due to firewalls/NATs/proxies. Therefore, you almost always need to use passive mode.';
$string['ftp_destination'] = 'FTP Destination';
$string['ftp_destination_desc'] = 'The absolute path for the destination folder. It must start with / and not end with / (e.g., /backup, /save/backup)';
$string['ftp_error_connecting'] = 'Error connecting to FTP server. Please check the host and port.';
$string['ftp_error_login'] = 'Error logging in to FTP server. Please check the username and password.';
$string['ftp_connection_failed'] = 'FTP connection failed.';
$string['no_files_found'] = 'No files found on the FTP server.';
$string['transfer_successful'] = 'File transfer completed successfully:    {$a->filename} ({$a->filesize}).';
$string['error_transferring_file'] = 'Error transferring file:                 {$a->filename} ({$a->filesize}).';