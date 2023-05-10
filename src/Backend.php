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
declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use dcAdmin;
use dcCore;
use dcPage;
use dcNsProcess;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && !is_null(dcCore::app()->auth) && !is_null(dcCore::app()->blog)
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcCore::app()->auth::PERMISSION_USAGE,
                dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
            ]), dcCore::app()->blog->id);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        // nullsafe
        if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return false;
        }

        // backend sidebar menu icon
        if (Utils::isActive()) {
            dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
                My::name(),
                dcCore::app()->adminurl->get('admin.plugin.' . My::id()),
                dcPage::getPF(My::id() . '/icon.svg'),
                preg_match('/' . preg_quote((string) dcCore::app()->adminurl->get('admin.plugin.' . My::id())) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
                dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcCore::app()->auth::PERMISSION_CONTENT_ADMIN]), dcCore::app()->blog->id)
            );
            // backend user dashboard favorites icon
            dcCore::app()->addBehavior('adminDashboardFavoritesV2', [BackendBehaviors::class, 'adminDashboardFavoritesV2']);
        }

        // backend pwt management
        dcCore::app()->addBehaviors([
            // user pref
            'adminFiltersListsV2'           => [BackendBehaviors::class, 'adminFiltersListsV2'],
            'adminColumnsListsV2'           => [BackendBehaviors::class, 'adminColumnsListsV2'],
            'adminBlogPreferencesFormV2'    => [BackendBehaviors::class, 'adminBlogPreferencesFormV2'],
            'adminBeforeBlogSettingsUpdate' => [BackendBehaviors::class, 'adminBeforeBlogSettingsUpdate'],
            // post
            'adminPostHeaders'      => [BackendBehaviors::class, 'adminPostHeaders'],
            'adminPostEditorTags'   => [BackendBehaviors::class, 'adminPostEditorTags'],
            'adminPostFormItems'    => [BackendBehaviors::class, 'adminPostFormItems'],
            'adminAfterPostUpdate'  => [BackendBehaviors::class, 'adminAfterPostSave'],
            'adminAfterPostCreate'  => [BackendBehaviors::class, 'adminAfterPostSave'],
            'adminBeforePostDelete' => [BackendBehaviors::class, 'adminBeforePostDelete'],
            // Plugin "pages"
            'adminPageHeaders'      => [BackendBehaviors::class, 'adminPostHeaders'],
            'adminPageFormItems'    => [BackendBehaviors::class, 'adminPostFormItems'],
            'adminAfterPageUpdate'  => [BackendBehaviors::class, 'adminAfterPostSave'],
            'adminAfterPageCreate'  => [BackendBehaviors::class, 'adminAfterPostSave'],
            'adminBeforePageDelete' => [BackendBehaviors::class, 'adminBeforePostDelete'],
            // widgets registration
            'initWidgets' => [Widgets::class, 'initWidgets'],
        ]);

        // add plugin "importExport" features
        if (!is_null(dcCore::app()->blog) && dcCore::app()->blog->settings->get(My::id())->get('importexport_active')) {
            dcCore::app()->addBehaviors([
                'exportFullV2'   => [ImportExport::class, 'exportFullV2'],
                'exportSingleV2' => [ImportExport::class, 'exportSingleV2'],
                'importInitV2'   => [ImportExport::class, 'importInitV2'],
                'importSingleV2' => [ImportExport::class, 'importSingleV2'],
                'importFullV2'   => [ImportExport::class, 'importFullV2'],
            ]);
        }

        return true;
    }
}
