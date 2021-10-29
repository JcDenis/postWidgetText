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

require dirname(__FILE__).'/_widgets.php';

# Admin menu
if ($core->blog->settings->postwidgettext->postwidgettext_active) {
    $_menu['Plugins']->addItem(
        __('Post widget text'),
        $core->adminurl->get('admin.plugin.postWidgetText'),
        dcPage::getPF('postWidgetText/icon.png'),
        preg_match('/' . preg_quote($core->adminurl->get('admin.plugin.postWidgetText')) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
        $core->auth->check('contentadmin', $core->blog->id)
    );

    $core->addBehavior('adminDashboardFavorites', ['postWidgetTextDashboard', 'favorites']);
}
# Pref
$core->addBehavior('adminFiltersLists', ['postWidgetTextAdmin', 'adminFiltersLists']);
# Post
$core->addBehavior('adminPostHeaders', ['postWidgetTextAdmin', 'headers']);
$core->addBehavior('adminPostFormItems', ['postWidgetTextAdmin', 'form']);
$core->addBehavior('adminAfterPostUpdate', ['postWidgetTextAdmin', 'save']);
$core->addBehavior('adminAfterPostCreate', ['postWidgetTextAdmin', 'save']);
$core->addBehavior('adminBeforePostDelete', ['postWidgetTextAdmin', 'delete']);

# Plugin "pages"
$core->addBehavior('adminPageHeaders', ['postWidgetTextAdmin', 'headers']);
$core->addBehavior('adminPageFormItems', ['postWidgetTextAdmin', 'form']);
$core->addBehavior('adminAfterPageUpdate', ['postWidgetTextAdmin', 'save']);
$core->addBehavior('adminAfterPageCreate', ['postWidgetTextAdmin', 'save']);
$core->addBehavior('adminBeforePageDelete', ['postWidgetTextAdmin', 'delete']);

# Plugin "importExport"
if ($core->blog->settings->postwidgettext->postwidgettext_importexport_active) {
    $core->addBehavior('exportFull', ['postWidgetTextBackup', 'exportFull']);
    $core->addBehavior('exportSingle', ['postWidgetTextBackup', 'exportSingle']);
    $core->addBehavior('importInit', ['postWidgetTextBackup', 'importInit']);
    $core->addBehavior('importSingle', ['postWidgetTextBackup', 'importSingle']);
    $core->addBehavior('importFull', ['postWidgetTextBackup', 'importFull']);
}