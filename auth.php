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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

class auth_plugin_oauth1 extends auth_plugin_base {

    /**
     * Constructor with initialisation.
     */
    function auth_plugin_oauth1() {
        $this->authtype = 'oauth1';
        $this->roleauth = 'auth_oauth1';
        $this->errorlogtag = '[AUTH OAUTH1] ';
        $this->config = get_config('auth/oauth1');
    }

    /**
     * Prevent authenticate_user_login() to update the password in the DB
     * @return boolean
     */
    function prevent_local_passwords() {
        return true;
    }

    function user_login($username, $password) {
        global $DB, $CFG;

        // Retrieve the user matching username.
        $user = $DB->get_record('user', array('username' => $username,
            'mnethostid' => $CFG->mnet_localhost_id));
        // Username must exist and have the right authentication method.
        if (!empty($user) && ($user->auth == 'oauth1')) {
            $code = optional_param('code', false, PARAM_TEXT);
            if ($code === false) {
                return false;
            }
            return true;
        }

        return false;
    }

    function get_userinfo($username) {
        global $SESSION, $CFG;

        $attrmap = $this->get_attributes();
        $result = array();

        $authorizationcode = optional_param('code', '', PARAM_TEXT);
        if (!empty($authorizationcode) && 200 == $authorizationcode) {
            require_once($CFG->dirroot.'/auth/oauth1/lib.php');
            $cfg = get_config('auth/oauth1');
            $accesstoken = $SESSION->access_token;

            $connection = new TwitterOAuth($cfg->apiurl, $cfg->baseurl, $cfg->consumer_key, $cfg->consumer_secret,
                $accesstoken['oauth_token'], $accesstoken['oauth_token_secret']);
            $userinfo = $connection->post($cfg->apifunc);
            //
            foreach ($attrmap as $key=>$value) {
                // Check if attribute is present
               /*  if (!isset($_SERVER[$value])){
                    $result[$key] = '';
                    continue;
                } */
// $userinfo->field_nome->und[0]->value
                // $result[$key] = $this->get_first_string($_SERVER[$value]);
                if (preg_match('/,/', $value)) {
                    $tmp = explode(',', $value);
                     $result[$key] = $userinfo->{$tmp[0]}->{$tmp[1]}[0]->{$tmp[2]};
                    // $result[$key] = $userinfo->field_citta->und[0]->value;
                } else {
                    $result[$key] = $value;
                }         
            }
        }
        return $result;
    }

    function get_attributes() {
        // $moodleattributes = array();
        // $userfields = $this->userfields;
        // foreach ($userfields as $field) {
            // if (!empty($this->config->{"field_map_$field"})) {
                // $moodleattributes[$field] = $this->config->{"field_map_$field"};
            // }
        // }
        // $moodleattributes['username'] = $this->config->fielduser;
        // return $moodleattributes;
        $moodleattributes = array();
        $customfields = $this->get_custom_user_profile_fields();
        if (!empty($customfields) && !empty($this->userfields)) {
            $userfields = array_merge($this->userfields, $customfields);
        } else {
            $userfields = $this->userfields;
        }

        foreach ($userfields as $field) {
            if (!empty($this->config->{"field_map_$field"})) {
                $moodleattributes[$field] = core_text::strtolower(trim($this->config->{"field_map_$field"}));
                // if (preg_match('/,/', $moodleattributes[$field])) {
                    // $moodleattributes[$field] = explode(',', $moodleattributes[$field]); // split ?
                // }
                // $moodleattributes[$field] = $this->config->{"field_map_$field"};
            }
        }
        $moodleattributes['username'] = core_text::strtolower(trim($this->config->username));
        return $moodleattributes;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * Authentication hook - is called every time user hit the login page
     * The code is run only if the param code is mentionned.
     */
    function loginpage_hook() {
        global $SESSION, $CFG, $DB, $USER;

        $authorizationcode = optional_param('code', '', PARAM_TEXT);
        if (!empty($authorizationcode) && 200 == $authorizationcode) {
            require_once($CFG->dirroot.'/auth/oauth1/lib.php');
            $cfg = get_config('auth/oauth1');
            $accesstoken = $SESSION->access_token;
            $connection = new TwitterOAuth($cfg->apiurl, $cfg->baseurl, $cfg->consumer_key, $cfg->consumer_secret,
                $accesstoken['oauth_token'], $accesstoken['oauth_token_secret']);
            $userinfo = $connection->post($cfg->apifunc);
          
            if (!empty($userinfo->{$cfg->username})) {
                // $useremail = $userinfo->mail;
                $user = $DB->get_record('user', array('username' => $userinfo->{$cfg->username}, 'deleted' => 0,
                    'mnethostid' => $CFG->mnet_localhost_id));
                // Create the user if it doesn't exist.
                if (empty($user)) {
                    // Deny login if setting "Prevent account creation when authenticating" is on.
                    if ($CFG->authpreventaccountcreation) {
                        throw new moodle_exception("noaccountyet", "auth_oauth1");
                    }
                    // Retrieve more information from the provider.
                    // $newuser = new stdClass();
                    // $newuser->auth = 'oauth1';
                    // $username = $userinfo->uuid;
                    $username = $userinfo->{$cfg->username};
                    // $newuser->username = $username;
                    // $newuser->email = $userinfo->mail;
                    // $newuser->lang = $userinfo->language;
                    // if (!empty($userinfo->field_nome->und[0]->value)) {
                        // $newuser->firstname = $userinfo->field_nome->und[0]->value;
                    // }
                    // if (!empty($userinfo->field_cognome->und[0]->value)) {
                        // $newuser->lastname = $userinfo->field_cognome->und[0]->value;
                    // }
                    // if (!empty($userinfo->field_citta->und[0]->value)) {
                        // $newuser->city = $userinfo->field_citta->und[0]->value;
                    // }
                    // if (!empty($userinfo->field_telefono->und[0]->value)) {
                        // $newuser->phone1 = $userinfo->field_telefono->und[0]->value;
                    // }
                    // if (!empty($userinfo->field_cellulare->und[0]->value)) {
                        // $newuser->phone1 = $userinfo->field_cellulare->und[0]->value;
                    // }
                    // if (!empty($userinfo->field_unit_organizzativa->und[0]->value)) {
                        // $newuser->istitution = $userinfo->field_unit_organizzativa->und[0]->value;
                    // }
                    create_user_record($username, '', 'oauth1');
                } else {
                    $username = $user->username;
                }
                // Authenticate the user.
                $userid = empty($user)?'new user':$user->id;
                add_to_log(SITEID, 'auth_oauth1', '', '', $username . '/' . $userid);
                $user = authenticate_user_login($username, null);
                if ($user) {
                    //set a cookie to remember what auth provider was selected
                    // setcookie('MOODLEOAUTH1_'.$CFG->sessioncookie, 'oauth1',
                            // time()+(DAYSECS*60), $CFG->sessioncookiepath,
                            // $CFG->sessioncookiedomain, $CFG->cookiesecure,
                            // $CFG->cookiehttponly);
                    //prefill more user information if new user
                   
                    if (!empty($newuser)) {
                        $newuser->id = $user->id;
                        $DB->update_record('user', $newuser);
                        $user = (object) array_merge((array) $user, (array) $newuser);
                    }

                    complete_user_login($user);
                    // Create event for authenticated user.
                    $event = \auth_oauth1\event\user_loggedin::create(
                        array('context' => context_system::instance(),
                            'objectid' => $user->id, 'relateduserid' => $user->id,
                            'other' => array('accesstoken' => $accesstoken)));
                    $event->trigger();
                    // Redirection.
                    if (user_not_fully_set_up($USER)) {
                        $urltogo = $CFG->wwwroot.'/user/edit.php';
                        // We don't delete $SESSION->wantsurl yet, so we get there later.
                    } else if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0)) {
                        $urltogo = $SESSION->wantsurl;    // Because it's an address in this site.
                        unset($SESSION->wantsurl);
                    } else {
                        // No wantsurl stored or external - go to homepage.
                        $urltogo = $CFG->wwwroot.'/';
                        unset($SESSION->wantsurl);
                    }
                    redirect($urltogo);
                   
                }
            } else {
                throw new moodle_exception('invalid access', 'auth_oauth1');
            }
            
        } 
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        global $OUTPUT, $CFG;

        // Set to defaults if undefined.
        if (!isset ($config->consumer_key)) {
            $config->consumer_key = '';
        }
        if (!isset ($config->consumer_secret)) {
            $config->consumer_secret = '';
        }
        if (!isset ($config->baseurl)) {
            $config->baseurl = '';
        }
        if (!isset ($config->apiurl)) {
            $config->apiurl = '';
        }
        if (!isset ($config->apifunc)) {
            $config->apifunc = '';
        }
        if (!isset ($config->username)) {
            $config->username = '';
        }

        echo '<table cellspacing="0" cellpadding="5" border="0"><tr><td colspan="3"><h2 class="main">';
        print_string('auth_oauth1settings', 'auth_oauth1');
        echo '</h2></td></tr>';
        // Consumer key.
        echo '<tr><td align="right"><label for="consumer_key">';
        print_string('auth_consumer_key', 'auth_oauth1');
        echo '</label></td><td>';
        echo html_writer::empty_tag('input',
            array('type' => 'text', 'id' => 'consumer_key', 'name' => 'consumer_key',
            'class' => 'consumer_key', 'value' => $config->consumer_key));
        if (isset($err["consumer_key"])) {
            echo $OUTPUT->error_text($err["consumer_key"]);
        }
        echo '</td><td>';
        print_string('auth_consumer_key_desc', 'auth_oauth1');
        echo '</td></tr>';
        // Consumer_secret.
        echo '<tr><td align="right"><label for="consumer_secret">';
        print_string('auth_consumer_secret', 'auth_oauth1');
        echo '</label></td><td>';
        echo html_writer::empty_tag('input',
            array('type' => 'text', 'id' => 'consumer_secret', 'name' => 'consumer_secret',
                'class' => 'consumer_secret', 'value' => $config->consumer_secret));
        if (isset($err["consumer_secret"])) {
            echo $OUTPUT->error_text($err["consumer_secret"]);
        }
        echo '</td><td>';
        print_string('auth_consumer_secret_desc', 'auth_oauth1');
        echo '</td></tr>';
        // URL base.
        echo '<tr><td align="right"><label for="baseurl">';
        print_string('auth_baseurl', 'auth_oauth1');
        echo '</label></td><td>';
        echo html_writer::empty_tag('input',
            array('type' => 'text', 'id' => 'baseurl', 'name' => 'baseurl',
                'class' => 'baseurl', 'value' => $config->baseurl));

        if (isset($err["baseurl"])) {
            echo $OUTPUT->error_text($err["baseurl"]);
        }
        echo '</td><td>';
        print_string('auth_baseurl_desc', 'auth_oauth1');
        echo '</td></tr>';
        // URL Api.
        echo '<tr><td align="right"><label for="apiurl">';
        print_string('auth_apiurl', 'auth_oauth1');
        echo '</label></td><td>';
        echo html_writer::empty_tag('input',
            array('type' => 'text', 'id' => 'apiurl', 'name' => 'apiurl',
                'class' => 'apiurl', 'value' => $config->apiurl));

        if (isset($err["apiurl"])) {
            echo $OUTPUT->error_text($err["apiurl"]);
        }
        echo '</td><td>';
        print_string('auth_apiurl_desc', 'auth_oauth1');
        echo '</td></tr>';
        // Func Api.
        echo '<tr><td align="right"><label for="apifunc">';
        print_string('auth_apifunc', 'auth_oauth1');
        echo '</label></td><td>';
        echo html_writer::empty_tag('input',
            array('type' => 'text', 'id' => 'apifunc', 'name' => 'apifunc',
                'class' => 'apifunc', 'value' => $config->apifunc));

        if (isset($err["apifunc"])) {
            echo $OUTPUT->error_text($err["apifunc"]);
        }
        echo '</td><td>';
        print_string('auth_apifunc_desc', 'auth_oauth1');
        echo '</td></tr>';
        // Username.
        echo '<tr><td align="right"><label for="username">';
        print_string('username');
        echo '</label></td><td>';
        echo html_writer::empty_tag('input',
            array('type' => 'text', 'id' => 'username', 'name' => 'username',
                'class' => 'username', 'value' => $config->username));
        if (isset($err["username"])) {
            echo $OUTPUT->error_text($err["username"]);
        }
        echo '</td><td>&nbsp;</td></tr>';
/*         // Block field options.
        // Hidden email options - email must be set to: locked.
        echo html_writer::empty_tag('input', array('type' => 'hidden', 'value' => 'locked',
            'name' => 'lockconfig_field_lock_email'));

        // Display other field options.
        foreach ($user_fields as $key => $user_field) {
            if ($user_field == 'email') {
                unset($user_fields[$key]);
            }
        } */
        print_auth_lock_options('oauth1', $user_fields, get_string('auth_fieldlocks_help', 'auth_oauth1'), true, false, $this->get_custom_user_profile_fields());
        echo '</table>';
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config) {
        // Set to defaults if undefined.
        if (!isset ($config->consumer_key)) {
            $config->consumer_key = '';
        }
        if (!isset ($config->consumer_secret)) {
            $config->consumer_secret = '';
        }
        if (!isset ($config->baseurl)) {
            $config->baseurl = '';
        }        
        if (!isset ($config->apiurl)) {
            $config->apiurl = '';
        }
        if (!isset ($config->apifunc)) {
            $config->apifunc = '';
        }        
        if (!isset ($config->username)) {
            $config->username = '';
        }
        // Save settings.
        set_config('consumer_key', $config->consumer_key, 'auth/oauth1');
        set_config('consumer_secret', $config->consumer_secret, 'auth/oauth1');
        set_config('baseurl', $config->baseurl, 'auth/oauth1');
        set_config('apiurl', $config->apiurl, 'auth/oauth1');
        set_config('apifunc', $config->apifunc, 'auth/oauth1');
        set_config('username', $config->username, 'auth/oauth1');

        return true;
    }

    /**
     * Called when the user record is updated.
     *
     * We check there is no hack-attempt by a user to change his/her email address
     *
     * @param mixed $olduser     Userobject before modifications    (without system magic quotes)
     * @param mixed $newuser     Userobject new modified userobject (without system magic quotes)
     * @return boolean result
     *
     */
    function user_update($olduser, $newuser) {
        if ($olduser->email != $newuser->email) {
            return false;
        } else {
            return true;
        }
    }

}