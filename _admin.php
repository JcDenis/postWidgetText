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

require dirname(__FILE__).'/_widgets.php';

# Admin menu
if ($core->blog->settings->postwidgettext->postwidgettext_active) {
    $_menu['Plugins']->addItem(
        __('Post widget text'),
        'plugin.php?p=postWidgetText',
        'index.php?pf=postWidgetText/icon.png',
        preg_match(
            '/plugin.php\?p=postWidgetText(&.*)?$/',
            $_SERVER['REQUEST_URI']),
        $core->auth->check('contentadmin', $core->blog->id)
    );

    $core->addBehavior(
        'adminDashboardFavorites',
        array('postWidgetTextDashboard', 'favorites')
    );
}
# Post
$core->addBehavior(
    'adminPostHeaders',
    array('postWidgetTextAdmin', 'headers'));
$core->addBehavior(
    'adminPostFormItems',
    array('postWidgetTextAdmin', 'form'));
$core->addBehavior(
    'adminAfterPostUpdate',
    array('postWidgetTextAdmin', 'save'));
$core->addBehavior(
    'adminAfterPostCreate',
    array('postWidgetTextAdmin', 'save'));
$core->addBehavior(
    'adminBeforePostDelete',
    array('postWidgetTextAdmin', 'delete'));

# Plugin "pages"
$core->addBehavior(
    'adminPageHeaders',
    array('postWidgetTextAdmin', 'headers'));
$core->addBehavior(
    'adminPageFormItems',
    array('postWidgetTextAdmin', 'form'));
$core->addBehavior(
    'adminAfterPageUpdate',
    array('postWidgetTextAdmin', 'save'));
$core->addBehavior(
    'adminAfterPageCreate',
    array('postWidgetTextAdmin', 'save'));
$core->addBehavior(
    'adminBeforePageDelete',
    array('postWidgetTextAdmin', 'delete'));

# Plugin "importExport"
if ($core->blog->settings->postwidgettext->postwidgettext_importexport_active) {
    $core->addBehavior(
        'exportFull',
        array('postWidgetTextBackup', 'exportFull')
    );
    $core->addBehavior(
        'exportSingle',
        array('postWidgetTextBackup', 'exportSingle')
    );
    $core->addBehavior(
        'importInit',
        array('postWidgetTextBackup', 'importInit')
    );
    $core->addBehavior(
        'importSingle',
        array('postWidgetTextBackup', 'importSingle')
    );
    $core->addBehavior(
        'importFull',
        array('postWidgetTextBackup', 'importFull')
    );
}