<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use Dotclear\App;
use Dotclear\Database\{
    Cursor,
    MetaRecord
};
use Dotclear\Database\Statement\{
    DeleteStatement,
    JoinStatement,
    SelectStatement
};
use Dotclear\Helper\Text;
use Exception;

/**
 * @brief       postWidgetText utils class.
 * @ingroup     postWidgetText
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Utils
{
    /**
     * Check if module is active on current blog
     *
     * @return  bool    True on activated
     */
    public static function isActive(): bool
    {
        return (bool) My::settings()->get('active');
    }

    /**
     * Open cursor.
     *
     * @return  Cursor  The fresh cursor
     */
    public static function openCursor(): Cursor
    {
        return App::con()->openCursor(App::con()->prefix() . My::TABLE_NAME);
    }

    /**
     * Get widgetTexts.
     *
     * @param   array<string, mixed>    $params     The query params
     * @param   bool                    $count_only     Return count only
     *
     * @return  MetaRecord  The record (that mixes post and widgetText info)
     */
    public static function getWidgets(array $params, bool $count_only = false): MetaRecord
    {
        if (!App::blog()->isDefined()) {
            throw new Exception(__('Blog is not set'));
        }

        $sql = new SelectStatement();

        if (!$count_only) {
            $sql->columns([
                'option_id',
                'option_creadt',
                'option_upddt',
                'option_type',
                'option_format',
                'option_lang',
                'option_title',
                'option_content',
                'option_content_xhtml',
            ]);
        }

        $sql->join(
            (new JoinStatement())
                ->left()
                ->from($sql->as(App::con()->prefix() . My::TABLE_NAME, 'W'))
                ->on('P.post_id = W.post_id')
                ->statement()
        );

        if (isset($params['option_type'])) {
            if (is_array($params['option_type']) || $params['option_type'] != '') {
                $sql->and('option_type' . $sql->in($params['option_type']));
            }
        } else {
            $sql->and('option_type = ' . $sql->quote(My::id()));
        }

        // search post title
        if (!empty($params['search_post_title'])) {
            $words = Text::splitWords($params['search_post_title']);

            if (!empty($words)) {
                foreach ($words as $i => $w) {
                    $words[$i] = $sql->like('post_title', '%' . $sql->escape($w) . '%');
                }
                $sql->and($words);
            }
            unset($params['search_post_title']);
        }

        // search widget title
        if (!empty($params['search_widget_title'])) {
            $words = Text::splitWords($params['search_widget_title']);

            if (!empty($words)) {
                foreach ($words as $i => $w) {
                    $words[$i] = $sql->like('option_title', '%' . $sql->escape($w) . '%');
                }
                $sql->and($words);
            }
            unset($params['search_widget_title']);
        }

        // work on all post type by default
        if (!isset($params['post_type'])) {
            $params['post_type'] = '';
        }

        return App::blog()->getPosts($params, $count_only, $sql);
    }

    /**
     * Add a widgetText.
     *
     * @param   Cursor  $cur    The widgetText Cursor
     *
     * @return  int     The new widgetText ID
     */
    public static function addWidget(Cursor $cur): int
    {
        if (!App::blog()->isDefined()) {
            throw new Exception(__('Blog is not set'));
        }

        // check permissions to add post
        if (!App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_USAGE,
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            throw new Exception(__('You are not allowed to create an entry text widget'));
        }

        // check properties
        if ($cur->getField('post_id') == '') {
            throw new Exception('No such entry ID');
        }

        // lock table
        App::con()->writeLock(App::con()->prefix() . My::TABLE_NAME);

        try {
            $sql = new SelectStatement();
            $rs  = $sql->from(App::con()->prefix() . My::TABLE_NAME)->column($sql->max('option_id'))->select();
            if (is_null($rs) || $rs->isEmpty()) {
                throw new Exception(__('Something went wrong)'));
            }

            // set default widgetText properties
            $cur->setField('option_id', (int) $rs->f(0) + 1);
            $cur->setField('option_creadt', date('Y-m-d H:i:s'));
            $cur->setField('option_upddt', date('Y-m-d H:i:s'));

            // check and complete Cursor
            self::getWidgetContent($cur, (int) $cur->getField('option_id'));

            // add new widgetText
            $cur->insert();

            App::con()->unlock();
        } catch (Exception $e) {
            App::con()->unlock();

            throw $e;
        }

        // update blog
        App::blog()->triggerBlog();

        // return new widgetText ID
        return (int) $cur->getField('option_id');
    }

    /**
     * Update a widgetText.
     *
     * @param   int     $id     The widgetText ID
     * @param   Cursor  $cur    The widgetText Cursor
     */
    public static function updWidget(int $id, Cursor $cur): void
    {
        if (!App::blog()->isDefined()) {
            throw new Exception(__('Blog is not set'));
        }

        // check permission to delete post
        if (!App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_USAGE,
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            throw new Exception(__('You are not allowed to update entries text widget'));
        }

        // check properties
        if (empty($id)) {
            throw new Exception(__('No such ID'));
        }

        // check and complete Cursor
        self::getWidgetContent($cur, $id);
        $cur->setField('option_upddt', date('Y-m-d H:i:s'));

        // check if user is post owner
        if (!App::auth()->check(App::auth()->makePermissions([App::auth()::PERMISSION_CONTENT_ADMIN]), App::blog()->id())) {
            $rs = self::getWidgets([
                'option_id'  => $id,
                'user_id'    => App::con()->escapeStr((string) App::auth()->userID()),
                'no_content' => true,
                'limit'      => 1,
            ]);

            if ($rs->isEmpty()) {
                throw new Exception(__('You are not allowed to delete this entry text widget'));
            }
        }

        // update widgetText
        $cur->update('WHERE option_id = ' . $id . ' ');

        // update blog
        App::blog()->triggerBlog();
    }

    /**
     * Delete a widgetText.
     *
     * @param   int             $id     The widgetText ID
     * @param   null|string     $type   The widgetText optionnal type
     */
    public static function delWidget(int $id, ?string $type = null): void
    {
        if (!App::blog()->isDefined()) {
            throw new Exception(__('Blog is not set'));
        }

        // check permission to delete post
        if (!App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_DELETE,
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            throw new Exception(__('You are not allowed to delete entries text widget'));
        }

        // check properties
        if (empty($id)) {
            throw new Exception(__('No such ID'));
        }
        if (empty($type)) {
            $type = My::id();
        }

        // check if user is post owner
        if (!App::auth()->check(App::auth()->makePermissions([App::auth()::PERMISSION_CONTENT_ADMIN]), App::blog()->id())) {
            $rs = self::getWidgets([
                'option_id'  => $id,
                'user_id'    => App::con()->escapeStr((string) App::auth()->userID()),
                'no_content' => true,
                'limit'      => 1,
            ]);

            if ($rs->isEmpty()) {
                throw new Exception(__('You are not allowed to delete this entry text widget'));
            }
        }

        // delete widgetText
        $sql = new DeleteStatement();
        $sql->from(App::con()->prefix() . My::TABLE_NAME)
            ->where('option_id = ' . $id)
            ->and('option_type = ' . $sql->quote($type))
            ->delete();

        // update blog
        App::blog()->triggerBlog();
    }

    /**
     * Parse widgetText content.
     *
     * @param   int             $option_id      The widgetText ID
     * @param   string          $format         The format
     * @param   string          $lang           The lang
     * @param   null|string     $content        The content
     * @param   null|string     $content_xhtml  The xhtml content
     */
    public static function setWidgetContent(int $option_id, string $format, string $lang, ?string &$content, ?string &$content_xhtml): void
    {
        if ($format == 'wiki') {
            App::filter()->initWikiPost();
            App::filter()->wiki()?->setOpt('note_prefix', 'wnote-' . $option_id);
            if (strpos($lang, 'fr') === 0) {
                App::filter()->wiki()?->setOpt('active_fr_syntax', 1);
            }
        }

        if ($content) {
            $content_xhtml = App::formater()->callEditorFormater('dcLegacyEditor', $format, $content);
            $content_xhtml = App::filter()->HTMLfilter($content_xhtml);
        } else {
            $content_xhtml = '';
        }

        $excerpt = $excerpt_xhtml = '';

        # --BEHAVIOR-- coreAfterPostContentFormat -- array
        App::behavior()->callBehavior('coreAfterPostContentFormat', [
            'excerpt'       => &$excerpt,
            'content'       => &$content,
            'excerpt_xhtml' => &$excerpt_xhtml,
            'content_xhtml' => &$content_xhtml,
        ]);
    }

    /**
     * Extract content.
     *
     * @param   Cursor  $cur        The widgetText Cursor
     * @param   int     $option_id  The widgetText ID
     */
    private static function getWidgetContent(Cursor $cur, int $option_id): void
    {
        $option_content       = $cur->getfield('option_content');
        $option_content_xhtml = $cur->getField('option_content_xhtml');

        self::setWidgetContent(
            $option_id,
            $cur->getField('option_format'),
            $cur->getField('option_lang'),
            $option_content,
            $option_content_xhtml
        );

        $cur->setField('option_content', $option_content);
        $cur->setField('option_content_xhtml', $option_content_xhtml);
    }
}
