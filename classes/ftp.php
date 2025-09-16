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
 * Ftp file
 *
 * @package   local_ftptransfer
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @copyright 2025 Josemaria Bolanos <admin@mako.digital>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ftptransfer;

use Exception;
use stdClass;

/**
 * Class ftp
 *
 * @package local_ftptransfer
 */
class ftp {
    /** @var resource */
    public $connection = null;

    /** @var stdClass */
    public stdClass $config;

    /**
     * Function connect
     * 
     * @throws Exception
     */
    public function __construct() {
        $this->config = get_config('local_ftptransfer');

        $ftphost = $this->config->ftp_host;
        $ftpusername = $this->config->ftp_username;
        $ftpauth = $this->config->ftp_auth;
        $ftppublickeyfile = $this->config->ftp_public_key_file ?? '';
        $ftpprivatekeyfile = $this->config->ftp_private_key_file ?? '';
        $ftppassword = $this->config->ftp_password ?? '';
        $ftppassive = $this->config->ftp_passive;

        $url = parse_url($ftphost);

        if (isset($url["path"])) {
            $url["host"] = $url["path"];
        }
        if (!isset($url["port"])) {
            $url["port"] = 21;
        }

        if ($ftpauth == 'password') {
            if (isset($url["scheme"]) && $url["scheme"] == "ftps") {
                $this->connection = ftp_ssl_connect($url["host"], $url["port"]);
            } else {
                $this->connection = ftp_connect($url["host"], $url["port"]);
            }

            if (!$this->connection) {
                $this->connection = null;
                throw new Exception(get_string("ftp_error_connecting", "local_ftptransfer"));
            }

            if (!ftp_login($this->connection, $ftpusername, $ftppassword)) {
                $this->connection = null;
                throw new Exception(get_string("ftp_error_login", "local_ftptransfer"));
            }

            if ($ftppassive) {
                ftp_pasv($this->connection, true);
            }
        } else if ($ftpauth == 'keyfile') {
            if (!function_exists('ssh2_connect')) {
                throw new Exception(get_string("ftp_error_phpextension", "local_ftptransfer"));
            }

            $this->connection = ssh2_connect($url["host"], $url["port"], ['hostkey' => 'ssh-rsa']);
            if (!$this->connection) {
                $this->connection = null;
                throw new Exception(get_string("ftp_error_connecting", "local_ftptransfer"));
            }

            if (!ssh2_auth_pubkey_file($this->connection, $ftpusername, $ftppublickeyfile, $ftpprivatekeyfile, '') || !ssh2_sftp($this->connection)) {
                $this->connection = null;
                throw new Exception(get_string("ftp_error_login", "local_ftptransfer"));
            }
        } else {
            throw new Exception(get_string("ftp_error_unknown_auth", "local_ftptransfer"));
        }
    }

    /**
     * Retrieves a list of files and directories from the current directory on the FTP server.
     *
     * @return array|false An array of file and directory names on success, or false on failure.
     */
    public function list_files() {
        if ($this->config->ftp_auth == 'password') {
            $list = ftp_nlist($this->connection, ".");

            // Remove . and .. and folders.
            foreach ($list as $key => $item) {
                if ($item === '.' || $item === '..' || ftp_size($this->connection, $item) === -1) {
                    unset($list[$key]);
                }
            }
        } else {
            $sftp = ssh2_sftp($this->connection);
            $dir = opendir("ssh2.sftp://$sftp/.");

            $list = [];
            while (false !== ($file = readdir($dir))) {
                // Skip . and .. and folders.
                if ($file === '.' || $file === '..' || is_dir("ssh2.sftp://$sftp/$file")) {
                    continue;
                }
                $list[] = $file;
            }
            closedir($dir);
        }

        return $list;
    }

    /**
     * Transfers a remote file from the FTP server to a local destination.
     *
     * @param string $remotefile The path to the remote file on the FTP server.
     * @return bool
     */
    public function transfer($remotefile) {
        $localfile = $this->config->ftp_destination . '/' . $remotefile;

        if ($this->config->ftp_auth == 'password') {
            $destination = fopen($localfile, "w");
            $result = ftp_fget($this->connection, $destination, $remotefile, FTP_BINARY);
        } else {
            $sftp = ssh2_sftp($this->connection);
            $source = fopen("ssh2.sftp://$sftp/$remotefile", "r");
            $destination = fopen($localfile, "w");
            $result = stream_copy_to_stream($source, $destination) !== false;
            fclose($source);
            fclose($destination);
        }

        return $result;
    }

    /**
     * Deletes a remote file from the FTP server.
     *
     * @param string $remotefile The path to the remote file on the FTP server.
     * @return bool
     */
    public function delete($remotefile) {
        if ($this->config->ftp_auth == 'password') {
            $result = ftp_delete($this->connection, $remotefile);
        } else {
            $sftp = ssh2_sftp($this->connection);
            $result = ssh2_sftp_unlink($sftp, $remotefile);
        }

        return $result;
    }

    /**
     * Retrieves the size of a specified file from the FTP server and formats it as a human-readable string.
     *
     * @param string $file The path to the file on the FTP server.
     * @return string The formatted file size (e.g., "2 MB").
     */
    public function get_filesize($file) {
        if ($this->config->ftp_auth == 'password') {
            /**
             * @var \FTP\Connection|resource $this->connection
             */
            $size = ftp_size($this->connection, $file);
            $size = preg_replace('/[^0-9]/', "", $size);
        } else {
            $sftp = ssh2_sftp($this->connection);
            $size = ssh2_sftp_stat($sftp, $file)['size'];
        }

        return self::format_bytes($size);
    }

    /**
     * Function format_bytes
     *
     * @param $bytes
     * @return mixed|string
     */
    public static function format_bytes($bytes) {
        if ($bytes == 0) {
            return "0";
        }
        
        $bytes = $bytes / 1000;
        if ($bytes < 1000) {
            return self::remove_zero(number_format($bytes, 1) . " KB", 1);
        }

        $bytes = $bytes / 1000;
        if ($bytes < 1000) {
            return self::remove_zero(number_format($bytes, 1) . " MB", 1);
        }

        $bytes = $bytes / 1000;
        if ($bytes < 1000) {
            return self::remove_zero(number_format($bytes, 2) . " GB", 2);
        }

        $bytes = $bytes / 1000;

        return self::remove_zero(number_format($bytes, 3) . " TB", 3);
    }

    /**
     * Function remove_zero
     *
     * @param $text
     * @param $count
     * @return string
     */
    private static function remove_zero($text, $count) {
        if ($count == 3) {
            return str_replace(".000", "", $text);
        } else if ($count == 2) {
            return str_replace(".00", "", $text);
        } else {
            return str_replace(".0", "", $text);
        }
    }
}
