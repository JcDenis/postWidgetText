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
if (dcCore::app()->blog->settings->get(basename(__DIR__))->get('active')) {
    dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
        __('Post widget text'),
        dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
        dcPage::getPF(basename(__DIR__) . '/icon.svg'),
        preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__))) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
        dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_CONTENT_ADMIN]), dcCore::app()->blog->id)
    );

    dcCore::app()->addBehavior('adminDashboardFavoritesV2', ['adminPostWidgetText', 'adminDashboardFavoritesV2']);
}
# Pref
dcCore::app()->addBehavior('adminFiltersListsV2', ['adminPostWidgetText', 'adminFiltersListsV2']);
dcCore::app()->addBehavior('adminBlogPreferencesFormV2', ['adminPostWidgetText', 'adminBlogPreferencesFormV2']);
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
if (dcCore::app()->blog->settings->get(basename(__DIR__))->get('importexport_active')) {
    dcCore::app()->addBehavior('exportFullV2', ['adminPostWidgetText', 'exportFullV2']);
    dcCore::app()->addBehavior('exportSingleV2', ['adminPostWidgetText', 'exportSingleV2']);
    dcCore::app()->addBehavior('importInitV2', ['adminPostWidgetText', 'importInitV2']);
    dcCore::app()->addBehavior('importSingleV2', ['adminPostWidgetText', 'importSingleV2']);
    dcCore::app()->addBehavior('importFullV2', ['adminPostWidgetText', 'importFullV2']);
}
