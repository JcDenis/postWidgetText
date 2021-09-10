<?php
/**
 * @brief postWidgetText, a plugin for Dotclear 2
 * 
 * @package Dotclear
 * @subpackage Plugin
 * 
 * @author Jean-Christian Denis and Contributors
 * 
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('DC_CONTEXT_ADMIN')) {

    return null;
}

dcPage::check('usage,contentadmin');

$pwt = new postWidgetText($core);

# Delete widgets
if (!empty($_POST['save']) && !empty($_POST['widgets'])) {
    try {
        foreach($_POST['widgets'] as $k => $id) {
            $id = (integer) $id;
            $pwt->delWidget($id);
        }

        dcPage::addSuccessNotice(
            __('Posts widgets successfully delete.')
        );
        http::redirect(
            $p_url
        );
    }
    catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
}

# Combos
$sortby_combo = array(
    __('Post title')    => 'post_title',
    __('Post date')    => 'post_dt',
    __('Widget title')    => 'option_title',
    __('Widget date')    => 'option_upddt',
);

$order_combo = array(
    __('Descending')    => 'desc',
    __('Ascending')    => 'asc'
);

# Filters
$show_filters = false;
$nb_per_page =  1;

$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'post_dt';
$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';
$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
    if ($nb_per_page != $_GET['nb']) {
        $show_filters = true;
    }
    $nb_per_page = (integer) $_GET['nb'];
}
$params['limit'] = array((($page-1)*$nb_per_page), $nb_per_page);

if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
    if ($order !== '' && in_array($order,$order_combo)) {
        $params['order'] = $sortby.' '.$order;
    }
    if ($sortby != 'post_dt' || $order != 'desc') {
        $show_filters = true;
    }
}

# Get posts with text widget
try {
    $posts = $pwt->getWidgets($params);
    $counter = $pwt->getWidgets($params, true);
    $posts_list = new postWidgetTextList(
        $core,
        $posts,
        $counter->f(0)
    );
}
catch (Exception $e) {
    $core->error->add($e->getMessage());
}

# Display
echo '
<html><head><title>'.__('Post widget text').'</title>'.
dcPage::jsLoad(
    'js/filter-controls.js'
).
'<script type="text/javascript">'."\n".
"//<![CDATA["."\n".
dcPage::jsVar(
    'dotclear.msg.show_filters',
    $show_filters ? 'true':'false'
)."\n".
dcPage::jsVar(
    'dotclear.msg.filter_posts_list',
    __('Show filters and display options')
)."\n".
dcPage::jsVar(
    'dotclear.msg.cancel_the_filter',
    __('Cancel filters and display options')
)."\n".
"//]]>\n".
"</script>\n".'
</head>
<body>'.

dcPage::breadcrumb(
    array(
        html::escapeHTML($core->blog->name) => '',
        __('Posts widgets') => ''
    )
).
dcPage::notices().'

<form action="'.$p_url.'" method="get" id="filters-form">
<h3 class="out-of-screen-if-js">'.__('Show filters and display options').'</h3>

<div class="table">
<div class="cell">
<p><label for="sortby" class="ib">'.__('Order by:').'</label> '.
form::combo('sortby', $combo_sortby, $sortby).'</p>
</div>
<div class="cell">
<p><label for="order" class="ib">'.__('Sort:').'</label> '.
form::combo('order', $combo_order, $order).'</p>
</div>
<div class="cell">
<p><span class="label ib">'.__('Show').'</span> <label for="nb" class="classic">'.
form::field('nb', 3, 3, $nb_per_page).' '.
__('entries per page').'</label></p>
</div>
</div>

<p><input type="submit" value="'.__('Apply filters and display options').'" />'.
form::hidden(array('p'), 'postWidgetText').'
<br class="clear" /></p>
</form>'.

$posts_list->display($page, $nb_per_page,
    '<form action="'.$p_url.'" method="post" id="form-periods">'.
    '%s'.
    '<div class="two-cols">'.
    '<p class="col checkboxes-helpers"></p>'.
    '<p class="col right">'.
    '<input type="submit" name="save" value="'.__('Delete selected widgets').'" /></p>'.
    form::hidden(array('sortby'), $sortby).
    form::hidden(array('order'), $order).
    form::hidden(array('page'), $page).
    form::hidden(array('nb'), $nb_per_page).
    form::hidden(array('p'), 'postWidgetText').
    $core->formNonce().
    '</div>'.
    '</form>'
);

# Footer
dcPage::helpBlock('postWidgetText');

echo 
'<hr class="clear"/><p class="right modules">
<a class="module-config" '.
'href="plugins.php?module=postWidgetText&amp;conf=1&amp;redir='.
urlencode('plugin.php?p=postWidgetText').'">'.__('Configuration').'</a> - 
postWidgetText - '.$core->plugins->moduleInfo('postWidgetText', 'version').'&nbsp;
<img alt="'.__('postWidgetText').'" src="index.php?pf=postWidgetText/icon.png" />
</p>
</body></html>';