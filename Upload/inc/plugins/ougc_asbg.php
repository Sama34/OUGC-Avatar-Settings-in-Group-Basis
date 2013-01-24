<?php

/***************************************************************************
 *
 *   OUGC Avatar Settings in Group Basis plugin ()
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://community.mybb.com/user-25096.html
 *
 *   This plugin will add two new user group settings for avatar control.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// Run the ACP hooks.
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('admin_formcontainer_end', 'ougc_asbg_usergroup_permission');
	$plugins->add_hook('admin_user_groups_edit_commit', 'ougc_asbg_usergroup_permission_commit');
}
// Run the UCP hooks.
else
{
	$plugins->add_hook('usercp_avatar_start', 'ougc_asbg_run');
	$plugins->add_hook('usercp_do_avatar_start', 'ougc_asbg_run');
}

// Necessary plugin information for the ACP plugin manager.
function ougc_asbg_info()
{
	global $lang;
	$lang->load('ougc_asbg');

	return array(
		'name'			=> 'OUGC Avatar Settings in Group Basis',
		'description'	=> $lang->ougc_asbg_plugin_d,
		'website'		=> 'http://udezain.com.ar/',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://udezain.com.ar/',
		'version'		=> '1.0',
		'compatibility'	=> '16*',
		'guid'			=> '0ab5f373eea869dd95cc73a63b6d4737'
	);
}

// Install the plugin.
function ougc_asbg_install()
{
	global $db, $cache, $settings;
	ougc_asbg_uninstall(false);

	// Insert our two columns
	$db->add_column('usergroups', 'avatarsize', 'text NOT NULL');
	$db->add_column('usergroups', 'avatardims', 'text NOT NULL');

	// Update the usergroups cache
	$cache->update_usergroups();
}

// Is this plugin installed?
function ougc_asbg_is_installed()
{
	global $db;

	return ($db->field_exists('avatarsize', 'usergroups'));
}

// Uninstall the plugin.
function ougc_asbg_uninstall($update_usergroups=true)
{
	global $db, $cache;

	// Drop our columns
	if($db->field_exists('avatarsize', 'usergroups'))
	{
		$db->drop_column('usergroups', 'avatarsize');
	}
	if($db->field_exists('avatardims', 'usergroups'))
	{
		$db->drop_column('usergroups', 'avatardims');
	}

	// Update the cache (only if uninstalling)
	if($update_usergroups)
	{
		$cache->update_usergroups();
	}
}

// Insert the require code in the group edit page.
function ougc_asbg_usergroup_permission()
{
	global $run_module, $form_container, $lang, $form, $mybb;

	if($run_module == 'user' && !empty($form_container->_title) && !empty($lang->users_permissions) && $form_container->_title == $lang->users_permissions)
	{
		global $form, $mybb;
		$lang->load('ougc_asbg');

		$account_options = array(
			$lang->ougc_asbg_avatarsettings_dim.'<br />'.$form->generate_text_box('avatardims', $mybb->input['avatardims'], array('id' => 'avatardims')),
			$lang->ougc_asbg_avatarsettings_size.'<br />'.$form->generate_text_box('avatarsize', $mybb->input['avatarsize'], array('id' => 'avatarsize'))
		);
		$form_container->output_row($lang->ougc_asbg_avatarsettings, '', '<div class="group_settings_bit">'.implode('</div><div class="group_settings_bit">', $account_options).'</div>');
	}
}

// Save the data.
function ougc_asbg_usergroup_permission_commit()
{
	global $updated_group, $mybb;

	$updated_group = array_merge($updated_group, array('avatardims' => $mybb->input['avatardims'], 'avatarsize' => $mybb->input['avatarsize']));
}

// Modify the settings for users editing their signatures.
function ougc_asbg_run()
{
	global $mybb, $groupscache;

	// Dimensions
	if(
	$groupscache[$mybb->user['usergroup']]['avatardims'] == '-1' ||
	$groupscache[$mybb->user['displaygroup']]['avatardims'] == '-1'
	)
	{
		$mybb->usergroup['avatardims'] = '';
	}
	$mybb->settings['maxavatardims'] = $mybb->usergroup['avatardims'];

	// Size
	if(
	$groupscache[$mybb->user['usergroup']]['avatarsize'] == '-1' ||
	$groupscache[$mybb->user['displaygroup']]['avatarsize'] == '-1'
	)
	{
		$mybb->usergroup['avatarsize'] = '';
	}
	$mybb->settings['avatarsize'] = $mybb->usergroup['avatarsize'];
}