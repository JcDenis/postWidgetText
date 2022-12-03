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

/**
 * @ingroup DC_PLUGIN_POSTWIDGETTEXT
 * @brief postWidgetText - admin and public methods.
 * @since 2.6
 */
class postWidgetText
{
    public $con;
    private $table;
    private $blog;

    public function __construct()
    {
        $this->con   = dcCore::app()->con;
        $this->table = dcCore::app()->prefix . 'post_option';
        $this->blog  = dcCore::app()->con->escape(dcCore::app()->blog->id);
    }

    public function tableName()
    {
        return $this->table;
    }

    public function openCursor()
    {
        return $this->con->openCursor($this->table);
    }

    public function lockTable()
    {
        $this->con->writeLock($this->table);
    }

    public function unlockTable()
    {
        $this->con->unlock();
    }

    public function triggerBlog()
    {
        dcCore::app()->blog->triggerBlog();
    }

    public function getWidgets($params, $count_only = false)
    {
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
        $params['join'] = 'LEFT JOIN ' . $this->table . ' W ON P.post_id=W.post_id ';

        if (!isset($params['sql'])) {
            $params['sql'] = '';
        }
        if (isset($params['option_type'])) {
            $params['sql'] .= "AND W.option_type = '" . $this->con->escape($params['option_type']) . "' ";
        } else {
            $params['sql'] .= "AND W.option_type = 'postwidgettext' ";
        }
        unset($params['option_type']);
        if (!isset($params['post_type'])) {
            $params['post_type'] = '';
        }

        return dcCore::app()->blog->getPosts($params, $count_only);
    }

    public function addWidget($cur)
    {
        if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), $this->blog)) {
            throw new Exception(__('You are not allowed to create an entry text widget'));
        }
        if ($cur->post_id == '') {
            throw new Exception('No such entry ID');

            return null;
        }

        $this->lockTable();

        try {
            $rs = $this->con->select(
                'SELECT MAX(option_id) ' .
                'FROM ' . $this->table
            );

            $cur->option_id     = (int) $rs->f(0) + 1;
            $cur->option_creadt = date('Y-m-d H:i:s');
            $cur->option_upddt  = date('Y-m-d H:i:s');

            $this->getWidgetContent($cur, $cur->option_id);

            $cur->insert();
            $this->unlockTable();
        } catch (Exception $e) {
            $this->unlockTable();

            throw $e;
        }

        $this->triggerBlog();

        return $cur->option_id;
    }

    public function updWidget($id, &$cur)
    {
        if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), $this->blog)) {
            throw new Exception(__('You are not allowed to update entries text widget'));
        }

        $id = (int) $id;

        if (empty($id)) {
            throw new Exception(__('No such ID'));
        }

        $this->getWidgetContent($cur, $id);

        $cur->option_upddt = date('Y-m-d H:i:s');

        if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_CONTENT_ADMIN]), $this->blog)) {
            $params['option_id']  = $id;
            $params['user_id']    = $this->con->escape(dcCore::app()->auth->userID());
            $params['no_content'] = true;
            $params['limit']      = 1;

            $rs = $this->getWidgets($params);

            if ($rs->isEmpty()) {
                throw new Exception(__('You are not allowed to delete this entry text widget'));
            }
        }

        $cur->update('WHERE option_id = ' . $id . ' ');
        $this->triggerBlog();
    }

    public function delWidget($id, $type = 'postwidgettext')
    {
        if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_DELETE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), $this->blog)) {
            throw new Exception(__('You are not allowed to delete entries text widget'));
        }

        $id = (int) $id;

        if (empty($id)) {
            throw new Exception(__('No such ID'));
        }

        if (!dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_CONTENT_ADMIN]), $this->blog)) {
            $params['option_id']  = $id;
            $params['user_id']    = $this->con->escape(dcCore::app()->auth->userID());
            $params['no_content'] = true;
            $params['limit']      = 1;

            $rs = $this->getWidgets($params);

            if ($rs->isEmpty()) {
                throw new Exception(__('You are not allowed to delete this entry text widget'));
            }
        }

        $this->con->execute(
            'DELETE FROM ' . $this->table . ' ' .
            'WHERE option_id = ' . $id . ' ' .
            "AND option_type = '" . $this->con->escape($type) . "' "
        );

        $this->triggerBlog();
    }

    private function getWidgetContent(&$cur, $option_id)
    {
        $option_content       = $cur->option_content;
        $option_content_xhtml = $cur->option_content_xhtml;

        $this->setWidgetContent(
            $option_id,
            $cur->option_format,
            $cur->option_lang,
            $option_content,
            $option_content_xhtml
        );

        $cur->option_content       = $option_content;
        $cur->option_content_xhtml = $option_content_xhtml;
    }

    public function setWidgetContent($option_id, $format, $lang, &$content, &$content_xhtml)
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
}
