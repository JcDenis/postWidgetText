<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use Dotclear\App;

/**
 * @brief       postWidgetText plugin importExport stuff.
 * @ingroup     postWidgetText
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ImportExport
{
    private static $ie_cursor;

    public static function exportSingleV2($exp, $blog_id)
    {
        $exp->export(
            My::id(),
            'SELECT option_type, option_content, ' .
            'option_content_xhtml, W.post_id ' .
            'FROM ' . App::con()->prefix() . My::TABLE_NAME . ' W ' .
            'LEFT JOIN ' . App::con()->prefix() . App::blog()::POST_TABLE_NAME . ' P ' .
            'ON P.post_id = W.post_id ' .
            "WHERE P.blog_id = '" . $blog_id . "' " .
            "AND W.option_type = '" . App::con()->escapeStr((string) My::id()) . "' "
        );
    }

    public static function exportFullV2($exp)
    {
        $exp->export(
            My::id(),
            'SELECT option_type, option_content, ' .
            'option_content_xhtml, W.post_id ' .
            'FROM ' . App::con()->prefix() . My::TABLE_NAME . ' W ' .
            'LEFT JOIN ' . App::con()->prefix() . App::blog()::POST_TABLE_NAME . ' P ' .
            'ON P.post_id = W.post_id ' .
            "WHERE W.option_type = '" . App::con()->escapeStr((string) My::id()) . "' "
        );
    }

    public static function importInitV2($bk)
    {
        self::$ie_cursor = App::con()->openCursor(
            App::con()->prefix() . My::TABLE_NAME
        );
    }

    public static function importSingleV2($line, $bk)
    {
        if ($line->__name == My::id()
         && isset($bk->old_ids['post'][(int) $line->post_id])
        ) {
            $line->post_id = $bk->old_ids['post'][(int) $line->post_id];

            $exists = Utils::getWidgets([
                'post_id' => $line->post_id,
            ]);

            if ($exists->isEmpty()) {
                self::$ie_cursor->clean();

                self::$ie_cursor->post_id              = (int) $line->post_id;
                self::$ie_cursor->option_type          = (string) $line->option_type;
                self::$ie_cursor->option_lang          = (string) $line->option_lang;
                self::$ie_cursor->option_format        = (string) $line->option_format;
                self::$ie_cursor->option_content       = (string) $line->option_content;
                self::$ie_cursor->option_content_xhtml = (string) $line->option_content_xhtml;

                Utils::addWidget(
                    self::$ie_cursor
                );
            }
        }
    }

    public static function importFullV2($line, $bk)
    {
        if ($line->__name == My::id()) {
            $exists = Utils::getWidgets([
                'post_id' => $line->post_id,
            ]);

            if ($exists->isEmpty()) {
                self::$ie_cursor->clean();

                self::$ie_cursor->post_id              = (int) $line->post_id;
                self::$ie_cursor->option_type          = (string) $line->option_type;
                self::$ie_cursor->option_format        = (string) $line->option_format;
                self::$ie_cursor->option_content       = (string) $line->option_content;
                self::$ie_cursor->option_content       = (string) $line->option_content;
                self::$ie_cursor->option_content_xhtml = (string) $line->option_content_xhtml;

                Utils::addWidget(
                    self::$ie_cursor
                );
            }
        }
    }
}
