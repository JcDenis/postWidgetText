<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use Dotclear\App;
use Dotclear\Database\Cursor;
use Dotclear\Plugin\importExport\FlatBackup;
use Dotclear\Plugin\importExport\FlatBackupItem;
use Dotclear\Plugin\importExport\FlatExport;
use Dotclear\Plugin\importExport\FlatImportV2;

/**
 * @brief       postWidgetText plugin importExport stuff.
 * @ingroup     postWidgetText
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ImportExport
{
    public static function exportSingleV2(FlatExport $exp, string $blog_id): void
    {
        $exp->export(
            My::id(),
            'SELECT option_type, option_format, option_lang, option_title, option_content, ' .
            'option_content_xhtml, W.post_id ' .
            'FROM ' . App::db()->con()->prefix() . My::TABLE_NAME . ' W ' .
            'LEFT JOIN ' . App::db()->con()->prefix() . App::blog()::POST_TABLE_NAME . ' P ' .
            'ON P.post_id = W.post_id ' .
            "WHERE P.blog_id = '" . App::db()->con()->escapeStr($blog_id) . "' " .
            "AND W.option_type = '" . App::db()->con()->escapeStr(My::id()) . "' "
        );
    }

    public static function exportFullV2(FlatExport $exp): void
    {
        $exp->export(
            My::id(),
            'SELECT option_type, option_format, option_lang, option_title, option_content, ' .
            'option_content_xhtml, W.post_id ' .
            'FROM ' . App::db()->con()->prefix() . My::TABLE_NAME . ' W ' .
            'LEFT JOIN ' . App::db()->con()->prefix() . App::blog()::POST_TABLE_NAME . ' P ' .
            'ON P.post_id = W.post_id ' .
            "WHERE W.option_type = '" . App::db()->con()->escapeStr(My::id()) . "' "
        );
    }

    public static function importInitV2(FlatBackup $bk): void
    {
        $bk->__set('cur_postwidgettext', App::db()->con()->openCursor(App::db()->con()->prefix() . My::TABLE_NAME));
    }

    public static function importSingleV2(FlatBackupItem $line, FlatImportV2 $bk): void
    {
        $post_id = is_numeric($line->f('post_id')) ? (int) $line->f('post_id') : 0;

        if ($line->__name === My::id() && isset($bk->old_ids['post'][$post_id])) {
            self::import($line, $bk, $bk->old_ids['post'][$post_id]);
        }
    }

    public static function importFullV2(FlatBackupItem $line, FlatImportV2 $bk): void
    {
        if ($line->__name === My::id()) {
            self::import($line, $bk, is_numeric($line->f('post_id')) ? (int) $line->f('post_id') : 0);
        }
    }

    /**
     * Import a widget if entry has none.
     */
    private static function import(FlatBackupItem $line, FlatImportV2 $bk, int $post_id): void
    {
        $cur = $bk->__get('cur_postwidgettext');
        if ($post_id === 0 || !$cur instanceof Cursor || !Utils::getWidgets(['post_id' => $post_id])->isEmpty()) {
            return;
        }

        $cur->clean();
        $cur->setField('post_id', $post_id);
        $cur->setField('option_type', $line->f('option_type'));
        $cur->setField('option_format', $line->exists('option_format') ? $line->f('option_format') : 'xhtml');
        $cur->setField('option_lang', $line->exists('option_lang') ? $line->f('option_lang') : null);
        $cur->setField('option_title', $line->exists('option_title') ? $line->f('option_title') : null);
        $cur->setField('option_content', $line->f('option_content'));
        $cur->setField('option_content_xhtml', $line->f('option_content_xhtml'));

        Utils::addWidget($cur);
    }
}
