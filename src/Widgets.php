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
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsStack;
use Dotclear\Plugin\widgets\WidgetsElement;

/**
 * @ingroup DC_PLUGIN_POSTWIDGETTEXT
 * @brief postWidgetText - admin and public widget methods.
 * @since 2.6
 */
class Widgets
{
    /**
     * Widget initialisation.
     *
     * @param  WidgetsStack $w WidgetsStack instance
     */
    public static function initWidgets(WidgetsStack $w): void
    {
        $w
            ->create(
                basename(__DIR__),
                __('Post widget text'),
                [self::class, 'parseWidget'],
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

    /**
     * Parse widget.
     *
     * @param  WidgetsElement $w WidgetsElement instance
     */
    public static function parseWidget(WidgetsElement $w): string
    {
        if ($w->__get('offline')
            || !Utils::isActive()
            || is_null(dcCore::app()->ctx)
            || !dcCore::app()->ctx->exists('posts')
            || !dcCore::app()->ctx->__get('posts')->f('post_id')
        ) {
            return '';
        }

        $title   = $w->__get('title') ?: null;
        $content = '';

        $rs = Utils::getWidgets(['post_id' => dcCore::app()->ctx->__get('posts')->f('post_id')]);
        if ($rs->isEmpty()) {
            return '';
        }

        if ('' != $rs->f('option_title')) {
            $title = $rs->f('option_title');
        }
        if ('' != $rs->f('option_content_xhtml')) {
            $content = $rs->f('option_content_xhtml');
        }
        if ('' == $content && $w->__get('excerpt')) {
            $content = dcCore::app()->ctx->__get('posts')->f('post_excerpt_xhtml');
        }

        return $w->renderDiv(
            (bool) $w->__get('content_only'),
            My::id() . ' ' . $w->__get('class'),
            '',
            ($title ? $w->renderTitle(Html::escapeHTML($title)) : '') . $content
        );
    }
}
