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
        if (!empty($_POST['redir'])) {
            http::redirect($_POST['redir']);
        } else {
            $core->adminurl->redirect('admin.plugin.postWidgetText');
        }
    }
    catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
}

# filters
$filter = new adminGenericFilter($core, 'pwt');
$filter->add(dcAdminFilters::getPageFilter());
$params = $filter->params();

# Get posts with text widget
try {
    $posts = $pwt->getWidgets($params);
    $counter = $pwt->getWidgets($params, true);
    $posts_list = new postWidgetTextList($core, $posts, $counter->f(0));
}
catch (Exception $e) {
    $core->error->add($e->getMessage());
}

# Display
echo '
<html><head><title>' . __('Post widget text') . '</title>' .
dcPage::jsLoad(dcPage::getPF('postWidgetText/js/index.js')) .
$filter->js($core->adminurl->get('admin.plugin.postWidgetText')) . '
</head>
<body>' .

dcPage::breadcrumb([
    __('Plugins') => '',
    __('Posts widgets') => ''
]).
dcPage::notices();

$filter->display('admin.plugin.postWidgetText', form::hidden('p', 'postWidgetText'));

$posts_list->display($filter->page, $filter->nb,
    '<form action="' . $core->adminurl->get('admin.plugin.postWidgetText') . '" method="post" id="form-entries">' .
    '%s' .
    '<div class="two-cols">' .
    '<p class="col checkboxes-helpers"></p>' .
    '<p class="col right">' .
    '<input id="do-action" type="submit" name="save" value="' . __('Delete selected widgets') . '" /></p>' .
    $core->adminurl->getHiddenFormFields('admin.plugin.postWidgetText', array_merge(['p' =>  'postWidgetText'], $filter->values(true))) . 
    $core->formNonce() .
    '</div>' .
    '</form>'
);

# Footer
dcPage::helpBlock('postWidgetText');

echo '</body></html>';