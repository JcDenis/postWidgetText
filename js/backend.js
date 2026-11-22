/*global $, dotclear, jsToolBar */
'use strict';

$(() => {
  const title = document.querySelector('#post-wtext-form h4');
  if (title) {
    dotclear.toggleWithLegend(title, document.querySelectorAll('#post-wtext-form > :not(h4)'), {
      user_pref: 'dcx_pwt_form',
      legend_click: true,
    });
  }

  $('#post_wtext').each(function () {
    if (typeof jsToolBar === 'function') {
      const tbWidgetText = new jsToolBar(this);
      tbWidgetText.context = 'pwt';
      tbWidgetText.draw('xhtml');
    }
  });
});
