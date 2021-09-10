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

$core->blog->settings->addNamespace('postwidgettext');

$core->addBehavior(
    'initWidgets',
    array('postWidgetTextWidget', 'init')
);

/**
 * @ingroup DC_PLUGIN_POSTWIDGETTEXT
 * @brief postWidgetText - admin and public widget methods.
 * @since 2.6
 */
class postWidgetTextWidget
{
    public static function init($w)
    {
        global $core;

        $w->create(
            'postwidgettext',
            __('Post widget text'),
            array('postWidgetTextWidget', 'display'),
            null,
            __('Add a widget with a text related to an entry')
        );
        $w->postwidgettext->setting(
            'title',
            __('Title:'),
            __('More about this entry'),
            'text'
        );
        $w->postwidgettext->setting(
            'excerpt',
            __('Use excerpt if no content'),
            0,
            'check'
        );
        $w->postwidgettext->setting(
            'show',
            __('Show widget even if empty'),
            0,
            'check'
        );
        $w->postwidgettext->setting(
            'content_only',
            __('Content only'),
            0,
            'check'
        );
        $w->postwidgettext->setting(
            'class',
            __('CSS class:'),
            ''
        );
    }

    public static function display($w)
    {
        global $core, $_ctx; 

        if (!$core->blog->settings->postwidgettext->postwidgettext_active
         || !$_ctx->exists('posts') 
         || !$_ctx->posts->post_id
        ) {
            return null;
        }

        $title = (strlen($w->title) > 0) ? 
                '<h2>'.html::escapeHTML($w->title).'</h2>' : null;
        $content = '';

        $pwt = new postWidgetText($core);
        $rs = $pwt->getWidgets(array('post_id'=>$_ctx->posts->post_id));

        if ('' != $rs->option_title) {
            $title = '<h2>'.$rs->option_title.'</h2>';
        }
        if ('' != $rs->option_content_xhtml) {
            $content = $rs->option_content_xhtml;
        }
        if ('' == $content && $w->excerpt) {
            $content = $_ctx->posts->post_excerpt_xhtml;
        }

        return '' == $content && !$w->show ?
            null : 
            ($w->content_only ? '' : '<div class="postwidgettext'.
            ($w->class ? ' '.html::escapeHTML($w->class) : '').'">').
            $title.
            $content.
            ($w->content_only ? '' : '</div>');
    }
}