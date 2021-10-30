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

$this->registerModule(
    'Post widget text',
    'Add a widget with a text related to an entry',
    'Jean-Christian Denis and Contributors',
    '2021.10.30',
    [
        'requires'    => [['core', '2.20']],
        'permissions' => 'usage,contentadmin',
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/postWidgetText',
        'details'     => 'https://plugins.dotaddict.org/dc2/details/postWidgetText',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/postWidgetText/master/dcstore.xml',
        'settings' => [
            'blog' => '#params.pwt_params'
        ]
    ]
);