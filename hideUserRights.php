<?php
/**
 * Plugin to hide the display of user rights and other info for users that do NOT have ADMIN_RIGHTS.
 *
 * @author Fred Sondaar (fretzl)
 * @package plugins
 * @subpackage admin
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext_pl("Hide the display of user rights and other info if a user does NOT have ADMIN_RIGHTS.", "hideUserRights");
$plugin_author = "Fred Sondaar (fretzl)";
$plugin_version = '1.0';

$option_interface = 'hideUserRights';

zp_register_filter('admin_head', 'hideUserRights::customDisplayRights');

class hideUserRights {

	function __construct() {
		setOptionDefault('all_rights', 0);
		setOptionDefault('albums', 0);
		setOptionDefault('pages', 0);
		setOptionDefault('categories', 0);
		//setOptionDefault('albums_pages_news', 0);
		setOptionDefault('notebox', 0);
		setOptionDefault('languages', 0);
		setOptionDefault('quota', 0);
		setOptionDefault('groups', 0);
	}

	function getOptionsSupported() {

		$options =  array(	gettext('All rights') => array(
										'key' => 'all_rights',
										'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 1,
										'desc' => gettext('Rights. (the part with all the checkboxes)')),
						gettext('Albums') => array(
										'key' => 'albums',
										'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 2,
										'desc' => gettext('Managed albums')),
						gettext('Pages') => array(
										'key' => 'pages',
										'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 3,
										'desc' => gettext('Managed pages')),
						gettext('Categories') => array(
										'key' => 'categories',
										'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 4,
										'desc' => gettext('Managed news categories')),
						/*
						gettext('Albums, Pages and Categories') => array(
										'key' => 'albums_pages_cats',
										'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext('Albums, Pages and Categories')),
						*/
						gettext('Languages (Flags)') => array(
										'key' => 'languages',
										'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 5,
										'desc' => gettext('Languages (Flags)')),
						gettext('Address fields') => array(
										'key' => 'addressfields',
										'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 6,
										'desc' => gettext('User address fields (only if the <code>userAddressFields</code> plugin is enabled)')),
						gettext('Quota') => array(
										'key' => 'quota',
										'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 7,
										'desc' => gettext('Assigned quota (only if the <code>quota_manager</code> plugin is enabled)')),
						gettext('Groups') => array(
										'key' => 'groups',
										'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 8,
										'desc' => gettext('User group membership information (only if the <code>user_groups</code> plugin is enabled).')),
						gettext('All Noteboxes') => array(
										'key' => 'notebox',
										'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 9,
										'desc' => gettext('All Noteboxes'))
		);
		$active_plugins = getEnabledPlugins();
		if (!array_key_exists("userAddressFields", $active_plugins)) {
			$options[gettext('Address fields')]['disabled'] = true;
				if (!isset($_POST['addressfields'])) setOption('addressfields', 0);
			}
		if (!array_key_exists("quota_manager", $active_plugins)) {
			$options[gettext('Quota')]['disabled'] = true;
				if (!isset($_POST['quota'])) setOption('quota', 0);
			}
		if (!array_key_exists("user_groups", $active_plugins)) {
			$options[gettext('Groups')]['disabled'] = true;
				if (!isset($_POST['groups'])) setOption('groups', 0);
			}

		return $options;
	}

	static function customDisplayRights() {
		global $_zp_admin_tab;
		$active_plugins = getEnabledPlugins();
		if (!zp_loggedin(ADMIN_RIGHTS) && $_zp_admin_tab == 'users') {
			$user_config_add = '';
			$user_config = '
			<script type="text/javascript">
			// <!-- <![CDATA[
					$(document).ready(function(){';
						// start with aligning everything on top of the <td>
						$user_config_add .= '$(".box-rights").parent("td").css({"vertical-align":"top","padding-top":"20px"});';

					if (getOption("all_rights"))	// Rights. (the part with all the checkboxes).
						$user_config_add .= '$(".box-rights").hide();';

					if (getOption("albums"))		// Managed albums
						$user_config_add .= '$(".box-albums-unpadded:eq(0)").hide();';

					if (getOption("pages"))			// Managed pages
						$user_config_add .= '$(".box-albums-unpadded:eq(1)").hide();';

					if (getOption("categories"))	// Managed news categories
						$user_config_add .= '$(".box-albums-unpadded:eq(2)").hide();';

					/*
					if (getOption("albums_pages_cats"))	// Albums, Pages, and Categories.
						$user_config_add .= '$(".box-albums-unpadded").remove();';
					*/

					if (getOption("notebox"))		// All Noteboxes
						$user_config_add .= '$(".notebox").hide();';

					if (getOption("languages"))		// Languages (Flags)
						$user_config_add .= '$("label[for=\'admin_language_0\'], ul.flags").hide();';

					if (array_key_exists("userAddressFields", $active_plugins) && getOption("addressfields"))  // Address fields (if the "userAddressFields" plugin is enabled).
						$user_config_add .= '$("td:contains(' . gettext("Street") . '), td:contains(' . gettext("City") . '), td:contains(' . gettext("State") . ')").parent("tr.userextrainfo").hide();';

					if (array_key_exists("quota_manager", $active_plugins) && getOption("quota"))  // Assigned quota (if the "quota_manager" plugin is enabled).
						$user_config_add .= '$("td:contains(' . gettext("Image storage quota") . ')").parent("tr.userextrainfo").hide();';

					if (array_key_exists("user_groups", $active_plugins) && getOption("groups"))  // "User group membership" information (if the "user_groups" plugin is enabled).
						$user_config_add .= '$("tr.userextrainfo td:contains(' . gettext("User group membership") . ')").next().andSelf().hide();';


				$user_config_add .= '
				});
			// ]]> -->
			</script>';

			$user_config = $user_config.$user_config_add;

			echo $user_config;
		}
	}
}

?>
