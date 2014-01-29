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

require('../../config.php');
require_once('lib.php');
global $SESSION;
$cfg = get_config('auth/oauth1');

if (isset($_REQUEST['oauth_token']) && $SESSION->oauth_token !== $_REQUEST['oauth_token']) {
    clearsessions();
}
$connection = new TwitterOAuth($cfg->apiurl, $cfg->baseurl, $cfg->consumer_key, $cfg->consumer_secret, $SESSION->oauth_token,
    $SESSION->oauth_token_secret);
$accesstoken = $connection->getAccessToken($_REQUEST['oauth_token']);
$SESSION->access_token = $accesstoken;
unset($SESSION->oauth_token);
unset($SESSION->oauth_token_secret);

$code = $connection->http_code;
$loginurl = '/login/index.php';
if (!empty($CFG->alternateloginurl)) {
    $loginurl = $CFG->alternateloginurl;
}
$url = new moodle_url($loginurl, array('code' => $code, 'authprovider' => 'oauth1'));
redirect($url);