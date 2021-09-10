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

if (!defined('DC_RC_PATH')) {

	return null;
}

$__autoload['postWidgetText'] = 
	dirname(__FILE__).'/inc/class.postwidgettext.php';
$__autoload['postWidgetTextDashboard'] = 
	dirname(__FILE__).'/inc/lib.pwt.dashboard.php';
$__autoload['postWidgetTextAdmin'] = 
	dirname(__FILE__).'/inc/lib.pwt.admin.php';
$__autoload['postWidgetTextBackup'] = 
	dirname(__FILE__).'/inc/lib.pwt.backup.php';
$__autoload['postWidgetTextList'] = 
	dirname(__FILE__).'/inc/lib.pwt.list.php';
