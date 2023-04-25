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

  $('#post_wtext').each(function () {
    if (typeof jsToolBar === 'function') {
      const tbWidgetText = new jsToolBar(this);
      tbWidgetText.context = 'pwt';
      tbWidgetText.draw('xhtml');
    }
  });
});
