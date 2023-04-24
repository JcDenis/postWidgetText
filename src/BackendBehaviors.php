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
/**
 * @ingroup DC_PLUGIN_POSTWIDGETTEXT
 * @brief postWidgetText - admin methods.
 * @since 2.20
 */
class adminPostWidgetText
{
    private static $ie_cursor;
    private static $ie_pwt;

    private static function id()
    {
        return basename(dirname(__DIR__));
    }

    public static function sortbyCombo()
    {
        return [
            __('Post title')   => 'post_title',
            __('Post date')    => 'post_dt',
            __('Widget title') => 'option_title',
            __('Widget date')  => 'option_upddt',
        ];
    }

    public static function adminFiltersListsV2($sorts)
    {
        $sorts['pwt'] = [
            __('Post widget text'),
            self::sortbyCombo(),
            'post_dt',
            'desc',
            [__('entries per page'), 20],
        ];
    }

    public static function adminBlogPreferencesFormV2(dcSettings $blog_settings)
    {
        echo '
        <div class="fieldset">
        <h4 id="pwt_params">' . __('Post widget text') . '</h4>
        <div class="two-cols">
        <div class="col">
        <p><label for="active">' .
        form::checkbox('active', 1, (bool) $blog_settings->get(self::id())->get('active')) .
        __('Enable post widget text on this blog') . '</label></p>
        </div>
        <div class="col">
        <p><label for="importexport_active">' .
        form::checkbox('importexport_active', 1, (bool) $blog_settings->get(self::id())->get('importexport_active')) .
        __('Enable import/export behaviors') . '</label></p>
        </div>
        </div>
        <br class="clear" />
        </div>';
    }

    public static function adminBeforeBlogSettingsUpdate(dcSettings $blog_settings)
    {
        $blog_settings->get(self::id())->put('active', !empty($_POST['active']));
        $blog_settings->get(self::id())->put('importexport_active', !empty($_POST['importexport_active']));
    }

    public static function adminDashboardFavoritesV2(dcFavorites $favs)
    {
        $favs->register(self::id(), [
            'title'       => __('Post widget text'),
            'url'         => dcCore::app()->adminurl->get('admin.plugin.' . self::id()),
            'small-icon'  => dcPage::getPF(self::id() . '/icon.svg'),
            'large-icon'  => dcPage::getPF(self::id() . '/icon.svg'),
            'permissions' => dcCore::app()->auth->makePermissions([
                dcAuth::PERMISSION_USAGE,
                dcAuth::PERMISSION_CONTENT_ADMIN,
            ]),
        ]);
    }

    public static function adminPostHeaders()
    {
        $editor = dcCore::app()->auth->getOption('editor');

        return
            dcCore::app()->callBehavior('adminPostEditor', $editor['xhtml'], 'pwt', ['#post_wtext'], 'xhtml') .
            dcPage::jsModuleLoad(self::id() . '/js/post.js');
    }

    public static function adminPostFormItems($main, $sidebar, $post)
    {
        # _POST fields
        $title   = $_POST['post_wtitle'] ?? '';
        $content = $_POST['post_wtext']  ?? '';

        # Existing post
        if ($post) {
            $post_id = (int) $post->post_id;

            $pwt = new postWidgetText();
            $w   = $pwt->getWidgets(['post_id' => $post_id]);

            # Existing widget
            if (!$w->isEmpty()) {
                $title   = $w->option_title;
                $content = $w->option_content;
            }
        }

        $main['post_widget'] = '<div id="post-wtext-form">' .
        '<h4>' . __('Additional widget') . '</h4>' .

        '<p class="col">' .
        '<label class="bold" for="post_wtitle">' . __('Widget title:') . '</label>' .
        form::field('post_wtitle', 20, 255, html::escapeHTML($title), 'maximal') .
        '</p>' .

        '<p class="area" id="post-wtext">' .
        '<label class="bold" for="post_wtext">' . __('Wigdet text:') . '</label>' .
        form::textarea('post_wtext', 50, 5, html::escapeHTML($content)) .
        '</p>' .

        '</div>';
    }

    public static function adminAfterPostSave($cur, $post_id)
    {
        $post_id = (int) $post_id;

        # _POST fields
        $title   = $_POST['post_wtitle'] ?? '';
        $content = $_POST['post_wtext']  ?? '';

        # Object
        $pwt = new postWidgetText();

        # Get existing widget
        $w = $pwt->getWidgets(['post_id' => $post_id]);

        # If new content is empty, delete old existing widget
        if (empty($title) && empty($content) && !$w->isEmpty()) {
            $pwt->delWidget($w->option_id);
        }

        # If new content is not empty
        if (!empty($title) || !empty($content)) {
            $wcur                 = $pwt->openCursor();
            $wcur->post_id        = $post_id;
            $wcur->option_type    = self::id();
            $wcur->option_lang    = $cur->post_lang;
            $wcur->option_format  = $cur->post_format;
            $wcur->option_title   = $title;
            $wcur->option_content = $content;

            # Create widget
            if ($w->isEmpty()) {
                $id = $pwt->addWidget($wcur);
            }
            # Upddate widget
            else {
                $pwt->updWidget($w->option_id, $wcur);
            }
        }
    }

    public static function adminBeforePostDelete($post_id)
    {
        $post_id = (int) $post_id;

        # Object
        $pwt = new postWidgetText();

        # Get existing widget
        $w = $pwt->getWidgets(['post_id' => $post_id]);

        # If new content is empty, delete old existing widget
        if (!$w->isEmpty()) {
            $pwt->delWidget($w->option_id);
        }
    }

    public static function exportSingleV2($exp, $blog_id)
    {
        $exp->export(
            self::id(),
            'SELECT option_type, option_content, ' .
            'option_content_xhtml, W.post_id ' .
            'FROM ' . dcCore::app()->prefix . initPostWidgetText::PWT_TABLE_NAME . ' W ' .
            'LEFT JOIN ' . dcCore::app()->prefix . dcBlog::POST_TABLE_NAME . ' P ' .
            'ON P.post_id = W.post_id ' .
            "WHERE P.blog_id = '" . $blog_id . "' " .
            "AND W.option_type = '" . dcCore::app()->con->escape(self::id()) . "' "
        );
    }

    public static function exportFullV2($exp)
    {
        $exp->export(
            self::id(),
            'SELECT option_type, option_content, ' .
            'option_content_xhtml, W.post_id ' .
            'FROM ' . dcCore::app()->prefix . initPostWidgetText::PWT_TABLE_NAME . ' W ' .
            'LEFT JOIN ' . dcCore::app()->prefix . dcBlog::POST_TABLE_NAME . ' P ' .
            'ON P.post_id = W.post_id ' .
            "WHERE W.option_type = '" . dcCore::app()->con->escape(self::id()) . "' "
        );
    }

    public static function importInitV2($bk)
    {
        self::$ie_cursor = dcCore::app()->con->openCursor(
            dcCore::app()->prefix . initPostWidgetText::PWT_TABLE_NAME
        );
        self::$ie_pwt = new postWidgetText();
    }

    public static function importSingleV2($line, $bk)
    {
        if ($line->__name == self::id()
         && isset($bk->old_ids['post'][(int) $line->post_id])
        ) {
            $line->post_id = $bk->old_ids['post'][(int) $line->post_id];

            $exists = self::$ie_pwt->getWidgets([
                'post_id' => $line->post_id,
            ]);

            if ($exists->isEmpty()) {
                self::$ie_cursor->clean();

                self::$ie_cursor->post_id              = (int) $line->post_id;
                self::$ie_cursor->option_type          = (string) $line->option_type;
                self::$ie_cursor->option_lang          = (string) $line->option_lang;
                self::$ie_cursor->option_format        = (string) $line->option_format;
                self::$ie_cursor->option_content       = (string) $line->option_content;
                self::$ie_cursor->option_content_xhtml = (string) $line->option_content_xhtml;

                self::$ie_pwt->addWidget(
                    self::$ie_cursor
                );
            }
        }
    }

    public static function importFullV2($line, $bk)
    {
        if ($line->__name == self::id()) {
            $exists = self::$ie_pwt->getWidgets([
                'post_id' => $line->post_id,
            ]);

            if ($exists->isEmpty()) {
                self::$ie_cursor->clean();

                self::$ie_cursor->post_id              = (int) $line->post_id;
                self::$ie_cursor->option_type          = (string) $line->option_type;
                self::$ie_cursor->option_format        = (string) $line->option_format;
                self::$ie_cursor->option_content       = (string) $line->option_content;
                self::$ie_cursor->option_content       = (string) $line->option_content;
                self::$ie_cursor->option_content_xhtml = (string) $line->option_content_xhtml;

                self::$ie_pwt->addWidget(
                    self::$ie_cursor
                );
            }
        }
    }
}
