<?php
/**
 * @package    quantummanager
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\QuantumManager\Administrator\Field\QuantumcombineField;
use Joomla\Component\QuantumManager\Administrator\Helper\QuantummanagerHelper;
use Joomla\Plugin\Button\QuantumManagerButton\Helper\ButtonHelper;

$app    = Factory::getApplication();
$folder = $app->input->get('folder', '', 'string');
$app->getSession()->clear('quantummanageraddscripts');

if (!empty($folder))
{
	$app->getSession()->set('quantummanagerroot', 'images/' . $folder);
}
else
{
	$app->getSession()->clear('quantummanagerroot');
}

HTMLHelper::_('stylesheet', 'com_quantummanager/modal.css', [
	'version'  => filemtime(__FILE__),
	'relative' => true
]);

HTMLHelper::_('stylesheet', 'plg_button_quantummanagerbutton/modal.css', [
	'version'  => filemtime(__FILE__),
	'relative' => true
]);

HTMLHelper::_('jquery.framework');

HTMLHelper::_('script', 'com_quantummanager/sortable.min.js', [
	'version'  => filemtime(__FILE__),
	'relative' => true
]);

HTMLHelper::_('script', 'plg_button_quantummanagerbutton/modal.js', [
	'version'  => filemtime(__FILE__),
	'relative' => true
]);

ButtonHelper::loadLang();
$fieldsForContentPlugin       = ButtonHelper::getFieldsForScopes();
$templatelistForContentPlugin = ButtonHelper::getTemplateListForScopes();
$groups                       = Factory::getUser()->groups;

try
{

	$folderRoot = 'root';

	$buttonsBun = [];
	$fields     = [
		'quantumtreecatalogs' => [
			'label'     => '',
			'directory' => $folderRoot,
			'position'  => 'container-left',
		],
		'quantumtoolbar'      => [
			'label'      => '',
			'position'   => 'container-center-top',
			'buttons'    => 'all',
			'buttonsBun' => '',
			'cssClass'   => 'qm-padding-small-left qm-padding-small-right qm-padding-small-top qm-padding-small-bottom',
		],
		'quantumupload'       => [
			'label'          => '',
			'position'       => 'container-center-top',
			'maxsize'        => QuantummanagerHelper::getParamsComponentValue('maxsize', '10'),
			'dropAreaHidden' => QuantummanagerHelper::getParamsComponentValue('dropareahidden', '0'),
			'directory'      => $folderRoot,
			'cssClass'       => 'qm-padding-small-left qm-padding-small-right qm-padding-small-bottom',
		],
		'quantumviewfiles'    => [
			'label'     => '',
			'position'  => 'container-center-center',
			'directory' => $folderRoot,
			'view'      => 'list-grid',
			'onlyfiles' => '0',
			'watermark' => QuantummanagerHelper::getParamsComponentValue('overlay', 0) > 0 ? '1' : '0',
			'help'      => QuantummanagerHelper::getParamsComponentValue('help', '1'),
			'metafile'  => QuantummanagerHelper::getParamsComponentValue('metafile', '1'),
		],
		'quantumcropperjs'    => [
			'label'    => '',
			'position' => 'bottom'
		],
	];

	if ((int) QuantummanagerHelper::getParamsComponentValue('unsplash', '1'))
	{
		$fields['quantumunsplash'] = [
			'position' => 'bottom'
		];
	}

	$actions = QuantummanagerHelper::getActions();
	if (!$actions->get('core.create'))
	{
		$buttonsBun[] = 'viewfilesCreateDirectory';
		unset($fields['quantumupload']);
	}

	if (!$actions->get('core.delete'))
	{
		unset($fields['quantumcropperjs']);
	}

	if (!$actions->get('core.delete'))
	{
		$buttonsBun[] = 'viewfilesDelete';
	}

	$optionsForField = [
		'name'   => 'filemanager',
		'label'  => '',
		'fields' => json_encode($fields)
	];

	$field = new QuantumcombineField();
	foreach ($optionsForField as $name => $value)
	{
		$field->__set($name, $value);
	}
	echo $field->getInput();
}
catch (Exception $e)
{
	echo $e->getMessage();
}

?>


<script type="text/javascript">

    window.QuantumButtonPlugin = {
        templatelist: '<?php echo QuantummanagerHelper::escapeJsonString(json_encode($templatelistForContentPlugin)) ?>',
        fields: '<?php echo QuantummanagerHelper::escapeJsonString(json_encode($fieldsForContentPlugin)) ?>'
    };

    window.QuantumwindowLang = {
        'buttonInsert': '<?php echo Text::_('COM_QUANTUMMANAGER_WINDOW_INSERT'); ?>',
        'inputAlt': '<?php echo Text::_('COM_QUANTUMMANAGER_WINDOW_ALT'); ?>',
        'inputWidth': '<?php echo Text::_('COM_QUANTUMMANAGER_WINDOW_WIDTH'); ?>',
        'defaultScope': '<?php echo Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_NAME_DEFAULT'); ?>',
        'defaultName': '<?php echo Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_DOCS_FIELDSFORM_NAME_NAME'); ?>',
        'defaultNameValue': '<?php echo Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_IMAGES_FIELDSFORM_DEFAULT_NAME'); ?>',
        'insertFile': '<?php echo Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_INSERT_FILE'); ?>',
        'helpSettings': '<?php echo in_array('2', $groups) || in_array('8', $groups) ? Text::sprintf('PLG_BUTTON_QUANTUMMANAGERBUTTON_HELP_SETTINGS', 'index.php?' . http_build_query(['option' => 'com_plugins', 'view' => 'plugins', (QuantummanagerHelper::isJoomla4() ? 'filter[search]' : 'filter.search') => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON')])) : '' ?>',
    };
</script>


