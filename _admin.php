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

require dirname(__FILE__).'/_widgets.php';

# Admin menu
if ($core->blog->settings->postwidgettext->postwidgettext_active) {
	$_menu['Plugins']->addItem(
		__('Post widget text'),
		'plugin.php?p=postWidgetText',
		'index.php?pf=postWidgetText/icon.png',
		preg_match(
			'/plugin.php\?p=postWidgetText(&.*)?$/',
			$_SERVER['REQUEST_URI']),
		$core->auth->check('contentadmin', $core->blog->id)
	);

	$core->addBehavior(
		'adminDashboardFavorites',
		array('postWidgetTextDashboard', 'favorites')
	);
}
# Post
$core->addBehavior(
	'adminPostHeaders',
	array('postWidgetTextAdmin', 'headers'));
$core->addBehavior(
	'adminPostFormItems',
	array('postWidgetTextAdmin', 'form'));
$core->addBehavior(
	'adminAfterPostUpdate',
	array('postWidgetTextAdmin', 'save'));
$core->addBehavior(
	'adminAfterPostCreate',
	array('postWidgetTextAdmin', 'save'));
$core->addBehavior(
	'adminBeforePostDelete',
	array('postWidgetTextAdmin', 'delete'));

# Plugin "pages"
$core->addBehavior(
	'adminPageHeaders',
	array('postWidgetTextAdmin', 'headers'));
$core->addBehavior(
	'adminPageFormItems',
	array('postWidgetTextAdmin', 'form'));
$core->addBehavior(
	'adminAfterPageUpdate',
	array('postWidgetTextAdmin', 'save'));
$core->addBehavior(
	'adminAfterPageCreate',
	array('postWidgetTextAdmin', 'save'));
$core->addBehavior(
	'adminBeforePageDelete',
	array('postWidgetTextAdmin', 'delete'));

# Plugin "importExport"
if ($core->blog->settings->postwidgettext->postwidgettext_importexport_active) {
	$core->addBehavior(
		'exportFull',
		array('postWidgetTextBackup', 'exportFull')
	);
	$core->addBehavior(
		'exportSingle',
		array('postWidgetTextBackup', 'exportSingle')
	);
	$core->addBehavior(
		'importInit',
		array('postWidgetTextBackup', 'importInit')
	);
	$core->addBehavior(
		'importSingle',
		array('postWidgetTextBackup', 'importSingle')
	);
	$core->addBehavior(
		'importFull',
		array('postWidgetTextBackup', 'importFull')
	);
}
