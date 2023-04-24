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

use ArrayObject;
use dcCore;
use dcFavorites;
use dcPage;
use dcSettings;
use Dotclear\Database\{
    Cursor,
    MetaRecord
};
use Dotclear\Helper\Html\Html;

use form;

/**
 * Backend behaviors.
 */
class BackendBehaviors
{
    public static function sortbyCombo(): array
    {
        return [
            __('Post title')   => 'post_title',
            __('Post date')    => 'post_dt',
            __('Widget title') => 'option_title',
            __('Widget date')  => 'option_upddt',
        ];
    }

    public static function adminFiltersListsV2(ArrayObject $sorts): void
    {
        $sorts['pwt'] = [
            __('Post widget text'),
            self::sortbyCombo(),
            'post_dt',
            'desc',
            [__('entries per page'), 20],
        ];
    }

    public static function adminBlogPreferencesFormV2(dcSettings $blog_settings): void
    {
        echo '
        <div class="fieldset">
        <h4 id="pwt_params">' . __('Post widget text') . '</h4>
        <div class="two-cols">
        <div class="col">
        <p><label for="active">' .
        form::checkbox('active', 1, (bool) $blog_settings->get(My::id())->get('active')) .
        __('Enable post widget text on this blog') . '</label></p>
        </div>
        <div class="col">
        <p><label for="importexport_active">' .
        form::checkbox('importexport_active', 1, (bool) $blog_settings->get(My::id())->get('importexport_active')) .
        __('Enable import/export behaviors') . '</label></p>
        </div>
        </div>
        <br class="clear" />
        </div>';
    }

    public static function adminBeforeBlogSettingsUpdate(dcSettings $blog_settings): void
    {
        $blog_settings->get(My::id())->put('active', !empty($_POST['active']));
        $blog_settings->get(My::id())->put('importexport_active', !empty($_POST['importexport_active']));
    }

    public static function adminDashboardFavoritesV2(dcFavorites $favs): void
    {
        if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->adminurl)) {
            return;
        }

        $favs->register(My::id(), [
            'title'       => __('Post widget text'),
            'url'         => dcCore::app()->adminurl->get('admin.plugin.' . My::id()),
            'small-icon'  => dcPage::getPF(My::id() . '/icon.svg'),
            'large-icon'  => dcPage::getPF(My::id() . '/icon.svg'),
            'permissions' => dcCore::app()->auth->makePermissions([
                dcCore::app()->auth::PERMISSION_USAGE,
                dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
            ]),
        ]);
    }

    public static function adminPostHeaders(): string
    {
        if (is_null(dcCore::app()->auth)) {
            return '';
        }

        $editor = dcCore::app()->auth->getOption('editor');

        return
            dcCore::app()->callBehavior('adminPostEditor', $editor['xhtml'], 'pwt', ['#post_wtext'], 'xhtml') .
            dcPage::jsModuleLoad(My::id() . '/js/backend.js');
    }

    public static function adminPostFormItems(ArrayObject $main, ArrayObject $sidebar, ?MetaRecord $post): void
    {
        # _POST fields
        $title   = $_POST['post_wtitle'] ?? '';
        $content = $_POST['post_wtext']  ?? '';

        # Existing post
        if (!is_null($post)) {
            $post_id = (int) $post->f('post_id');

            $w = Utils::getWidgets(['post_id' => $post_id]);

            # Existing widget
            if (!$w->isEmpty()) {
                $title   = $w->f('option_title');
                $content = $w->f('option_content');
            }
        }

        $main['post_widget'] = '<div id="post-wtext-form">' .
        '<h4>' . __('Additional widget') . '</h4>' .

        '<p class="col">' .
        '<label class="bold" for="post_wtitle">' . __('Widget title:') . '</label>' .
        form::field('post_wtitle', 20, 255, Html::escapeHTML($title), 'maximal') .
        '</p>' .

        '<p class="area" id="post-wtext">' .
        '<label class="bold" for="post_wtext">' . __('Wigdet text:') . '</label>' .
        form::textarea('post_wtext', 50, 5, Html::escapeHTML($content)) .
        '</p>' .

        '</div>';
    }

    public static function adminAfterPostSave(Cursor $cur, int $post_id): void
    {
        # _POST fields
        $title   = $_POST['post_wtitle'] ?? '';
        $content = $_POST['post_wtext']  ?? '';

        # Get existing widget
        $w = Utils::getWidgets(['post_id' => (int) $post_id]);

        # If new content is empty, delete old existing widget
        if (empty($title) && empty($content) && !$w->isEmpty()) {
            Utils::delWidget((int) $w->f('option_id'));
        }

        # If new content is not empty
        if (!empty($title) || !empty($content)) {
            $wcur = Utils::openCursor();
            $wcur->setField('post_id', (int) $post_id);
            $wcur->setField('option_type', My::id());
            $wcur->setField('option_lang', $cur->getField('post_lang'));
            $wcur->setField('option_format', $cur->getField('post_format'));
            $wcur->setField('option_title', $title);
            $wcur->setField('option_content', $content);

            # Create widget
            if ($w->isEmpty()) {
                $id = Utils::addWidget($wcur);
            }
            # Upddate widget
            else {
                Utils::updWidget($w->f('option_id'), $wcur);
            }
        }
    }

    public static function adminBeforePostDelete(int $post_id): void
    {
        # Get existing widget
        $w = Utils::getWidgets(['post_id' => (int) $post_id]);

        # If new content is empty, delete old existing widget
        if (!$w->isEmpty()) {
            Utils::delWidget($w->f('option_id'));
        }
    }
}
