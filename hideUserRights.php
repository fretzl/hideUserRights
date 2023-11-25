<?php
/**
 * Plugin to hide the display of user rights and other info for users that do NOT have ADMIN_RIGHTS.
 *
 * @author Fred Sondaar (fretzl)
 * @package plugins
 * @subpackage admin
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext_pl("Hide the display of user rights and other info for users that do NOT have ADMIN_RIGHTS", "hideUserRights");
$plugin_author = "Fred Sondaar (fretzl)";
$plugin_category = gettext('Admin');
$plugin_version = '1.3';
$option_interface = 'hideUserRightsOptions';

zp_register_filter('admin_head', 'hideUserRights::customDisplayRights', 999);

class hideUserRightsOptions {

	function __construct() {
		purgeOption('all_rights');
		purgeOption('albums');
		purgeOption('pages');
		purgeOption('categories');
		purgeOption('languages');
		purgeOption('addressfields');
		purgeOption('quota');
		purgeOption('groups');
		purgeOption('notebox');
		setOptionDefault('hideuserrights-all_rights', 0);
		setOptionDefault('hideuserrights-managedalbums', 0);
		setOptionDefault('hideuserrights-managedpages', 0);
		setOptionDefault('hideuserrights-managedcategories', 0);
		setOptionDefault('hideuserrights-userinfo', 0);
		setOptionDefault('hideuserrights-languages', 0);
		setOptionDefault('hideuserrights-addressfields', 0);
		setOptionDefault('hideuserrights-quota', 0);
		setOptionDefault('hideuserrights-groups', 0);
		setOptionDefault('hideuserrights-notebox', 0);
	}

	function getOptionsSupported() {

		$options =  array(	gettext_pl('All rights', 'hideUserRights') => array(
										'key' => 'hideuserrights-all_rights',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext_pl('Rights (the part with all the checkboxes)', 'hideUserRights')),
						gettext_pl('Managed albums', 'hideUserRights') => array(
										'key' => 'hideuserrights-managedalbums',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext_pl('Managed albums', 'hideUserRights')),
						gettext_pl('Managed pages', 'hideUserRights') => array(
										'key' => 'hideuserrights-managedpages',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext_pl('Managed pages', 'hideUserRights')),
						gettext_pl('Managed categories', 'hideUserRights') => array(
										'key' => 'hideuserrights-managedcategories',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext_pl('Managed news categories', 'hideUserRights')),
						gettext_pl('Languages (Flags)', 'hideUserRights') => array(
										'key' => 'hideuserrights-languages',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext_pl('Languages (Flags)', 'hideUserRights')),
						gettext_pl('User info', 'hideUserRights') => array(
										'key' => 'hideuserrights-userinfo',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext_pl('User account info such as last login, etc.', 'hideUserRights')),
						gettext_pl('Address fields', 'hideUserRights') => array(
										'key' => 'hideuserrights-addressfields',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext_pl('User address fields (only if the <code>userAddressFields</code> plugin is enabled)', 'hideUserRights')),
						gettext_pl('Quota', 'hideUserRights') => array(
										'key' => 'hideuserrights-quota',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext_pl('Assigned quota (only if the <code>quota_manager</code> plugin is enabled)', 'hideUserRights')),
						gettext_pl('Group membership', 'hideUserRights') => array(
										'key' => 'hideuserrights-groups',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext_pl('User group membership information (only if the <code>user_groups</code> plugin is enabled).', 'hideUserRights')),
						gettext_pl('Noteboxes', 'hideUserRights') => array(
										'key' => 'hideuserrights-notebox',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext_pl('All Noteboxes', 'hideUserRights'))
		);
		$active_plugins = getEnabledPlugins();
		if (!array_key_exists("userAddressFields", $active_plugins)) {
			$options['hideuserrights-addressfields']['disabled'] = true;
				if (!isset($_POST['hideuserrights-addressfields'])) setOption('hideuserrights-addressfields', 0);
			}
		if (!array_key_exists("quota_manager", $active_plugins)) {
			$options['hideuserrights-quota']['disabled'] = true;
				if (!isset($_POST['hideuserrights-quota'])) setOption('hideuserrights-quota', 0);
			}
		if (!array_key_exists("user_groups", $active_plugins)) {
			$options['hideuserrights-groups']['disabled'] = true;
				if (!isset($_POST['hideuserrights-groups'])) setOption('hideuserrights-groups', 0);
			}

		return $options;
	}
}

class hideUserRights {

	static function customDisplayRights() {
		global $_zp_loggedin, $_zp_admin_current_page, $_zp_admin_tab;
		$active_plugins = getEnabledPlugins();
		if ($_zp_loggedin) {
			if (!zp_loggedin(ADMIN_RIGHTS) && $_zp_admin_current_page == 'users') {
				$user_config = '
				<script>
					document.addEventListener("DOMContentLoaded", function() {' . "\n";
					
					// start with aligning everything on top of the <td>
					$user_config .= 'document.querySelector(".box-rights").parentElement.style.cssText += "vertical-align: top; padding-top: 20px;";' . "\n";

					// Rights. (the part with all the checkboxes)
					if (getOption("hideuserrights-all_rights")) {
						$user_config .= 'document.querySelector(".box-rights").remove();' . "\n";
					}
					
					// Managed albums
					if (getOption("hideuserrights-managedalbums")) {
						$user_config .= 'const albumsbox = Array.prototype.slice.call(document.querySelectorAll("div.box-albums-unpadded")).filter(function (el) { return el.textContent.includes("Managed albums")})[0];' . "\n";
						$user_config .= 'albumsbox.remove();' . "\n";
						
					}
					
					// Managed pages
					if (getOption("hideuserrights-managedpages")) {
						$user_config .= 'const pagesbox = Array.prototype.slice.call(document.querySelectorAll("div.box-albums-unpadded")).filter(function (el) { return el.textContent.includes("Managed pages")})[0];' . "\n";
						$user_config .= 'pagesbox.remove();' . "\n";
					}
					
					// Managed news categories
					if (getOption("hideuserrights-managedcategories")) {
						$user_config .= 'const catsbox = Array.prototype.slice.call(document.querySelectorAll("div.box-albums-unpadded")).filter(function (el) { return el.textContent.includes("Managed news categories")})[0];' . "\n";
						$user_config .= 'catsbox.remove();' . "\n";
					}
					
					// Languages (Flags)
					if (getOption("hideuserrights-languages"))	{
						$user_config .= 'document.querySelector("label[for=\"admin_language_0\"]").remove();' . "\n";
						$user_config .= 'document.querySelector("ul.flags").remove();' . "\n";
					}
					
					// User info
					if (getOption("hideuserrights-userinfo"))	{
						$user_config .= 'document.querySelector("tr.userextrainfo td ul:not(.flags)").style.display = "none";' . "\n";
					}
					
					// Address fields (if the "userAddressFields" plugin is enabled)
					if (array_key_exists("userAddressFields", $active_plugins) && getOption("hideuserrights-addressfields"))  {
						$user_config .= 'const addressfield = Array.prototype.slice.call(document.querySelectorAll("tr.userextrainfo td:first-child fieldset")).filter(function (el) { return el.textContent.includes("Street")})[0];' . "\n";
						$user_config .= 'const addressrow = addressfield.closest("tr");' . "\n";
						$user_config .= 'addressrow.nextElementSibling.remove();' . "\n";// First sibling
						$user_config .= 'addressrow.nextElementSibling.remove();' . "\n";// Second sibling which actually has become the first sibling since the first sibling has just been removed...
						$user_config .= 'addressrow.remove();' . "\n";//Selected element itself
					}
					
					// Groups and quota
					$user_config .= 'const elements = document.querySelectorAll("tr.userextrainfo td:first-child");' . "\n";

					// "User group membership" information (if the "user_groups" plugin is enabled)
					if (array_key_exists("user_groups", $active_plugins) && getOption("hideuserrights-groups")) {
						$user_config .= 'for (el of elements) {if (el.textContent.indexOf("User") == 1) {el.parentElement.remove()}};' . "\n";
					}
					
					// Assigned quota (if the "quota_manager" plugin is enabled and user has "Upload" rights)
					if (array_key_exists("quota_manager", $active_plugins) && getOption("hideuserrights-quota")) {
						$user_config .= 'for (el of elements) {if (el.textContent.indexOf("Image") == 0) {el.parentElement.remove()}};' . "\n";
					}
					
					// All Noteboxes
					if (getOption("hideuserrights-notebox")) {
						$user_config .= 'const allnotes = document.getElementsByClassName("notebox");' . "\n";
						$user_config .= 'if (allnotes.length > 0) { while (allnotes[0]) { allnotes[0].remove(); } }' . "\n";
					}

					$user_config .= '
					});
				</script>';

				echo $user_config;
			}
		}
	}
}
?>
