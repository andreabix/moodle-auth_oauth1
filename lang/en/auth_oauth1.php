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

$string['pluginname'] = 'Oauth1';
$string['auth_apifunc'] = 'Function API';
$string['auth_apifunc_desc'] = 'Funzione di chiamata API.';
$string['auth_apiurl'] = 'URL API';
$string['auth_apiurl_desc'] = 'Indirizzo chiamate API.';
$string['auth_baseurl'] = 'URL Provider';
$string['auth_baseurl_desc'] = 'Indirizzo base del provider.
<br>Callback URL: '.$CFG->wwwroot.'/auth/oauth1/callback.php';
$string['auth_consumer_key'] = 'Consumer Key';
$string['auth_consumer_key_desc'] = 'Chiave di autorizzazione consumer_key';
$string['auth_consumer_secret'] = 'Consumer Secret';
$string['auth_consumer_secret_desc'] = 'Chiave di autorizzazione consumer_secret';
$string['auth_fieldlocks_help'] = 'Questi campi sono opzionali. E\' possibile scegliere di riempire alcuni campi dell\'utente in Moodle con i dati provenienti dai campi OAUTH1.<br><br>
<b>Aggiorna dati interni</b>: Se abilitato, il campo sarà aggiornato (dall\'autenticazione esterna) tutte le volte che l\'utente accede. I campi impostati per l\'aggiornamento locale devono essere bloccati.<br><br>
<b>Blocca valore</b>: Se abilitato, impedirà agli utenti e agli amministratori di Moodle di modificare il campo direttamente.';
$string['auth_oauth1description'] = 'Consente all\'utente di connettersi al sito attraverso un servizio esterno (oauth1).
La prima volta che l\'utente si collega, viene creato un nuovo account.
<br>L\'opzione <a href="'.$CFG->wwwroot.'/admin/search.php?query=authpreventaccountcreation">Evita la creazione di account all\'atto dell\'autenticazione</a> <b>non deve</b> essere attiva.';
$string['auth_oauth1settings'] = 'Settings';

