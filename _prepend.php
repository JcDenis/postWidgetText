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

$__autoload['postWidgetText'] = 
    dirname(__FILE__).'/inc/class.postwidgettext.php';
$__autoload['postWidgetTextDashboard'] = 
    dirname(__FILE__).'/inc/lib.pwt.dashboard.php';
$__autoload['postWidgetTextAdmin'] = 
    dirname(__FILE__).'/inc/lib.pwt.admin.php';
$__autoload['postWidgetTextBackup'] = 
    dirname(__FILE__).'/inc/lib.pwt.backup.php';
$__autoload['postWidgetTextList'] = 
    dirname(__FILE__).'/inc/lib.pwt.list.php';