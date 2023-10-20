<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use Dotclear\App;
use Dotclear\Core\Process;

/**
 * @brief       postWidgetText backend class.
 * @ingroup     postWidgetText
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
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
        App::behavior()->addBehaviors([
            // user pref
            'adminFiltersListsV2'           => BackendBehaviors::adminFiltersListsV2(...),
            'adminColumnsListsV2'           => BackendBehaviors::adminColumnsListsV2(...),
            'adminBlogPreferencesFormV2'    => BackendBehaviors::adminBlogPreferencesFormV2(...),
            'adminBeforeBlogSettingsUpdate' => BackendBehaviors::adminBeforeBlogSettingsUpdate(...),
            // post
            'adminPostHeaders'      => BackendBehaviors::adminPostHeaders(...),
            'adminPostEditorTags'   => BackendBehaviors::adminPostEditorTags(...),
            'adminPostFormItems'    => BackendBehaviors::adminPostFormItems(...),
            'adminAfterPostUpdate'  => BackendBehaviors::adminAfterPostSave(...),
            'adminAfterPostCreate'  => BackendBehaviors::adminAfterPostSave(...),
            'adminBeforePostDelete' => BackendBehaviors::adminBeforePostDelete(...),
            // Plugin "pages"
            'adminPageHeaders'      => BackendBehaviors::adminPostHeaders(...),
            'adminPageFormItems'    => BackendBehaviors::adminPostFormItems(...),
            'adminAfterPageUpdate'  => BackendBehaviors::adminAfterPostSave(...),
            'adminAfterPageCreate'  => BackendBehaviors::adminAfterPostSave(...),
            'adminBeforePageDelete' => BackendBehaviors::adminBeforePostDelete(...),
            // widgets registration
            'initWidgets' => Widgets::initWidgets(...),
        ]);

        // add plugin "importExport" features
        if (!My::settings()->get('importexport_active')) {
            App::behavior()->addBehaviors([
                'exportFullV2'   => ImportExport::exportFullV2(...),
                'exportSingleV2' => ImportExport::exportSingleV2(...),
                'importInitV2'   => ImportExport::importInitV2(...),
                'importSingleV2' => ImportExport::importSingleV2(...),
                'importFullV2'   => ImportExport::importFullV2(...),
            ]);
        }

        return true;
    }
}
