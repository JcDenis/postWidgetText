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

require __DIR__ . '/_widgets.php';

# Admin menu
if (dcCore::app()->blog->settings->postwidgettext->postwidgettext_active) {
    dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
        __('Post widget text'),
        dcCore::app()->adminurl->get('admin.plugin.postWidgetText'),
        dcPage::getPF('postWidgetText/icon.png'),
        preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.postWidgetText')) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
        dcCore::app()->auth->check(dcAuth::PERMISSION_CONTENT_ADMIN, dcCore::app()->blog->id)
    );

    dcCore::app()->addBehavior('adminDashboardFavoritesV2', ['adminPostWidgetText', 'adminDashboardFavorites']);
}
# Pref
dcCore::app()->addBehavior('adminFiltersListsV2', ['adminPostWidgetText', 'adminFiltersLists']);
dcCore::app()->addBehavior('adminBlogPreferencesFormV2', ['adminPostWidgetText', 'adminBlogPreferencesForm']);
dcCore::app()->addBehavior('adminBeforeBlogSettingsUpdate', ['adminPostWidgetText', 'adminBeforeBlogSettingsUpdate']);

# Post
dcCore::app()->addBehavior('adminPostHeaders', ['adminPostWidgetText', 'adminPostHeaders']);
dcCore::app()->addBehavior('adminPostFormItems', ['adminPostWidgetText', 'adminPostFormItems']);
dcCore::app()->addBehavior('adminAfterPostUpdate', ['adminPostWidgetText', 'adminAfterPostSave']);
dcCore::app()->addBehavior('adminAfterPostCreate', ['adminPostWidgetText', 'adminAfterPostSave']);
dcCore::app()->addBehavior('adminBeforePostDelete', ['adminPostWidgetText', 'adminBeforePostDelete']);

# Plugin "pages"
dcCore::app()->addBehavior('adminPageHeaders', ['adminPostWidgetText', 'adminPostHeaders']);
dcCore::app()->addBehavior('adminPageFormItems', ['adminPostWidgetText', 'adminPostFormItems']);
dcCore::app()->addBehavior('adminAfterPageUpdate', ['adminPostWidgetText', 'adminAfterPostSave']);
dcCore::app()->addBehavior('adminAfterPageCreate', ['adminPostWidgetText', 'adminAfterPostSave']);
dcCore::app()->addBehavior('adminBeforePageDelete', ['adminPostWidgetText', 'adminBeforePostDelete']);

# Plugin "importExport"
if (dcCore::app()->blog->settings->postwidgettext->postwidgettext_importexport_active) {
    dcCore::app()->addBehavior('exportFull', ['adminPostWidgetText', 'exportFull']);
    dcCore::app()->addBehavior('exportSingle', ['adminPostWidgetText', 'exportSingle']);
    dcCore::app()->addBehavior('importInit', ['adminPostWidgetText', 'importInit']);
    dcCore::app()->addBehavior('importSingle', ['adminPostWidgetText', 'importSingle']);
    dcCore::app()->addBehavior('importFull', ['adminPostWidgetText', 'importFull']);
}
