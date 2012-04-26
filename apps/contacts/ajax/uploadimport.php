<?php
/**
 * ownCloud - Addressbook
 *
 * @author Thomas Tanghus
 * @copyright 2012 Thomas Tanghus <thomas@tanghus.net>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
// Init owncloud
require_once('../../../lib/base.php');

// Check if we are a user
OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('contacts');
function bailOut($msg) {
	OC_JSON::error(array('data' => array('message' => $msg)));
	OC_Log::write('contacts','ajax/uploadimport.php: '.$msg, OC_Log::ERROR);
	exit();
}
function debug($msg) {
	OC_Log::write('contacts','ajax/uploadimport.php: '.$msg, OC_Log::DEBUG);
}

$view = OC_App::getStorage('contacts');
$tmpfile = md5(rand());

// If it is a Drag'n'Drop transfer it's handled here.
$fn = (isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : false);
if($fn) {
	if($view->file_put_contents('/'.$tmpfile, file_get_contents('php://input'))) {
		debug($fn.' uploaded');
		OC_JSON::success(array('data' => array('path'=>'', 'file'=>$tmpfile)));
		exit();
	} else {
		bailOut(OC_Contacts_App::$l10n->t('Error uploading contacts to storage.'));
	}
}

// File input transfers are handled here
if (!isset($_FILES['importfile'])) {
	OC_Log::write('contacts','ajax/uploadphoto.php: No file was uploaded. Unknown error.', OC_Log::DEBUG);
	OC_JSON::error(array('data' => array( 'message' => 'No file was uploaded. Unknown error' )));
	exit();
}
$error = $_FILES['importfile']['error'];
if($error !== UPLOAD_ERR_OK) {
	$errors = array(
		0=>OC_Contacts_App::$l10n->t("There is no error, the file uploaded with success"),
		1=>OC_Contacts_App::$l10n->t("The uploaded file exceeds the upload_max_filesize directive in php.ini").ini_get('upload_max_filesize'),
		2=>OC_Contacts_App::$l10n->t("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
		3=>OC_Contacts_App::$l10n->t("The uploaded file was only partially uploaded"),
		4=>OC_Contacts_App::$l10n->t("No file was uploaded"),
		6=>OC_Contacts_App::$l10n->t("Missing a temporary folder")
	);
	bailOut($errors[$error]);
}
$file=$_FILES['importfile'];

$tmpfname = tempnam("/tmp", "occOrig");
if(file_exists($file['tmp_name'])) {
	if($view->file_put_contents('/'.$tmpfile, file_get_contents($file['tmp_name']))) {
		debug($fn.' uploaded');
		OC_JSON::success(array('data' => array('path'=>'', 'file'=>$tmpfile)));
	} else {
		bailOut(OC_Contacts_App::$l10n->t('Error uploading contacts to storage.'));
	}
} else {
	bailOut('Temporary file: \''.$file['tmp_name'].'\' has gone AWOL?');
}


?>