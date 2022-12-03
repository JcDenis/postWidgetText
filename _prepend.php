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

Clearbricks::lib()->autoload([
    'postWidgetText'      => __DIR__ . '/inc/class.postwidgettext.php',
    'adminPostWidgetText' => __DIR__ . '/inc/lib.pwt.admin.php',
    'listPostWidgetText'  => __DIR__ . '/inc/lib.pwt.list.php',
]);
