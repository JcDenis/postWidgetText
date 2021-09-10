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
    # Check module version
    if (version_compare(
        $core->getVersion('postWidgetText'),
        $core->plugins->moduleInfo('postWidgetText', 'version'),
        '>='
    )) {

        return null;
    }

    # Check Dotclear version
    if (!method_exists('dcUtils', 'versionsCompare') 
     || dcUtils::versionsCompare(DC_VERSION, '2.6', '<', false)) {
        throw new Exception(sprintf(
            '%s requires Dotclear %s', 'postWidgetText', '2.6'
        ));
    }

    # Table is the same for plugins
    # pollsFactory, postTask, postWidgetText
    $s = new dbStruct($core->con,$core->prefix);
    $s->post_option
        ->option_id ('bigint',0,false)
        ->post_id ('bigint',0,false)
        ->option_creadt ('timestamp',0,false,'now()')
        ->option_upddt ('timestamp',0,false,'now()')
        ->option_type ('varchar',32,false,"''")
        ->option_format ('varchar',32,false,"'xhtml'")
        ->option_lang ('varchar',5,true,null)
        ->option_title ('varchar',255,true,null)
        ->option_content ('text',0,true,null)
        ->option_content_xhtml ('text',0,false)

        ->index('idx_post_option_option','btree','option_id')
        ->index('idx_post_option_post','btree','post_id')
        ->index('idx_post_option_type','btree','option_type');

    $si = new dbStruct($core->con,$core->prefix);
    $changes = $si->synchronize($s);

    # Settings
    $core->blog->settings->addNamespace('postwidgettext');
    $core->blog->settings->postwidgettext->put('postwidgettext_active',
        true,'boolean','post widget text plugin enabled',false,true);
    $core->blog->settings->postwidgettext->put('postwidgettext_importexport_active',
        true,'boolean','activate import/export behaviors',false,true);

    # Transfert records from old table to the new one
    if ($core->getVersion('postWidgetText') !== null 
     && version_compare($core->getVersion('postWidgetText'), '0.5', '<')
    )    {
        require_once dirname(__FILE__).'/inc/patch.0.5.php';
    }

    # Set module version
    $core->setVersion(
        'postWidgetText',
        $core->plugins->moduleInfo('postWidgetText', 'version')
    );

    return true;
}
catch (Exception $e) {
    $core->error->add($e->getMessage());

    return false;
}