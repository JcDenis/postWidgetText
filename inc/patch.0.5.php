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

$records = $core->con->select(
	'SELECT W.*, P.post_lang, P.post_format FROM '.$core->prefix.'post_wtext W '.
	'LEFT JOIN '.$core->prefix.'post P ON P.post_id=W.post_id '
);
if (!$records->isEmpty())
{
	$cur = $core->con->openCursor($core->prefix.'post_option');
	while ($records->fetch())
	{
		$core->con->writeLock($core->prefix.'post_option');
		try {

			$id = $core->con->select(
				'SELECT MAX(option_id) FROM '.$core->prefix.'post_option'
			)->f(0) + 1;

			$cur->clean();
			$cur->option_creadt = date('Y-m-d H:i:s');
			$cur->option_upddt = date('Y-m-d H:i:s');

			$cur->option_id = $id;
			$cur->post_id = $records->post_id;
			$cur->option_type =  $records->wtext_type;
			$cur->option_lang = $records->post_lang;
			$cur->option_format = $records->post_format;
			$cur->option_title =  $records->wtext_title;
			$cur->option_content =  $records->wtext_content;
			$cur->option_content_xhtml =  $records->wtext_content_xhtml;

			$cur->insert();
			$core->con->unlock();
		}
		catch (Exception $e)
		{
			$core->con->unlock();
			throw $e;
		}
	}
}
?>