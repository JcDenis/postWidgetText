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
declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use dcCore;
use Dotclear\Core\Process;
use Dotclear\Database\Structure;
use Exception;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            // Table is the same for plugins pollsFactory, postTask, postWidgetText
            $s = new Structure(dcCore::app()->con, dcCore::app()->prefix);
            $s->__get(My::TABLE_NAME)
                ->field('option_id', 'bigint', 0, false)
                ->field('post_id', 'bigint', 0, false)
                ->field('option_creadt', 'timestamp', 0, false, 'now()')
                ->field('option_upddt', 'timestamp', 0, false, 'now()')
                ->field('option_type', 'varchar', 32, false, "''")
                ->field('option_format', 'varchar', 32, false, "'xhtml'")
                ->field('option_lang', 'varchar', 5, true, null)
                ->field('option_title', 'varchar', 255, true, null)
                ->field('option_content', 'text', 0, true, null)
                ->field('option_content_xhtml', 'text', 0, false)

                ->index('idx_post_option_option', 'btree', 'option_id')
                ->index('idx_post_option_post', 'btree', 'post_id')
                ->index('idx_post_option_type', 'btree', 'option_type');

            (new Structure(dcCore::app()->con, dcCore::app()->prefix))->synchronize($s);

            // Settings
            $s = My::settings();
            $s->put(
                'active',
                true,
                'boolean',
                'post widget text plugin enabled',
                false,
                true
            );
            $s->put(
                'importexport_active',
                true,
                'boolean',
                'activate import/export behaviors',
                false,
                true
            );

            return true;
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());

            return false;
        }
    }
}
