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
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-02-24T23:31:12+00:00',
    ]
);
