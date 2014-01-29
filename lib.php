<?php
// This file is part of Oauth1 plugin for Moodle (http://moodle.org/)
//
// Oauth1 plugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Ouath1 plugin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Oauth1 authentication login
 *
 * @package    auth_oauth1
 * @author     Marco Cappuccio m.cappuccio@mediatouch.it
 * @author     Andrea Bicciolo a.bicciolo@mediatouch.it
 * @copyright  2014 onwards MediaTouch 2000 srl (http://www.mediatouch.it)
 * @copyright  2014 onwards Formez (http://www.formez.it/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('twitteroauth/twitteroauth.php');

function auth_oauth1_display_buttons() {
    global $CFG;

    $cfg = get_config('auth/oauth1');
    $connection = new TwitterOAuth($cfg->apiurl, $cfg->baseurl, $cfg->consumer_key,
        $cfg->consumer_secret);
    echo '<a href="'.$CFG->wwwroot.'/auth/oauth1/redirect.php"><img src="'.
    $CFG->wwwroot.'/auth/oauth1/pix/oauth.png" alt="Accedi a Formez"/></a>';
}

function auth_oauth1_callbackurl() {
    global $CFG;
    return $CFG->wwwroot.'/auth/oauth1/callback.php';
}

function stampahtml($content, $ctrl) {
    echo $ctrl;
    echo "<a href='./index.php?clear=1'>clearing your session</a>";
}
function clearsessions() {
    session_start();
    session_destroy();

    if (get_config('auth/oauth1', 'consumer_key') === '' || get_config('auth/oauth1', 'consumer_secret') === '') {
        echo 'You need a consumer key and secret.';
        exit;
    }
    $content = '<a href="./redirect.php"><img src="./pix/oauth.png" alt="Accedi a Formez"/></a>';
    stampahtml($content, 'funzclaer');
}
