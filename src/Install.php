<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use Dotclear\App;
use Dotclear\Database\MetaRecord;
use Dotclear\Database\Statement\SelectStatement;
use Dotclear\Database\Statement\UpdateStatement;
use Dotclear\Helper\Process\TraitProcess;
use Exception;

/**
 * @brief       postWidgetText insatll class.
 * @ingroup     postWidgetText
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Install
{
    use TraitProcess;

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
            // Widget ID was "src" (folder name) from 2023 to 2026.11
            $old = App::version()->getVersion(My::id());
            if ($old !== '' && version_compare($old, '2026.11.15', '<')) {
                self::renameWidgetId('src', My::id());
            }

            // Table is the same for plugins pollsFactory, postTask, postWidgetText
            $s = App::db()->structure();
            $s->table(My::TABLE_NAME)
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

            try {
                App::db()->structure()->synchronize($s);
            } catch (Exception $e) {
                // SQLite can not change existing fields (varchar with NULL default),
                // this is not an issue if all fields exist.
                $fields = App::db()->con()->schema()->getColumns(App::db()->con()->prefix() . My::TABLE_NAME);
                foreach (['option_title', 'option_content', 'option_content_xhtml'] as $field) {
                    if (!isset($fields[$field])) {
                        throw $e;
                    }
                }
            }

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
            App::error()->add($e->getMessage());

            return false;
        }
    }

    /**
     * Rename widget ID in widgets settings of all blogs.
     */
    private static function renameWidgetId(string $from, string $to): void
    {
        $table = App::db()->con()->prefix() . App::blogWorkspace()::NS_TABLE_NAME;

        $sql = new SelectStatement();
        $rs  = $sql
            ->columns(['blog_id', 'setting_id', 'setting_value'])
            ->from($table)
            ->where('setting_ns = ' . $sql->quote('widgets'))
            ->and('setting_id' . $sql->in(['widgets_nav', 'widgets_extra', 'widgets_custom']))
            ->and('setting_type = ' . $sql->quote(App::blogWorkspace()::NS_ARRAY))
            ->select();

        while ($rs instanceof MetaRecord && $rs->fetch()) {
            $list = json_decode($rs->strField('setting_value'), true);
            if (!is_array($list)) {
                continue;
            }

            $changed = false;
            foreach ($list as $k => $widget) {
                // Only this plugin widget, which has settings "excerpt" and "show"
                if (is_array($widget)
                    && ($widget['id'] ?? '') === $from
                    && array_key_exists('excerpt', $widget)
                    && array_key_exists('show', $widget)
                ) {
                    $widget['id'] = $to;
                    $list[$k]     = $widget;
                    $changed      = true;
                }
            }
            if (!$changed) {
                continue;
            }

            $blog_id = $rs->field('blog_id');

            $sql = new UpdateStatement();
            $sql
                ->ref($table)
                ->column('setting_value')
                ->value(json_encode($list, JSON_THROW_ON_ERROR))
                ->where('setting_ns = ' . $sql->quote('widgets'))
                ->and('setting_id = ' . $sql->quote($rs->strField('setting_id')))
                ->and(is_string($blog_id) ? 'blog_id = ' . $sql->quote($blog_id) : 'blog_id IS NULL')
                ->update();
        }
    }
}
