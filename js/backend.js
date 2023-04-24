/*global $, dotclear, jsToolBar */
'use strict';

$(() => {
	$('#post-wtext-form h4').toggleWithLegend(
	  $('#post-wtext-form').children().not('h4'),
	  {
	    user_pref:'dcx_pwt_form',
	    legend_click:true
	  }
	);
	if ($.isFunction(jsToolBar)) {
	  $('#post_wtext').each(function() {
	    var tbWidgetText = new jsToolBar(this);
	    tbWidgetText.draw('xhtml');
	  });
	}
});
