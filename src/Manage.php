<?php

declare(strict_types=1);

namespace Dotclear\Plugin\postWidgetText;

use Dotclear\App;
use Dotclear\Core\Backend\Filter\Filters;
use Dotclear\Core\Backend\Filter\FiltersLibrary;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Helper\Network\Http;
use Exception;

/**
 * @brief       postWidgetText manage class.
 * @ingroup     postWidgetText
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Manage
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        # Delete widgets
        if (!empty($_POST['save']) && !empty($_POST['widgets'])) {
            try {
                foreach ($_POST['widgets'] as $k => $id) {
                    Utils::delWidget((int) $id);
                }

                Notices::addSuccessNotice(
                    __('Posts widgets successfully delete.')
                );
                if (!empty($_POST['redir'])) {
                    Http::redirect($_POST['redir']);
                } else {
                    My::redirect();
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()
            || !App::blog()->isDefined()
        ) {
            return;
        }

        # filters
        $filter = new Filters('pwt');
        $filter->add(FiltersLibrary::getPageFilter());
        $filter->add(FiltersLibrary::getInputFilter('search_post_title', __('Entry:')));
        $filter->add(FiltersLibrary::getInputFilter('search_widget_title', __('Widget:')));
        $filter->add(FiltersLibrary::getInputFilter('user_id', __('User:')));
        $params = $filter->params();

        # Get posts with text widget
        try {
            $posts      = Utils::getWidgets($params);
            $counter    = Utils::getWidgets($params, true);
            $posts_list = new ManageList($posts, $counter->f(0));
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
            $posts_list = null;
        }

        // display
        Page::openModule(
            My::name(),
            Page::jsPageTabs() .
            My::jsLoad('manage') .
            $filter->js(My::manageUrl() . '#record')
        );

        echo
        Page::breadcrumb([
            __('Plugins') => '',
            My::name()    => '',
        ]) .
        Notices::getNotices();

        if ($posts_list) {
            $filter->display('admin.plugin.' . My::id());

            $posts_list->display(
                $filter,
                '<form action="' . My::manageUrl() . '" method="post" id="form-entries">' .
                '%s' .
                '<div class="two-cols">' .
                '<p class="col checkboxes-helpers"></p>' .
                '<p class="col right">' .
                '<input id="do-action" class="delete" type="submit" name="save" value="' . __('Delete selected widgets') . '" /></p>' .
                My::parsedHiddenFields($filter->values(true)) .
                '</div>' .
                '</form>'
            );
        }

        Page::closeModule();
    }
}
