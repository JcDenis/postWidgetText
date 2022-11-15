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
$records = dcCore::app()->con->select(
    'SELECT W.*, P.post_lang, P.post_format FROM ' . dcCore::app()->prefix . 'post_wtext W ' .
    'LEFT JOIN ' . dcCore::app()->prefix . 'post P ON P.post_id=W.post_id '
);
if (!$records->isEmpty()) {
    $cur = dcCore::app()->con->openCursor(dcCore::app()->prefix . 'post_option');
    while ($records->fetch()) {
        dcCore::app()->con->writeLock(dcCore::app()->prefix . 'post_option');

        try {
            $id = dcCore::app()->con->select(
                'SELECT MAX(option_id) FROM ' . dcCore::app()->prefix . 'post_option'
            )->f(0) + 1;

            $cur->clean();
            $cur->option_creadt = date('Y-m-d H:i:s');
            $cur->option_upddt  = date('Y-m-d H:i:s');

            $cur->option_id            = $id;
            $cur->post_id              = $records->post_id;
            $cur->option_type          = $records->wtext_type;
            $cur->option_lang          = $records->post_lang;
            $cur->option_format        = $records->post_format;
            $cur->option_title         = $records->wtext_title;
            $cur->option_content       = $records->wtext_content;
            $cur->option_content_xhtml = $records->wtext_content_xhtml;

            $cur->insert();
            dcCore::app()->con->unlock();
        } catch (Exception $e) {
            dcCore::app()->con->unlock();

            throw $e;
        }
    }
}
