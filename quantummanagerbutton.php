<?php
/**
 * @package    quantummanagerbutton
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;

class PlgButtonQuantummanagerbutton extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var  boolean
	 *
	 * @since   1.1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button.
	 *
	 * @param   string  $name  The name of the button to add.
	 *
	 * @throws  Exception
	 *
	 * @return  CMSObject  The button options as CMSObject.
	 *
	 * @since   1.1.0
	 */
	public function onDisplay($name, $asset, $author)
	{
		$app = Factory::getApplication();
		if(!$app->isClient('administrator'))
		{
			return;
		}

		$user = Factory::getUser();

		// Can create in any category (component permission) or at least in one category
		$canCreateRecords = $user->authorise('core.create', 'com_content')
			|| count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

		// Instead of checking edit on all records, we can use **same** check as the form editing view
		$values           = (array) Factory::getApplication()->getUserState('com_content.edit.article.id');
		$isEditingRecords = count($values);

		// This ACL check is probably a double-check (form view already performed checks)
		$hasAccess = $canCreateRecords || $isEditingRecords;
		if (!$hasAccess)
		{
			return;
		}

		$function = 'function(){}';

		$link = 'index.php?option=com_ajax&amp;plugin=quantummanagerbutton&amp;group=editors-xtd&amp;format=html&amp;tmpl=component&amp;plugin.task=getmodal&amp;e_name=' . $name . '&amp;asset=com_content&amp;author='
			. Session::getFormToken() . '=1&amp;function=' . $function;

		$button          = new CMSObject();
		$button->modal   = true;
		$button->class   = 'btn';
		$button->link    = $link;
		$button->text    = Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_BUTTON');
		$button->name    = 'file-add';
		$button->options = "{handler: 'iframe', size: {x: 1450, y: 700}, classWindow: 'quantummanager-modal-sbox-window'}";

		$label = Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_BUTTON');

		Factory::getDocument()->addStyleDeclaration(<<<EOT
@media screen and (max-width: 1540px) {
	.mce-window[aria-label="{$label}"] {
		left: 2% !important;
		right: 0 !important;
		width: 95% !important;
	}
	
	.mce-window[aria-label="{$label}"] .mce-reset
	{
		width: 100% !important;
		height: 100% !important;
	}
	
	.mce-window[aria-label="{$label}"] .mce-window-body {
		width: 100% !important;
		height: calc(100% - 96px) !important;
	}
	
	.mce-window[aria-label="{$label}"] .mce-foot {
		width: 100% !important;
	}
	
	.mce-window[aria-label="{$label}"] .mce-foot .mce-container-body {
		width: 100% !important;
	}
	
	.mce-window[aria-label="{$label}"] .mce-foot .mce-container-body .mce-widget {
		left: auto !important;
		right: 18px !important;
	}
}

@media screen and (max-height: 700px) {

	.mce-window[aria-label="{$label}"] {
		top: 2% !important;
		height: 95% !important;
	}
		
	.mce-window[aria-label="{$label}"] .mce-window-body {
		height: calc(100% - 96px) !important;
	}
			
}


EOT
);
		return $button;
	}



	public function onAjaxQuantummanagerbutton()
	{

		JLoader::register('QuantummanagerHelper', JPATH_ROOT . '/administrator/components/com_quantummanager/helpers/quantummanager.php');
		JLoader::register('QuantummanagerbuttonHelper', JPATH_ROOT . '/plugins/editors-xtd/quantummanagerbutton/helper.php');

		$app = Factory::getApplication();
		$data = $app->input->getArray();
		$task = $app->input->get('plugin_task');
		$html = '';

		$app = Factory::getApplication();
		if(!$app->isClient('administrator'))
		{
			return;
		}

		if($task === 'getmodal')
		{
			QuantummanagerHelper::loadlang();
			$layout = new FileLayout('default', JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, [
					'plugins', 'editors-xtd', 'quantummanagerbutton', 'tmpl'
				]));
			echo $layout->render();
		}

		if($task === 'prepareforcontent')
		{
			if(!isset($data['params'], $data['scope']))
			{
				$app->close();
			}

			$scope = $data['scope'];
			$params = json_decode($data['params'], JSON_OBJECT_AS_ARRAY);
			$file = QuantummanagerHelper::preparePath($data['path'], false, $scope, true);
			$name = explode('/', $file);
			$filename = end($name);
			$type = explode('.', $file);
			$filetype = end($type);
			$filesize = filesize(JPATH_ROOT . '/' . $file);
			$scopesTemplate = $this->params->get('scopes', QuantummanagerbuttonHelper::defaultValues());
			$variables = [];
			$variablesParams = [];
			$html = '';

			$shortCode = false;
			$template = '<a href="{file}" target="_blank">{name}</a>';

			foreach ($scopesTemplate as $scopesTemplateCurrent)
			{

				if($scopesTemplateCurrent->id === $scope)
				{

					if(empty($scopesTemplateCurrent->templatelist))
					{
						foreach($params['files'] as $item)
						{
							$file = QuantummanagerHelper::preparePath($data['path'], false, $scope, true) . DIRECTORY_SEPARATOR . $item['file'];
							$name = explode('/', $file);
							$filename = end($name);
							$type = explode('.', $file);
							$filetype = end($type);
							$filesize = filesize(JPATH_ROOT . '/' . $file);

							$variables = [
								'{file}' => $file,
								'{filename}' => $filename,
								'{type}' => $filetype,
								'{size}' => QuantummanagerHelper::formatFileSize($filesize),
							];


							foreach ($item['fields'] as $key => $value)
							{
								if (preg_match("#^\{.*?\}$#isu", $key))
								{
									$variables[$key] = trim($value);
								}
							}

							$template = '<a href="{file}" target="_blank">{name}</a>';
							$variablesFind = [];
							$variablesReplace = [];

							foreach ($variables as $key => $value)
							{
								$variablesFind[] = $key;
								$variablesReplace[] = $value;
							}

							$template = str_replace($variablesFind, $variablesReplace, $template);
							$html .= preg_replace("#[\s\040]?[a-zA-Z0-9]{1,}\=\"\"#isu", '', $template);
						}
					}
					else
					{
						foreach ($scopesTemplateCurrent->templatelist as $templateList)
						{
							if($templateList->templatename === $params['template'])
							{
								//собираем по выбранному шаблону
								$templatebefore = '';
								$templateitems = '';
								$templateafter = '';

								if(preg_match("#^\{\{.*?\}\}$#isu", trim($templateList->templatebefore)))
								{
									$templatebefore = '[before]' .$templateList->templatebefore . '[/before]';
									$shortCode = true;
								}
								else
								{
									$templatebefore = $templateList->templatebefore;
								}

								$variablesForTemplate = [];
								foreach($params['files'] as $item)
								{
									$file = QuantummanagerHelper::preparePath($data['path'], false, $scope, true) . DIRECTORY_SEPARATOR . $item['file'];
									$name = explode('/', $file);
									$filename = end($name);
									$type = explode('.', $file);
									$filetype = end($type);
									$filesize = filesize(JPATH_ROOT . '/' . $file);

									$variables = [
										'{file}' => $file,
										'{filename}' => $filename,
										'{type}' => $filetype,
										'{size}' => QuantummanagerHelper::formatFileSize($filesize),
									];

									foreach ($item['fields'] as $key => $value)
									{
										if(preg_match("#^\{.*?\}$#isu", $key))
										{
											$variables[$key] = trim($value);
										}
									}

									$variablesFind = [];
									$variablesReplace = [];

									foreach ($variables as $key => $value)
									{
										$variablesFind[] = $key;
										$variablesReplace[] = $value;
									}

									foreach ($variables as $key => $value)
									{
										$variables[$key] = str_replace($variablesFind, $variablesReplace, $value);
									}

									$variablesFind = [];
									$variablesReplace = [];

									foreach ($variables as $key => $value)
									{
										$variablesFind[] = $key;
										$variablesReplace[] = $value;
									}

									if(preg_match("#^\{\{.*?\}\}$#isu", trim($templateList->template)))
									{
										$shortCode = true;
										$variablesForTemplate[] = $variables;
									}
									else
									{
										$item = str_replace($variablesFind, $variablesReplace, $templateList->template);
										$item = preg_replace("#[\s\040]?[a-zA-Z0-9]{1,}\=\"\"#isu", '', $item);
										$templateitems .= $item;
									}

								}

								if($shortCode)
								{
									$templateitems = '[item][variables]' . json_encode($variablesForTemplate) . '[/variables][template]' . $templateList->template . '[/template][/item]';
								}

								if(preg_match("#^\{\{.*?\}\}$#isu", trim($templateList->templateafter)))
								{
									$templateafter = '[after]' . $templateList->templateafter . '[/after]';
									$shortCode = true;
								}
								else
								{
									$templateafter = $templateList->templateafter;
								}

								if($shortCode)
								{
									$html = '[qmcontent]' . $templatebefore . $templateitems . $templateafter . '[/qmcontent]';
								}
								else
								{
									$html = $templatebefore . $templateitems . $templateafter;
								}

							}
						}
					}

				}
			}

			echo $html;

			$app->close();
		}

	}


}
