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

if (!defined('DC_CONTEXT_ADMIN')) {

    return null;
}

/**
 * @ingroup DC_PLUGIN_POSTWIDGETTEXT
 * @brief postWidgetText - admin dashboard methods.
 * @since 2.6
 */
class postWidgetTextDashboard
{
    /**
     * Favorites.
     *
     * @param    dcCore      $core dcCore instance
     * @param    arrayObject $favs Array of favorites
     */
    public static function favorites(dcCore $core, $favs)
    {
        $favs->register('postWidgetText', [
            'title'       => __('Post widget text'),
            'url'         => $core->adminurl->get('admin.plugin.postWidgetText'),
            'small-icon'  => dcPage::getPF('postWidgetText/icon.png'),
            'large-icon'  => dcPage::getPF('postWidgetText/icon-big.png'),
            'permissions' => $core->auth->check('usage,contentadmin', $core->blog->id),
            'active_cb'   => ['postWidgetTextDashboard', 'active']
        ]);
    }

    /**
     * Favorites selection.
     *
     * @param    string $request Requested page
     * @param    array  $params  Requested parameters
     */
    public static function active($request, $params)
    {
        return $request == 'plugin.php' 
            && isset($params['p']) 
            && $params['p'] == 'postWidgetText';
    }
}