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
use adminGenericFilterV2;
use adminGenericListV2;
use context;
use dcCore;
use dcPager;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Html;

/**
 * @ingroup DC_PLUGIN_POSTWIDGETTEXT
 * @brief postWidgetText - admin list methods.
 * @since 2.6
 */
class ManageList extends adminGenericListV2
{
    public function display(adminGenericFilterV2 $filter, string $enclose = '',): void
    {
        if ($this->rs->isEmpty()) {
            echo '<p><strong>' . ($filter->show() ?
                __('No widgets matching the filter.') : __('No widget')
            ) . '</strong></p>';

            return;
        }

        $pager = new dcPager((int) $filter->value('page'), (int) $this->rs_count, (int) $filter->value('nb'), 10);

        $content = 
        '<div class="table-outer">' .
        '<table class="clear">' .
        '<caption>' . (
            $filter->show() ?
            sprintf(__('List of %s widgets matching the filter.'), $this->rs_count) :
            sprintf(__('List of widgets (%s)'), $this->rs_count)
        ) . '</caption>' .
        '<thead><tr>';

        $cols = new ArrayObject([
            'name'          => '<th colspan="2" class="first">' . __('Post title') . '</th>',
            'post_dt'       => '<th scope="col" class="nowrap">' . __('Post date') . '</th>',
            'option_title'  => '<th scope="col" class="nowrap">' . __('Widget title') . '</th>',
            'option_creadt' => '<th scope="col" class="nowrap">' . __('Widget date') . '</th>',
            'user_id'       => '<th scope="col" class="nowrap">' . __('Author') . '</th>',
            'post_type'     => '<th scope="col" class="nowrap">' . __('Type') . '</th>',
        ]);

        $this->userColumns(My::id(), $cols);

        $content .= implode(iterator_to_array($cols)) . '</tr></thead><tbody>';

        while ($this->rs->fetch()) {
            $w_title = Html::escapeHTML($this->rs->option_title);
            if ($w_title == '') {
                $w_title = '<em>' . context::global_filters(
                    $this->rs->option_content,
                    [
                        'encode_xml',
                        'remove_html',
                        'cut_string' => 80,
                    ]
                ) . '</em>';
            }

            $cols = new ArrayObject([
                'check'   => '<td class="nowrap">' . (new Checkbox(['widgets[]'], (bool) $this->rs->f('option_id')))->value($this->rs->f('periodical_id'))->disabled(!$this->rs->isEditable())->render() . '</td>',
                'name'    => '<td class="maximal"><a href="' . dcCore::app()->getPostAdminURL($this->rs->f('post_type'), $this->rs->f('post_id')) . '#post-wtext-form">' .
                    Html::escapeHTML($this->rs->f('post_title')) . '</a></td>',
                'post_dt'   => '<td class="nowrap count">' . Date::dt2str(__('%Y-%m-%d %H:%M'), $this->rs->f('post_dt'), $tz ?? 'UTC') . '</td>',
                'option_title' => '<td class="nowrap">' . $w_title . '</td>',
                'option_creadt'   => '<td class="nowrap count">' . Date::dt2str(__('%Y-%m-%d %H:%M'), $this->rs->f('option_upddt'), $tz ?? 'UTC') . '</td>',
                'user_id' => '<td class="nowrap">' . $this->rs->f('user_id') . '</td>',
                'post_type' => '<td class="nowrap">' . $this->rs->f('post_type') . '</td>',

            ]);

            $this->userColumns(My::id(), $cols);

            $content .=
            '<tr class="line' . ($this->rs->f('post_status') == 1 ? '' : ' offline') . '" id="p' . $this->rs->f('post_id') . '">' .
            implode(iterator_to_array($cols)) .
            '</tr>';

        }

        $content .= '</tbody></table></div>';

        echo
            $pager->getLinks() .
            sprintf($enclose, $content) .
            $pager->getLinks();
    }
}
