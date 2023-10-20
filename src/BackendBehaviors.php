<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use ArrayObject;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Database\{
    Cursor,
    MetaRecord
};
use Dotclear\Helper\Html\Html;
use Dotclear\Interface\Core\BlogSettingsInterface;

use form;

/**
 * @brief       postWidgetText backend behaviors class.
 * @ingroup     postWidgetText
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class BackendBehaviors
{
    /**
     * Get list of sortable columns.
     *
     * @return  array   The combo
     */
    public static function sortbyCombo(): array
    {
        return [
            __('Post title')   => 'post_title',
            __('Post date')    => 'post_dt',
            __('Widget title') => 'option_title',
            __('Widget date')  => 'option_upddt',
        ];
    }

    /**
     * User pref widget text filters options.
     *
     * @param   ArrayObject     $sorts  Sort options
     */
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

    /**
     * User pref for widget text columns lists.
     *
     * @param   ArrayObject     $cols   Columns
     */
    public static function adminColumnsListsV2(ArrayObject $cols): void
    {
        $cols[My::id()] = [
            My::name(),
            [
                'post_dt'       => [true, __('Entry date')],
                'option_title'  => [true, __('Widget title')],
                'option_creadt' => [true, __('Widget date')],
                'user_id'       => [true, __('Author')],
                'post_type'     => [true, __('Entry type')],
            ],
        ];
    }

    /**
     * Add blog preferences form.
     *
     * @param   dcSettings  $blog_settings  The blog settings
     */
    public static function adminBlogPreferencesFormV2(BlogSettingsInterface $blog_settings): void
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

    /**
     * Save blog preference.
     *
     * @param   dcSettings  $blog_settings  The blog settings
     */
    public static function adminBeforeBlogSettingsUpdate(BlogSettingsInterface $blog_settings): void
    {
        $blog_settings->get(My::id())->put('active', !empty($_POST['active']));
        $blog_settings->get(My::id())->put('importexport_active', !empty($_POST['importexport_active']));
    }

    /**
     * Add user dashboard icon.
     *
     * @param   Favorites   $favs   The user favorites
     */
    public static function adminDashboardFavoritesV2(Favorites $favs): void
    {
        $favs->register(
            My::id(),
            [
                'title'      => My::name(),
                'url'        => My::manageUrl(),
                'small-icon' => My::icons(),
                'large-icon' => My::icons(),
                //'permissions' => null,
            ]
        );
    }

    /**
     * Add script to post edition page headers.
     *
     * @return  string  The HTML header content
     */
    public static function adminPostHeaders(): string
    {
        return My::jsLoad('backend');
    }

    /**
     * Add editor to post tags.
     *
     * @param   string          $editor     The editor name (ie dcCKEditor)
     * @param   string          $context    The editor context (ie post)
     * @param   ArrayObject     $alt_tags   The editor target (ie textarea id)
     * @param   string          $format     The editor format (ie xhtml)
     */
    public static function adminPostEditorTags(string $editor, string $context, ArrayObject $alt_tags, string $format): void
    {
        if ($context == 'post') {
            $alt_tags->append('#post_wtext');
        }
    }

    /**
     * Add widget text form to post edition page.
     *
     * @param   ArrayObject         $main       The main page contents
     * @param   ArrayObject         $sidebar    The sidebar page content
     * @param   null|MetaRecord     $post       The post record
     */
    public static function adminPostFormItems(ArrayObject $main, ArrayObject $sidebar, ?MetaRecord $post): void
    {
        if (!Utils::isActive()) {
            return;
        }

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

    /**
     * Save widget text from post edition page.
     *
     * @param   Cursor  $cur        The psot Cursor
     * @param   int     $post_id    The post ID
     */
    public static function adminAfterPostSave(Cursor $cur, int $post_id): void
    {
        if (!Utils::isActive()) {
            return;
        }

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
                Utils::updWidget((int) $w->f('option_id'), $wcur);
            }
        }
    }

    /**
     * Delete widget text on post deletion.
     *
     * @param   int     $post_id    The post ID
     */
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
