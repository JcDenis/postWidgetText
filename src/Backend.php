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

use dcCore;
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // backend sidebar menu icon
        if (Utils::isActive()) {
            My::addBackendMenuItem();
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
        if (!My::settings()->get('importexport_active')) {
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
