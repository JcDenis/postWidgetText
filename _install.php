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

try {
    # check installed version
    if (!dcCore::app()->newVersion(
        basename(__DIR__), 
        dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version')
    )) {
        return null;
    }

    # Table is the same for plugins
    # pollsFactory, postTask, postWidgetText
    $s = new dbStruct(dcCore::app()->con, dcCore::app()->prefix);
    $s->{initPostWidgetText::PWT_TABLE_NAME}
        ->option_id('bigint', 0, false)
        ->post_id('bigint', 0, false)
        ->option_creadt('timestamp', 0, false, 'now()')
        ->option_upddt('timestamp', 0, false, 'now()')
        ->option_type('varchar', 32, false, "''")
        ->option_format('varchar', 32, false, "'xhtml'")
        ->option_lang('varchar', 5, true, null)
        ->option_title('varchar', 255, true, null)
        ->option_content('text', 0, true, null)
        ->option_content_xhtml('text', 0, false)

        ->index('idx_post_option_option', 'btree', 'option_id')
        ->index('idx_post_option_post', 'btree', 'post_id')
        ->index('idx_post_option_type', 'btree', 'option_type');

    $si      = new dbStruct(dcCore::app()->con, dcCore::app()->prefix);
    $changes = $si->synchronize($s);

    # Settings
    dcCore::app()->blog->settings->addNamespace('postwidgettext');
    dcCore::app()->blog->settings->postwidgettext->put(
        'postwidgettext_active',
        true,
        'boolean',
        'post widget text plugin enabled',
        false,
        true
    );
    dcCore::app()->blog->settings->postwidgettext->put(
        'postwidgettext_importexport_active',
        true,
        'boolean',
        'activate import/export behaviors',
        false,
        true
    );

    # Transfert records from old table to the new one
    if (dcCore::app()->getVersion(basename(__DIR__)) !== null
     && version_compare(dcCore::app()->getVersion(basename(__DIR__)), '0.5', '<')
    ) {
        require_once __DIR__ . '/inc/patch.0.5.php';
    }

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
