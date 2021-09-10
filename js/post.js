/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of postWidgetText, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2010 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

$(function(){
	if(!document.getElementById){
		return;
	}
	if(document.getElementById('edit-entry')){
		var formatAddField=$('#post_format').get(0);
		$(formatAddField).change(function(){
			postWidgetTextTb.switchMode(this.value);
		});
		var postWidgetTextTb=new jsToolBar(document.getElementById('post_wtext'));
		postWidgetTextTb.context='post';
	}
/*
	$('#edit-entry').onetabload(function(){
		$('#post-wtext-form p#post-wtext-head').toggleWithLegend(
			$('#post-wtext-form').children().not('p#post-wtext-head'),
			{
				fn:function(){postWidgetTextTb.switchMode(formatAddField.value);},
				cookie:'dcx_post_wtext',
				hide:$('#post-wtext-form').val()==''
			}
		);
	});
*/
	$('#post-wtext-form h4').toggleWithLegend(
		$('#post-wtext-form').children().not('h4'),
		{
			fn:function(){postWidgetTextTb.switchMode(formatAddField.value);},
			cookie:'dcx_zcfs_admin_form_sidebar',
			legend_click:true
		}
	);
});