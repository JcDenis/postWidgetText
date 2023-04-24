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

// Admin menu
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
dcCore::app()->addBehaviors([
    // Pref
    'adminFiltersListsV2'           => ['adminPostWidgetText', 'adminFiltersListsV2'],
    'adminBlogPreferencesFormV2'    => ['adminPostWidgetText', 'adminBlogPreferencesFormV2'],
    'adminBeforeBlogSettingsUpdate' => ['adminPostWidgetText', 'adminBeforeBlogSettingsUpdate'],
    // Post
    'adminPostHeaders'              => ['adminPostWidgetText', 'adminPostHeaders'],
    'adminPostFormItems'            => ['adminPostWidgetText', 'adminPostFormItems'],
    'adminAfterPostUpdate'          => ['adminPostWidgetText', 'adminAfterPostSave'],
    'adminAfterPostCreate'          => ['adminPostWidgetText', 'adminAfterPostSave'],
    'adminBeforePostDelete'         => ['adminPostWidgetText', 'adminBeforePostDelete'],
    // Plugin "pages"
    'adminPageHeaders'              => ['adminPostWidgetText', 'adminPostHeaders'],
    'adminPageFormItems'            => ['adminPostWidgetText', 'adminPostFormItems'],
    'adminAfterPageUpdate'          => ['adminPostWidgetText', 'adminAfterPostSave'],
    'adminAfterPageCreate'          => ['adminPostWidgetText', 'adminAfterPostSave'],
    'adminBeforePageDelete'         => ['adminPostWidgetText', 'adminBeforePostDelete'],
]);

// Plugin "importExport"
if (dcCore::app()->blog->settings->get(basename(__DIR__))->get('importexport_active')) {
    dcCore::app()->addBehaviors([
        'exportFullV2'   => ['adminPostWidgetText', 'exportFullV2'],
        'exportSingleV2' => ['adminPostWidgetText', 'exportSingleV2'],
        'importInitV2'   => ['adminPostWidgetText', 'importInitV2'],
        'importSingleV2' => ['adminPostWidgetText', 'importSingleV2'],
        'importFullV2'   => ['adminPostWidgetText', 'importFullV2'],
    ]);
}
