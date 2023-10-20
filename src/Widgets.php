<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use Dotclear\App;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsStack;
use Dotclear\Plugin\widgets\WidgetsElement;

/**
 * @brief       postWidgetText widgets class.
 * @ingroup     postWidgetText
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
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
                self::parseWidget(...),
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
            || !App::frontend()->context()->exists('posts')
            || !App::frontend()->context()->__get('posts')->f('post_id')
        ) {
            return '';
        }

        $title   = $w->__get('title') ?: null;
        $content = '';

        $rs = Utils::getWidgets(['post_id' => App::frontend()->context()->__get('posts')->f('post_id')]);
        if ($rs->isEmpty()) {
            return '';
        }

        if ('' != $rs->f('option_title')) {
            $title = $rs->f('option_title');
        }
        if ('' != $rs->f('option_content')) {
            $content = $rs->f('option_content');
        }
        if ('' == $content && $w->__get('excerpt')) {
            $content = App::frontend()->context()->__get('posts')->f('post_excerpt');
        }

        return $w->renderDiv(
            (bool) $w->__get('content_only'),
            My::id() . ' ' . $w->__get('class'),
            '',
            ($title ? $w->renderTitle(Html::escapeHTML($title)) : '') . $content
        );
    }
}
