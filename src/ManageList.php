<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Backend\Filter\Filters;
use Dotclear\Core\Backend\Listing\Listing;
use Dotclear\Core\Backend\Listing\Pager;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Html;

/**
 * @brief       postWidgetText backend list class.
 * @ingroup     postWidgetText
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @extends Listing<string>
 */
class ManageList extends Listing
{
    public function display(Filters $filter, string $enclose = '%s'): void
    {
        if (!App::blog()->isDefined()) {
            return;
        }

        // prepare page
        $blocks = explode('%s', $enclose);
        $page   = is_numeric($filter->value('page')) ? (int) $filter->value('page') : 1;
        $nb     = is_numeric($filter->value('nb')) ? (int) $filter->value('nb') : 30;
        $pager  = new Pager($page, (int) $this->rs_count, $nb, 10);
        $tz     = App::auth()->getInfo('user_tz');
        $tz     = is_string($tz) ? $tz : App::blog()->settings()->get('system')->getStr('blog_timezone', false);
        $tz     = $tz !== '' ? $tz : 'UTC';
        $sel    = is_array($_REQUEST['widgets'] ?? null) ? $_REQUEST['widgets'] : [];

        // no record
        if ($this->rs->isEmpty()) {
            echo sprintf(
                '<p><strong>%s</strong></p>',
                $filter->show() ? __('No widgets matching the filter.') : __('No widget')
            );

            return;
        }

        // table head line
        /**
         * @var ArrayObject<string, string>
         */
        $thead = new ArrayObject([
            'name'          => '<th colspan="2" class="first">' . __('Post title') . '</th>',
            'post_dt'       => '<th scope="col" class="nowrap">' . __('Post date') . '</th>',
            'option_title'  => '<th scope="col" class="nowrap">' . __('Widget title') . '</th>',
            'option_creadt' => '<th scope="col" class="nowrap">' . __('Widget date') . '</th>',
            'user_id'       => '<th scope="col" class="nowrap">' . __('Author') . '</th>',
            'post_type'     => '<th scope="col" class="nowrap">' . __('Type') . '</th>',
        ]);

        $this->userColumns(My::id(), $thead);

        // display list header
        echo
        $pager->getLinks() .
        $blocks[0] .
        sprintf(
            '<div class="table-outer"><table class="clear"><caption>%s</caption><thead><tr>%s</tr></thead><tbody>',
            $filter->show() ?
                sprintf(__('List of %s widgets matching the filter.'), $this->rs_count) :
                sprintf(__('List of widgets (%s)'), $this->rs_count),
            implode(iterator_to_array($thead))
        );

        // parses lines
        while ($this->rs->fetch()) {
            $w_title = Html::escapeHTML($this->rs->strField('option_title'));
            if ($w_title == '') {
                // widget title can accept HTML, but backend table not
                /*
                $w_title = App::frontend()->context()->global_filters(
                    $this->rs->option_content,
                    [
                        'encode_xml',
                        'remove_html',
                        'cut_string' => 80,
                    ]
                );
                */
                $w_title = '?';
            }

            $editable = $this->rs->__call('isEditable', []);

            // table body line
            /**
             * @var ArrayObject<string, string>
             */
            $tbody = new ArrayObject([
                'check' => '<td class="nowrap">' .
                    (new Checkbox(['widgets[]'], in_array((string) $this->rs->intField('option_id'), $sel)))
                        ->value($this->rs->intField('option_id'))
                        ->disabled(!$editable)
                        ->render() .
                    '</td>',
                'name' => '<td class="maximal"><a href="' . App::postTypes()->getPostAdminURL($this->rs->strField('post_type'), $this->rs->intField('post_id')) . '#post-wtext-form">' .
                    Html::escapeHTML($this->rs->strField('post_title')) . '</a></td>',
                'post_dt'       => '<td class="nowrap count">' . Date::dt2str(__('%Y-%m-%d %H:%M'), $this->rs->strField('post_dt'), $tz) . '</td>',
                'option_title'  => '<td class="nowrap">' . $w_title . '</td>',
                'option_creadt' => '<td class="nowrap count">' . Date::dt2str(__('%Y-%m-%d %H:%M'), $this->rs->strField('option_upddt'), $tz) . '</td>',
                'user_id'       => '<td class="nowrap">' . Html::escapeHTML($this->rs->strField('user_id')) . '</td>',
                'post_type'     => '<td class="nowrap">' . Html::escapeHTML($this->rs->strField('post_type')) . '</td>',
            ]);

            $this->userColumns(My::id(), $tbody);

            // display table body line
            echo
            '<tr class="line' . ($this->rs->intField('post_status') === App::status()->post()::PUBLISHED ? '' : ' offline') . '" id="p' . $this->rs->intField('post_id') . '">' .
            implode(iterator_to_array($tbody)) .
            '</tr>';
        }

        // display list footer
        echo
        '</tbody></table></div>' .
        ($blocks[1] ?? '') .
        $pager->getLinks();
    }
}
