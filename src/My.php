<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use Dotclear\Module\MyPlugin;

/**
 * @brief       postWidgetText My helper.
 * @ingroup     postWidgetText
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class My extends MyPlugin
{
    /**
     * Plugin table name.
     *
     * @var     string  TABLE_NAME
     */
    public const TABLE_NAME = 'post_option';

    // Use default permissions
}
