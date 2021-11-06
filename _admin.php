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

require dirname(__FILE__) . '/_widgets.php';

# Admin menu
if ($core->blog->settings->postwidgettext->postwidgettext_active) {
    $_menu['Plugins']->addItem(
        __('Post widget text'),
        $core->adminurl->get('admin.plugin.postWidgetText'),
        dcPage::getPF('postWidgetText/icon.png'),
        preg_match('/' . preg_quote($core->adminurl->get('admin.plugin.postWidgetText')) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
        $core->auth->check('contentadmin', $core->blog->id)
    );

    $core->addBehavior('adminDashboardFavorites', ['adminPostWidgetText', 'adminDashboardFavorites']);
}
# Pref
$core->addBehavior('adminFiltersLists', ['adminPostWidgetText', 'adminFiltersLists']);
$core->addBehavior('adminBlogPreferencesForm', ['adminPostWidgetText', 'adminBlogPreferencesForm']);
$core->addBehavior('adminBeforeBlogSettingsUpdate', ['adminPostWidgetText', 'adminBeforeBlogSettingsUpdate']);

# Post
$core->addBehavior('adminPostHeaders', ['adminPostWidgetText', 'adminPostHeaders']);
$core->addBehavior('adminPostFormItems', ['adminPostWidgetText', 'adminPostFormItems']);
$core->addBehavior('adminAfterPostUpdate', ['adminPostWidgetText', 'adminAfterPostSave']);
$core->addBehavior('adminAfterPostCreate', ['adminPostWidgetText', 'adminAfterPostSave']);
$core->addBehavior('adminBeforePostDelete', ['adminPostWidgetText', 'adminBeforePostDelete']);

# Plugin "pages"
$core->addBehavior('adminPageHeaders', ['adminPostWidgetText', 'adminPostHeaders']);
$core->addBehavior('adminPageFormItems', ['adminPostWidgetText', 'adminPostFormItems']);
$core->addBehavior('adminAfterPageUpdate', ['adminPostWidgetText', 'adminAfterPostSave']);
$core->addBehavior('adminAfterPageCreate', ['adminPostWidgetText', 'adminAfterPostSave']);
$core->addBehavior('adminBeforePageDelete', ['adminPostWidgetText', 'adminBeforePostDelete']);

# Plugin "importExport"
if ($core->blog->settings->postwidgettext->postwidgettext_importexport_active) {
    $core->addBehavior('exportFull', ['adminPostWidgetText', 'exportFull']);
    $core->addBehavior('exportSingle', ['adminPostWidgetText', 'exportSingle']);
    $core->addBehavior('importInit', ['adminPostWidgetText', 'importInit']);
    $core->addBehavior('importSingle', ['adminPostWidgetText', 'importSingle']);
    $core->addBehavior('importFull', ['adminPostWidgetText', 'importFull']);
}
