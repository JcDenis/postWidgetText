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

if (!defined('DC_CONTEXT_MODULE')) {

    return null;
}

$redir = empty($_REQUEST['redir']) ? 
    $list->getURL().'#plugins' : $_REQUEST['redir'];

# -- Get settings --
$core->blog->settings->addNamespace('postwidgettext');
$s = $core->blog->settings->postwidgettext;

$active = (boolean) $s->postwidgettext_active;
$importexport_active = (boolean) $s->postwidgettext_importexport_active;

# -- Set settings --
if (!empty($_POST['save'])) {

    try {
        $active = !empty($_POST['active']);
        $importexport_active = !empty($_POST['importexport_active']);

        $s->put('postwidgettext_active', $active);
        $s->put('postwidgettext_importexport_active', $importexport_active);

        $core->blog->triggerBlog();

        dcPage::addSuccessNotice(
            __('Configuration has been successfully updated.')
        );
        http::redirect(
            $list->getURL('module=postWidgetText&conf=1&redir='.
            $list->getRedir())
        );
    }
    catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
}

# -- Display form --
echo '

<div class="fieldset">
<h4>'.__('Activation').'</h4>

<p><label for="active">'.
form::checkbox('active', 1, $active).
__('Enable plugin').'</label></p>

<p><label for="importexport_active">'.
form::checkbox('importexport_active', 1, $importexport_active).
__('Enable import/export behaviors').'</label></p>

</div>';