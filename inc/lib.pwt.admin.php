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
 * @brief postWidgetText - admin post methods.
 * @since 2.20
 */
class adminPostWidgetText
{
    public static function sortbyCombo()
    {
        return [
            __('Post title')   => 'post_title',
            __('Post date')    => 'post_dt',
            __('Widget title') => 'option_title',
            __('Widget date')  => 'option_upddt',
        ];
    }

    public static function adminFiltersLists(dcCore $core, $sorts)
    {
        $sorts['pwt'] = [
            __('Post widget text'),
            self::sortbyCombo(),
            'post_dt',
            'desc',
            [__('entries per page'), 20]
        ];
    }

    public static function adminBlogPreferencesForm(dcCore $core, dcSettings $blog_settings)
    {
        echo '
        <div class="fieldset">
        <h4 id="pwt_params">' . __('Post widget text') . '</h4>
        <div class="two-cols">
        <div class="col">
        <p><label for="active">' .
        form::checkbox('active', 1, (boolean) $blog_settings->postwidgettext->postwidgettext_active).
        __('Enable post widget text on this blog') . '</label></p>
        </div>
        <div class="col">
        <p><label for="importexport_active">' .
        form::checkbox('importexport_active', 1, (boolean) $blog_settings->postwidgettext->postwidgettext_importexport_active).
        __('Enable import/export behaviors') . '</label></p>
        </div>
        </div>
        <br class="clear" />
        </div>';
    }

    public static function adminBeforeBlogSettingsUpdate(dcSettings $blog_settings)
    {
        $blog_settings->postwidgettext->put('postwidgettext_active', !empty($_POST['active']));
        $blog_settings->postwidgettext->put('postwidgettext_importexport_active', !empty($_POST['importexport_active']));
    }

    public static function adminDashboardFavorites(dcCore $core, $favs)
    {
        $favs->register('postWidgetText', [
            'title'       => __('Post widget text'),
            'url'         => $core->adminurl->get('admin.plugin.postWidgetText'),
            'small-icon'  => dcPage::getPF('postWidgetText/icon.png'),
            'large-icon'  => dcPage::getPF('postWidgetText/icon-big.png'),
            'permissions' => $core->auth->check('usage,contentadmin', $core->blog->id),
            'active_cb'   => ['adminPostWidgetText', 'adminDashboardFavoritesActive']
        ]);
    }

    /**
     * Favorites selection.
     *
     * @param    string $request Requested page
     * @param    array  $params  Requested parameters
     */
    public static function adminDashboardFavoritesActive($request, $params)
    {
        return $request == 'plugin.php' 
            && isset($params['p']) 
            && $params['p'] == 'postWidgetText';
    }

    public static function adminPostHeaders()
    {
        global $core;
        $editor = $core->auth->getOption('editor');

        return 
            $core->callBehavior('adminPostEditor', $editor['xhtml'], 'pwt', ['#post_wtext'], 'xhtml') . 
            dcPage::jsLoad(dcPage::getPF('postWidgetText/js/post.js'));
    }

    public static function adminPostFormItems($main, $sidebar, $post)
    {
        # _POST fields
        $title = $_POST['post_wtitle'] ?? '';
        $content = $_POST['post_wtext'] ?? '';

        # Existing post
        if ($post) {
            $post_id = (integer) $post->post_id;

            $pwt = new postWidgetText($GLOBALS['core']);
            $w = $pwt->getWidgets(['post_id' => $post_id]);

            # Existing widget
            if (!$w->isEmpty()) {
                $title = $w->option_title;
                $content = $w->option_content;
            }
        }

        $main['post_widget'] = 
        '<div id="post-wtext-form">' .
        '<h4>' . __('Additional widget') . '</h4>' .

        '<p class="col">' .
        '<label class="bold" for="post_wtitle">' . __('Widget title:') . '</label>' .
        form::field('post_wtitle', 20, 255, html::escapeHTML($title), 'maximal') .
        '</p>' .

        '<p class="area" id="post-wtext">' .
        '<label class="bold" for="post_wtext">' .__('Wigdet text:') . '</label>' .
        form::textarea('post_wtext', 50, 5, html::escapeHTML($content)) .
        '</p>' .

        '</div>';
    }

    public static function adminAfterPostSave($cur, $post_id)
    {
        $post_id = (integer) $post_id;

        # _POST fields
        $title = $_POST['post_wtitle'] ?? '';
        $content = $_POST['post_wtext'] ?? '';

        # Object
        $pwt = new postWidgetText($GLOBALS['core']);

        # Get existing widget
        $w = $pwt->getWidgets(['post_id' => $post_id]);

        # If new content is empty, delete old existing widget
        if (empty($title) && empty($content) && !$w->isEmpty()) {
            $pwt->delWidget($w->option_id);
        }

        # If new content is not empty
        if (!empty($title) || !empty($content)) {
            $wcur = $pwt->openCursor();
            $wcur->post_id        = $post_id;
            $wcur->option_type    = 'postwidgettext';
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
        $post_id = (integer) $post_id;

        # Object
        $pwt = new postWidgetText($GLOBALS['core']);

        # Get existing widget
        $w = $pwt->getWidgets(['post_id' => $post_id]);

        # If new content is empty, delete old existing widget
        if (!$w->isEmpty()) {
            $pwt->delWidget($w->option_id);
        }
    }

    public static function exportSingle(dcCore $core, $exp, $blog_id)
    {
        $exp->export('postwidgettext',
            'SELECT option_type, option_content, ' .
            'option_content_xhtml, W.post_id ' .
            'FROM ' . $core->prefix . 'post_option W ' .
            'LEFT JOIN ' . $core->prefix . 'post P ' .
            'ON P.post_id = W.post_id ' .
            "WHERE P.blog_id = '" . $blog_id . "' " .
            "AND W.option_type = 'postwidgettext' "
        );
    }

    public static function exportFull(dcCore $core, $exp)
    {
        $exp->export('postwidgettext',
            'SELECT option_type, option_content, ' .
            'option_content_xhtml, W.post_id '.
            'FROM ' . $core->prefix . 'post_option W ' .
            'LEFT JOIN ' . $core->prefix . 'post P ' .
            'ON P.post_id = W.post_id ' .
            "WHERE W.option_type = 'postwidgettext' "
        );
    }

    public static function importInit($bk, dcCore $core)
    {
        $bk->cur_postwidgettext = $core->con->openCursor(
            $core->prefix . 'post_option'
        );
        $bk->postwidgettext = new postWidgetText($core);
    }

    public static function importSingle($line, $bk, dcCore $core)
    {
        if ($line->__name == 'postwidgettext' 
         && isset($bk->old_ids['post'][(integer) $line->post_id])
        ) {
            $line->post_id = $bk->old_ids['post'][(integer) $line->post_id];

            $exists = $bk->postwidgettext->getWidgets([
                'post_id' => $line->post_id
            ]);

            if ($exists->isEmpty()) {
                $bk->cur_postwidgettext->clean();

                $bk->cur_postwidgettext->post_id = 
                    (integer) $line->post_id;
                $bk->cur_postwidgettext->option_type = 
                    (string) $line->option_type;
                $bk->cur_postwidgettext->option_lang = 
                    (string) $line->option_lang;
                $bk->cur_postwidgettext->option_format = 
                    (string) $line->option_format;
                $bk->cur_postwidgettext->option_content = 
                    (string) $line->option_content;
                $bk->cur_postwidgettext->option_content_xhtml = 
                    (string) $line->option_content_xhtml;

                $bk->postwidgettext->addWidget(
                    $bk->cur_postwidgettext
                );
            }
        }
    }

    public static function importFull($line, $bk, dcCore $core)
    {
        if ($line->__name == 'postwidgettext') {
            $exists = $bk->postwidgettext->getWidgets([
                'post_id' => $line->post_id
            ]);

            if ($exists->isEmpty()) {
                $bk->cur_postwidgettext->clean();

                $bk->cur_postwidgettext->post_id = 
                    (integer) $line->post_id;
                $bk->cur_postwidgettext->option_type = 
                    (string) $line->option_type;
                $bk->cur_postwidgettext->option_format = 
                    (string) $line->option_format;
                $bk->cur_postwidgettext->option_content = 
                    (string) $line->option_content;
                $bk->cur_postwidgettext->option_content = 
                    (string) $line->option_content;
                $bk->cur_postwidgettext->option_content_xhtml = 
                    (string) $line->option_content_xhtml;

                $bk->postwidgettext->addWidget(
                    $bk->cur_postwidgettext
                );
            }
        }
    }
}