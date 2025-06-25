<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use ArrayObject;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Database\{ Cursor, MetaRecord };
use Dotclear\Helper\Html\Form\{ Checkbox, Div, Fieldset, Img, Input, Label, Legend, Para, Text, Textarea };
use Dotclear\Helper\Html\Html;
use Dotclear\Interface\Core\BlogSettingsInterface;

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
     * @return  array<string, string>   The combo
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
     * @param   ArrayObject<string, mixed>  $sorts  Sort options
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
     * @param   ArrayObject<string, mixed>  $cols   Columns
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
     * @param   BlogSettingsInterface   $blog_settings  The blog settings
     */
    public static function adminBlogPreferencesFormV2(BlogSettingsInterface $blog_settings): void
    {
        echo (new Fieldset(My::id() . '_params'))
            ->legend(new Legend((new Img(My::icons()[0]))->class('icon-small')->render() . ' ' . My::name()))
            ->items([
                (new Div())
                    ->class('two-cols')->separator('')
                    ->items([
                        (new Div())
                            ->class('col')
                            ->items([
                                (new Para())
                                    ->items([
                                        (new Checkbox(My::id() . 'active', (bool) $blog_settings->get(My::id())->get('active')))
                                            ->value(1)
                                            ->label(new Label(__('Enable post widget text on this blog'), Label::IL_FT)),
                                    ]),
                            ]),
                        (new Div())
                            ->class('col')
                            ->items([
                                (new Para())
                                    ->items([
                                        (new Checkbox(My::id() . 'importexport_active', (bool) $blog_settings->get(My::id())->get('importexport_active')))
                                            ->value(1)
                                            ->label(new Label(__('Enable import/export behaviors'), Label::IL_FT)),
                                    ]),
                            ]),
                    ]),
            ])
            ->render();
    }

    /**
     * Save blog preference.
     *
     * @param   BlogSettingsInterface   $blog_settings  The blog settings
     */
    public static function adminBeforeBlogSettingsUpdate(BlogSettingsInterface $blog_settings): void
    {
        $blog_settings->get(My::id())->put('active', !empty($_POST[My::id() . 'active']));
        $blog_settings->get(My::id())->put('importexport_active', !empty($_POST[My::id() . 'importexport_active']));
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
     * @param   string                      $editor     The editor name (ie dcCKEditor)
     * @param   string                      $context    The editor context (ie post)
     * @param   ArrayObject<int, string>    $alt_tags   The editor target (ie textarea id)
     * @param   string                      $format     The editor format (ie xhtml)
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
     * @param   ArrayObject<string, string>     $main       The main page contents
     * @param   ArrayObject<string, string>     $sidebar    The sidebar page content
     * @param   null|MetaRecord                 $post       The post record
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

        $main['post_widget'] = (new Div('post-wtext-form'))
            ->items([
                (new Text('h4', __('Additional widget'))),
                (new Para())
                    ->items([
                        (new Input(My::id() . 'post_wtitle'))
                            ->class('maximal')
                            ->size(65)
                            ->maxlength(255)
                            ->value(Html::escapeHTML($title))
                            ->label(new Label(__('Widget title:'), Label::OL_TF)),
                    ]),
                (new Para())
                    ->items([
                        (new Textarea(My::id() . 'post_wtext', Html::escapeHTML($content)))
                            ->rows(6)
                            ->class('maximal')
                            ->label((new Label(__('Wigdet text:'), Label::OL_TF))),
                    ]),
            ])
            ->render();
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
        $title   = $_POST[My::id() . 'post_wtitle'] ?? '';
        $content = $_POST[My::id() . 'post_wtext']  ?? '';

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
