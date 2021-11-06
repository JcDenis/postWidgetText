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

$d = dirname(__FILE__) . '/inc/';

$__autoload['postWidgetText']      = $d . 'class.postwidgettext.php';
$__autoload['adminPostWidgetText'] = $d . 'lib.pwt.admin.php';
$__autoload['listPostWidgetText']  = $d . 'lib.pwt.list.php';
