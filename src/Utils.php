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
use Dotclear\Database\{
    Cursor,
    MetaRecord,
    Structure
};

use Exception;

/**
 * @ingroup DC_PLUGIN_POSTWIDGETTEXT
 * @brief postWidgetText - admin and public methods.
 * @since 2.6
 */
class Utils
{
    public static function openCursor(): Cursor
    {
        return dcCore::app()->con->openCursor(dcCore::app()->prefix . My::TABLE_NAME);
    }

    public static function getWidgets(array $params, bool $count_only = false): MetaRecord
    {
        if (is_null(dcCore::app()->blog)) {
            throw new Exception('blog is not set');
        }

        if (!isset($params['columns'])) {
            $params['columns'] = [];
        }
        $params['columns'][] = 'option_id';
        $params['columns'][] = 'option_creadt';
        $params['columns'][] = 'option_upddt';
        $params['columns'][] = 'option_type';
        $params['columns'][] = 'option_format';
        $params['columns'][] = 'option_lang';
        $params['columns'][] = 'option_title';
        $params['columns'][] = 'option_content';
        $params['columns'][] = 'option_content_xhtml';

        if (!isset($params['from'])) {
            $params['from'] = '';
        }
        $params['join'] = 'LEFT JOIN ' . dcCore::app()->prefix . My::TABLE_NAME . ' W ON P.post_id=W.post_id ';

        if (!isset($params['sql'])) {
            $params['sql'] = '';
        }
        if (isset($params['option_type'])) {
            $params['sql'] .= "AND W.option_type = '" . dcCore::app()->con->escapeStr((string) $params['option_type']) . "' ";
        } else {
            $params['sql'] .= "AND W.option_type = '" . dcCore::app()->con->escapeStr((string) My::id()) . "' ";
        }
        unset($params['option_type']);
        if (!isset($params['post_type'])) {
            $params['post_type'] = '';
        }

        return dcCore::app()->blog->getPosts($params, $count_only);
    }

    public static function addWidget(Cursor $cur): int
    {
        if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->blog)) {
            throw new Exception('blog is not set');
        }

        if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcCore::app()->auth::PERMISSION_USAGE,
            dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            throw new Exception(__('You are not allowed to create an entry text widget'));
        }
        if ($cur->post_id == '') {
            throw new Exception('No such entry ID');
        }

        dcCore::app()->con->writeLock(dcCore::app()->prefix . My::TABLE_NAME);

        try {
            $rs = dcCore::app()->con->select(
                'SELECT MAX(option_id) ' .
                'FROM ' . dcCore::app()->prefix . My::TABLE_NAME
            );

            $cur->option_id     = (int) $rs->f(0) + 1;
            $cur->option_creadt = date('Y-m-d H:i:s');
            $cur->option_upddt  = date('Y-m-d H:i:s');

            self::getWidgetContent($cur, (int) $cur->option_id);

            $cur->insert();
            dcCore::app()->con->unlock();
        } catch (Exception $e) {
            dcCore::app()->con->unlock();

            throw $e;
        }

        dcCore::app()->blog->triggerBlog();

        return (int) $cur->option_id;
    }

    public static function updWidget(int $id, Cursor $cur): void
    {
        if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->blog)) {
            throw new Exception('blog is not set');
        }

        if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcCore::app()->auth::PERMISSION_USAGE,
            dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            throw new Exception(__('You are not allowed to update entries text widget'));
        }

        $id = (int) $id;

        if (empty($id)) {
            throw new Exception(__('No such ID'));
        }

        self::getWidgetContent($cur, $id);

        $cur->option_upddt = date('Y-m-d H:i:s');

        if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcCore::app()->auth::PERMISSION_CONTENT_ADMIN]), dcCore::app()->blog->id)) {
            $params['option_id']  = $id;
            $params['user_id']    = dcCore::app()->con->escapeStr((string) dcCore::app()->auth->userID());
            $params['no_content'] = true;
            $params['limit']      = 1;

            $rs = self::getWidgets($params);

            if ($rs->isEmpty()) {
                throw new Exception(__('You are not allowed to delete this entry text widget'));
            }
        }

        $cur->update('WHERE option_id = ' . $id . ' ');
        dcCore::app()->blog->triggerBlog();
    }

    public static function delWidget(int $id, ?string $type = null): void
    {
        if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->blog)) {
            throw new Exception('blog is not set');
        }

        if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcCore::app()->auth::PERMISSION_DELETE,
            dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            throw new Exception(__('You are not allowed to delete entries text widget'));
        }

        $id = (int) $id;
        $type ??= My::id();

        if (empty($id)) {
            throw new Exception(__('No such ID'));
        }

        if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcCore::app()->auth::PERMISSION_CONTENT_ADMIN]), dcCore::app()->blog->id)) {
            $params['option_id']  = $id;
            $params['user_id']    = dcCore::app()->con->escapeStr((string) dcCore::app()->auth->userID());
            $params['no_content'] = true;
            $params['limit']      = 1;

            $rs = self::getWidgets($params);

            if ($rs->isEmpty()) {
                throw new Exception(__('You are not allowed to delete this entry text widget'));
            }
        }

        dcCore::app()->con->execute(
            'DELETE FROM ' . dcCore::app()->prefix . My::TABLE_NAME . ' ' .
            'WHERE option_id = ' . $id . ' ' .
            "AND option_type = '" . dcCore::app()->con->escapeStr((string) $type) . "' "
        );

        dcCore::app()->blog->triggerBlog();
    }

    public static function setWidgetContent(int $option_id, string $format, string $lang, ?string &$content, ?string &$content_xhtml): void
    {
        if ($format == 'wiki') {
            dcCore::app()->initWikiPost();
            dcCore::app()->wiki2xhtml->setOpt('note_prefix', 'wnote-' . $option_id);
            if (strpos($lang, 'fr') === 0) {
                dcCore::app()->wiki2xhtml->setOpt('active_fr_syntax', 1);
            }
        }

        if ($content) {
            $content_xhtml = dcCore::app()->callFormater($format, $content);
            $content_xhtml = dcCore::app()->HTMLfilter($content_xhtml);
        } else {
            $content_xhtml = '';
        }

        $excerpt = $excerpt_xhtml = '';

        # --BEHAVIOR-- coreAfterPostContentFormat
        dcCore::app()->callBehavior('coreAfterPostContentFormat', [
            'excerpt'       => &$excerpt,
            'content'       => &$content,
            'excerpt_xhtml' => &$excerpt_xhtml,
            'content_xhtml' => &$content_xhtml,
        ]);
    }

    private static function getWidgetContent(Cursor $cur, int $option_id): void
    {
        $option_content       = $cur->option_content;
        $option_content_xhtml = $cur->option_content_xhtml;

        self::setWidgetContent(
            $option_id,
            $cur->option_format,
            $cur->option_lang,
            $option_content,
            $option_content_xhtml
        );

        $cur->option_content       = $option_content;
        $cur->option_content_xhtml = $option_content_xhtml;
    }
}
