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

if (!defined('DC_CONTEXT_ADMIN')) {

    return null;
}

/**
 * @ingroup DC_PLUGIN_POSTWIDGETTEXT
 * @brief postWidgetText - admin list methods.
 * @since 2.6
 */
class listPostWidgetText extends adminGenericList
{
    public function display($page, $nb_per_page, $enclose = '')
    {
        if ($this->rs->isEmpty()) {

            return '<p><strong>'.__('No widget').'</strong></p>';
        }

        $pager = new dcPager($page, $this->rs_count, $nb_per_page, 10);
        $pager->html_prev = $this->html_prev;
        $pager->html_next = $this->html_next;
        $pager->var_page = 'page';

        $content =
        '<div class="table-outer">' .
        '<table class="clear">' .
        '<thead>' .
        '<tr>' .
        '<th colspan="2" class="nowrap">' . __('Post title') . '</th>' .
        '<th class="nowrap">' . __('Post date') . '</th>' .
        '<th class="nowrap">' . __('Widget title') . '</th>' .
        '<th class="nowrap">' . __('Widget date') . '</th>' .
        '<th class="nowrap">' . __('Author') . '</th>' .
        '<th class="nowrap">' . __('Type') . '</th>' .
        '</tr></thead><tbody>';

        while ($this->rs->fetch()) {

            $w_title = html::escapeHTML($this->rs->option_title);
            if ($w_title == '') {
                $w_title = '<em>' . context::global_filter(
                    $this->rs->option_content, 1, 1, 80, 0, 0
                ) . '</em>';
            }

            $content .= 
            '<tr class="line' . ($this->rs->post_status != 1 ? 
                ' offline' : ''
            ) . '" id="p' . $this->rs->post_id . '">' .
            '<td class="nowrap">' .
            form::checkbox(
                array('widgets[]'),
                $this->rs->option_id,
                '', '', '',
                !$this->rs->isEditable()
            ) . '</td>' .
            '<td class="maximal"><a href="' .
                $this->core->getPostAdminURL(
                    $this->rs->post_type,
                    $this->rs->post_id
                ) . '#post-wtext-form">' .
                html::escapeHTML($this->rs->post_title) .
            '</a></td>' .
            '<td class="nowrap">' . dt::dt2str(
                __('%Y-%m-%d %H:%M'),
                $this->rs->post_dt
            ) . '</td>' .
            '<td class="nowrap">' . $w_title . '</td>' .
            '<td class="nowrap">' . dt::dt2str(
                __('%Y-%m-%d %H:%M'),
                $this->rs->option_upddt
            ) . '</td>' .
            '<td class="nowrap">' . $this->rs->user_id . '</td>' .
            '<td class="nowrap">' . $this->rs->post_type . '</td>' .
            '</tr>';
        }

        $content .= 
        '</tbody></table></div>';

        echo 
            $pager->getLinks() .
            sprintf($enclose, $content) .
            $pager->getLinks();
    }
}