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

$__autoload['postWidgetText']          = $d . 'class.postwidgettext.php';
$__autoload['postWidgetTextDashboard'] = $d . 'lib.pwt.dashboard.php';
$__autoload['postWidgetTextAdmin']     = $d . 'lib.pwt.admin.php';
$__autoload['postWidgetTextBackup']    = $d . 'lib.pwt.backup.php';
$__autoload['postWidgetTextList']      = $d . 'lib.pwt.list.php';