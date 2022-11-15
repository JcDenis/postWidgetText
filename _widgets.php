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
if (!defined('DC_RC_PATH')) {
    return null;
}

dcCore::app()->blog->settings->addNamespace('postwidgettext');

dcCore::app()->addBehavior('initWidgets', ['postWidgetTextWidget', 'init']);

/**
 * @ingroup DC_PLUGIN_POSTWIDGETTEXT
 * @brief postWidgetText - admin and public widget methods.
 * @since 2.6
 */
class postWidgetTextWidget
{
    public static function init($w)
    {
        $w
            ->create(
                'postwidgettext',
                __('Post widget text'),
                ['postWidgetTextWidget', 'display'],
                null,
                __('Add a widget with a text related to an entry')
            )
            ->addTitle(__('More about this entry'))
            ->setting(
                'excerpt',
                __('Use excerpt if no content'),
                0,
                'check'
            )
            ->setting(
                'show',
                __('Show widget even if empty'),
                0,
                'check'
            )
            ->addContentOnly()
            ->addClass()
            ->addOffline();
    }

    public static function display($w)
    {
        if ($w->offline) {
            return null;
        }

        if (!dcCore::app()->blog->settings->postwidgettext->postwidgettext_active
            || !dcCore::app()->ctx->exists('posts')
            || !dcCore::app()->ctx->posts->post_id
        ) {
            return null;
        }

        $title   = $w->title ?: null;
        $content = '';

        $pwt = new postWidgetText();
        $rs  = $pwt->getWidgets(['post_id' => dcCore::app()->ctx->posts->post_id]);

        if ($rs->isEmpty()) {
            return null;
        }

        if ('' != $rs->option_title) {
            $title = $rs->option_title;
        }
        if ('' != $rs->option_content_xhtml) {
            $content = $rs->option_content_xhtml;
        }
        if ('' == $content && $w->excerpt) {
            $content = dcCore::app()->ctx->posts->post_excerpt_xhtml;
        }

        return $w->renderDiv(
            $w->content_only,
            'postwidgettext ' . $w->class,
            '',
            ($title ? $w->renderTitle(html::escapeHTML($title)) : '') . $content
        );
    }
}
