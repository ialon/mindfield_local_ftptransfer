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
 * Settings file
 *
 * @package    local_ftptransfer
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings = new admin_settingpage(
    "local_ftptransfer",
    get_string("pluginname", "local_ftptransfer")
);

$ADMIN->add("localplugins", $settings);

if ($hassiteconfig) {
    $setting = new admin_setting_heading(
        "local_ftptransfer/settings",
        get_string("pluginname", "local_ftptransfer"),
        ""
    );
    $settings->add($setting);

    $setting = new admin_setting_configcheckbox(
        "local_ftptransfer/enable_ftptransfer",
        get_string("enable_ftptransfer", "local_ftptransfer"),
        "",
        1
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        "local_ftptransfer/ftp_host",
        get_string("ftp_host", "local_ftptransfer"),
        get_string("ftp_host_desc", "local_ftptransfer"),
        ""
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        "local_ftptransfer/ftp_username",
        get_string("ftp_username", "local_ftptransfer"),
        get_string("ftp_username_desc", "local_ftptransfer"),
        ""
    );
    $settings->add($setting);

    $setting = new admin_setting_configselect(
        "local_ftptransfer/ftp_auth",
        get_string("ftp_auth", "local_ftptransfer"),
        get_string("ftp_auth_desc", "local_ftptransfer"),
        "password",
        [
            "password" => get_string("ftp_auth_password", "local_ftptransfer"),
            "keyfile" => get_string("ftp_auth_keyfile", "local_ftptransfer"),
        ]
    );
    $settings->add($setting);

    $setting = new admin_setting_configpasswordunmask(
        "local_ftptransfer/ftp_password",
        get_string("ftp_password", "local_ftptransfer"),
        get_string("ftp_password_desc", "local_ftptransfer"),
        ""
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        "local_ftptransfer/ftp_public_key_file",
        get_string("ftp_public_key_file", "local_ftptransfer"),
        get_string("ftp_public_key_file_desc", "local_ftptransfer"),
        ""
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        "local_ftptransfer/ftp_private_key_file",
        get_string("ftp_private_key_file", "local_ftptransfer"),
        get_string("ftp_private_key_file_desc", "local_ftptransfer"),
        ""
    );
    $settings->add($setting);

    $settings->hide_if('local_ftptransfer/ftp_public_key_file', 'local_ftptransfer/ftp_auth', 'eq', 'password');
    $settings->hide_if('local_ftptransfer/ftp_private_key_file', 'local_ftptransfer/ftp_auth', 'eq', 'password');
    $settings->hide_if('local_ftptransfer/ftp_password', 'local_ftptransfer/ftp_auth', 'eq', 'keyfile');

    $setting = new admin_setting_configcheckbox(
        "local_ftptransfer/ftp_passive",
        get_string("ftp_passive", "local_ftptransfer"),
        get_string("ftp_passive_desc", "local_ftptransfer"),
        1
    );
    $settings->add($setting);

    $setting = new admin_setting_configcheckbox(
        "local_ftptransfer/ftp_delete_after_transfer",
        get_string("ftp_delete_after_transfer", "local_ftptransfer"),
        get_string("ftp_delete_after_transfer_desc", "local_ftptransfer"),
        1
    );
    $settings->add($setting);

    $setting = new admin_setting_configtext(
        "local_ftptransfer/ftp_destination",
        get_string("ftp_destination", "local_ftptransfer"),
        get_string("ftp_destination_desc", "local_ftptransfer"),
        ""
    );
    $settings->add($setting);

    $ADMIN->add('localplugins',
        new admin_externalpage(
            'local_ftptransfer/ftptransfer_log',
            get_string('ftptransfer_log', 'local_ftptransfer'),
            new moodle_url('/local/ftptransfer/log.php'),
        )
    );
}
