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
 * @brief postWidgetText - backup methods.
 * @since 2.6
 */
class postWidgetTextBackup
{
    public static function exportSingle(dcCore $core, $exp, $blog_id)
    {
        $exp->export('postwidgettext',
            'SELECT option_type, option_content, '.
            'option_content_xhtml, W.post_id '.
            'FROM '.$core->prefix.'post_option W '.
            'LEFT JOIN '.$core->prefix.'post P '.
            'ON P.post_id = W.post_id '.
            "WHERE P.blog_id = '".$blog_id."' ".
            "AND W.option_type = 'postwidgettext' "
        );
    }

    public static function exportFull(dcCore $core, $exp)
    {
        $exp->export('postwidgettext',
            'SELECT option_type, option_content, '.
            'option_content_xhtml, W.post_id '.
            'FROM '.$core->prefix.'post_option W '.
            'LEFT JOIN '.$core->prefix.'post P '.
            'ON P.post_id = W.post_id '.
            "WHERE W.option_type = 'postwidgettext' "
        );
    }

    public static function importInit($bk, dcCore $core)
    {
        $bk->cur_postwidgettext = $core->con->openCursor(
            $core->prefix.'post_option'
        );
        $bk->postwidgettext = new postWidgetText($core);
    }

    public static function importSingle($line, $bk, dcCore $core)
    {
        if ($line->__name == 'postwidgettext' 
         && isset($bk->old_ids['post'][(integer) $line->post_id])
        ) {
            $line->post_id = $bk->old_ids['post'][(integer) $line->post_id];

            $exists = $bk->postwidgettext->getWidgets(array(
                'post_id' => $line->post_id)
            );

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

    public static function importFull($line ,$bk, dcCore $core)
    {
        if ($line->__name == 'postwidgettext') {
            $exists = $bk->postwidgettext->getWidgets(array(
                'post_id' => $line->post_id)
            );

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