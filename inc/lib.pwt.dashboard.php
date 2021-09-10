<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of postWidgetText, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) {

	return null;
}

/**
 * @ingroup DC_PLUGIN_POSTWIDGETTEXT
 * @brief postWidgetText - admin dashboard methods.
 * @since 2.6
 */
class postWidgetTextDashboard
{
	/**
	 * Favorites.
	 *
	 * @param	dcCore      $core dcCore instance
	 * @param	arrayObject $favs Array of favorites
	 */
	public static function favorites(dcCore $core, $favs)
	{
		$favs->register('postWidgetText', array(
			'title'		=> __('Post widget text'),
			'url'		=> 'plugin.php?p=postWidgetText',
			'small-icon'	=> 'index.php?pf=postWidgetText/icon.png',
			'large-icon'	=> 'index.php?pf=postWidgetText/icon-big.png',
			'permissions'	=> $core->auth->check(
				'usage,contentadmin',
				$core->blog->id
			),
			'active_cb'	=> array(
				'postWidgetTextDashboard', 
				'active'
			)
		));
	}

	/**
	 * Favorites selection.
	 *
	 * @param	string $request Requested page
	 * @param	array  $params  Requested parameters
	 */
	public static function active($request, $params)
	{
		return $request == 'plugin.php' 
			&& isset($params['p']) 
			&& $params['p'] == 'postWidgetText';
	}
}