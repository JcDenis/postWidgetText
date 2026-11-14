<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use Dotclear\App;
use Dotclear\Database\MetaRecord;
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
        $posts = App::frontend()->context()->exists('posts') ? App::frontend()->context()->__get('posts') : null;
        if ($w->__get('offline')
            || !Utils::isActive()
            || !$posts instanceof MetaRecord
            || !$posts->intField('post_id')
        ) {
            return '';
        }

        $title   = is_string($title = $w->__get('title')) ? $title : '';
        $content = '';

        $rs = Utils::getWidgets(['post_id' => $posts->intField('post_id')]);
        if (!$rs->isEmpty()) {
            if ('' !== $rs->strField('option_title')) {
                $title = $rs->strField('option_title');
            }
            if ('' !== $rs->strField('option_content_xhtml')) {
                $content = $rs->strField('option_content_xhtml');
            }
        }
        if ('' === $content && $w->__get('excerpt')) {
            $content = $posts->strField('post_excerpt_xhtml');
        }

        // Nothing to show
        if ('' === $content && !$w->__get('show')) {
            return '';
        }

        return $w->renderDiv(
            (bool) $w->__get('content_only'),
            My::id() . ' ' . (is_string($class = $w->__get('class')) ? $class : ''),
            '',
            ($title !== '' ? $w->renderTitle(Html::escapeHTML($title)) : '') . $content
        );
    }
}
