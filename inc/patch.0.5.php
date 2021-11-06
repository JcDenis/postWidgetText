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
$records = $core->con->select(
    'SELECT W.*, P.post_lang, P.post_format FROM ' . $core->prefix . 'post_wtext W ' .
    'LEFT JOIN ' . $core->prefix . 'post P ON P.post_id=W.post_id '
);
if (!$records->isEmpty()) {
    $cur = $core->con->openCursor($core->prefix . 'post_option');
    while ($records->fetch()) {
        $core->con->writeLock($core->prefix . 'post_option');

        try {
            $id = $core->con->select(
                'SELECT MAX(option_id) FROM ' . $core->prefix . 'post_option'
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
            $core->con->unlock();
        } catch (Exception $e) {
            $core->con->unlock();

            throw $e;
        }
    }
}
