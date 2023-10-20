<?php
/**
 * @file
 * @brief       The plugin postWidgetText definition
 * @ingroup     postWidgetText
 *
 * @defgroup    postWidgetText Plugin postWidgetText.
 *
 * Add a widget with a text related to an entry.
 *
 * @author      Tomtom (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

$this->registerModule(
    'Post widget text',
    'Add a widget with a text related to an entry',
    'Jean-Christian Denis and Contributors',
    '2023.10.20',
    [
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'settings'    => ['blog' => '#params.pwt_params'],
        'type'        => 'plugin',
        'support'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/issues',
        'details'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/src/branch/master/README.md',
        'repository'  => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/raw/branch/master/dcstore.xml',
    ]
);
