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
 * @brief postWidgetText - admin post methods.
 * @since 2.6
 */
class postWidgetTextAdmin
{
	public static function headers()
	{
		return dcPage::jsLoad('index.php?pf=postWidgetText/js/post.js');
	}

	public static function form($main, $sidebar, $post)
	{
		# _POST fields
		$title = empty($_POST['post_wtitle']) ? '' : $_POST['post_wtitle'];
		$content = empty($_POST['post_wtext']) ? '' : $_POST['post_wtext'];

		# Existing post
		if ($post) {
			$post_id = (integer) $post->post_id;

			$pwt = new postWidgetText($GLOBALS['core']);
			$w = $pwt->getWidgets(array('post_id' => $post_id));

			# Existing widget
			if (!$w->isEmpty()) {
				$title = $w->option_title;
				$content = $w->option_content;
			}
		}

		$main['post_widget'] = 
		'<div id="post-wtext-form">'.
		'<h4>'.__('Additional widget').'</h4>'.

		'<p class="col">'.
		'<label class="bold" for="post_wtitle">'.__('Widget title:').'</label>'.
		form::field('post_wtitle',20,255,html::escapeHTML($title),'maximal').
		'</p>'.

		'<p class="area" id="post-wtext">'.
		'<label class="bold" for="post_wtext">'.__('Wigdet text:').'</label>'.
		form::textarea('post_wtext',50,5,html::escapeHTML($content)).
		'</p>'.

		'</div>';
	}

	public static function save($cur, $post_id)
	{
		$post_id = (integer) $post_id;

		# _POST fields
		$title = isset($_POST['post_wtitle']) && !empty($_POST['post_wtitle']) ? 
			$_POST['post_wtitle'] : '';
		$content = isset($_POST['post_wtext']) && !empty($_POST['post_wtext']) ? 
			$_POST['post_wtext'] : '';

		# Object
		$pwt = new postWidgetText($GLOBALS['core']);

		# Get existing widget
		$w = $pwt->getWidgets(array('post_id'=>$post_id));

		# If new content is empty, delete old existing widget
		if (empty($title) && empty($content) && !$w->isEmpty()) {
			$pwt->delWidget($w->option_id);
		}

		# If new content is not empty
		if (!empty($title) || !empty($content)) {
			$wcur = $pwt->openCursor();
			$wcur->post_id		= $post_id;
			$wcur->option_type		= 'postwidgettext';
			$wcur->option_lang		= $cur->post_lang;
			$wcur->option_format	= $cur->post_format;
			$wcur->option_title		= $title;
			$wcur->option_content	= $content;

			# Create widget
			if ($w->isEmpty()) {
				$id = $pwt->addWidget($wcur);
			}
			# Upddate widget
			else {
				$pwt->updWidget($w->option_id,$wcur);
			}
		}
	}
	
	public static function delete($post_id)
	{
		$post_id = (integer) $post_id;

		# Object
		$pwt = new postWidgetText($GLOBALS['core']);

		# Get existing widget
		$w = $pwt->getWidgets(array('post_id'=>$post_id));

		# If new content is empty, delete old existing widget
		if (!$w->isEmpty()) {
			$pwt->delWidget($w->option_id);
		}
	}
}
