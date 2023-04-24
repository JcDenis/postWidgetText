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

use dcAdminFilters;
use adminGenericFilterV2;
use dcCore;
use dcNsProcess;
use dcPage;
use Dotclear\Helper\Network\Http;
use Exception;

use form;

class Manage extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && My::phpCompliant()
            && !is_null(dcCore::app()->auth) && !is_null(dcCore::app()->blog)
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcCore::app()->auth::PERMISSION_USAGE,
                dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
            ]), dcCore::app()->blog->id);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        // nullsafe check
        if (is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return false;
        }

        # Delete widgets
        if (!empty($_POST['save']) && !empty($_POST['widgets'])) {
            try {
                foreach ($_POST['widgets'] as $k => $id) {
                    Utils::delWidget((int) $id);
                }

                dcPage::addSuccessNotice(
                    __('Posts widgets successfully delete.')
                );
                if (!empty($_POST['redir'])) {
                    Http::redirect($_POST['redir']);
                } else {
                    dcCore::app()->adminurl->redirect('admin.plugin.' . My::id());
                }
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        // nullsafe check
        if (is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return;
        }

        # filters
        $filter = new adminGenericFilterV2('pwt');
        $filter->add(dcAdminFilters::getPageFilter());
        $filter->add(dcAdminFilters::getInputFilter('search_post_title', __('Entry:')));
        $filter->add(dcAdminFilters::getInputFilter('search_widget_title', __('Widget:')));
        $filter->add(dcAdminFilters::getInputFilter('user_id', __('User:')));
        $params = $filter->params();

        # Get posts with text widget
        try {
            $posts      = Utils::getWidgets($params);
            $counter    = Utils::getWidgets($params, true);
            $posts_list = new ManageList($posts, $counter->f(0));
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
            $posts_list = null;
        }

        // display
        dcPage::openModule(
            My::name(),
            dcPage::jsPageTabs() .
            dcPage::jsModuleLoad(My::id() . '/js/manage.js') .
            $filter->js(dcCore::app()->adminurl->get('admin.plugin.' . My::id()) . '#record')
        );

        echo
        dcPage::breadcrumb([
            __('Plugins') => '',
            My::name()    => '',
        ]) .
        dcPage::notices();

        if ($posts_list) {
            $filter->display('admin.plugin.' . My::id(), form::hidden('p', My::id()));

            $posts_list->display(
                $filter,
                '<form action="' . dcCore::app()->adminurl->get('admin.plugin.' . My::id()) . '" method="post" id="form-entries">' .
                '%s' .
                '<div class="two-cols">' .
                '<p class="col checkboxes-helpers"></p>' .
                '<p class="col right">' .
                '<input id="do-action" class="delete" type="submit" name="save" value="' . __('Delete selected widgets') . '" /></p>' .
                dcCore::app()->adminurl->getHiddenFormFields('admin.plugin.' . My::id(), array_merge(['p' => My::id()], $filter->values(true))) .
                dcCore::app()->formNonce() .
                '</div>' .
                '</form>'
            );
        }

        dcPage::closeModule();
    }
}
