<?php

namespace Joomla\Plugin\Button\QuantumManagerButton\Extension;

/**
 * @package    quantummanagerbutton
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\QuantumManager\Administrator\Helper\QuantummanagerHelper;
use Joomla\Plugin\Button\QuantumManagerButton\Helper\ButtonHelper;

class QuantumManagerButton extends CMSPlugin
{

	protected $app;

	protected $autoloadLanguage = true;

	protected $install_quantummanager = false;

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		if (file_exists(JPATH_SITE . '/administrator/components/com_quantummanager/services/provider.php'))
		{
			$this->install_quantummanager = true;
		}
	}

	public static function getSubscribedEvents(): array
	{
		return [
			'onDisplay'                  => 'onDisplay',
			'onAjaxQuantummanagerbutton' => 'onAjax',
		];
	}

	public function onEditorButtonsSetup(EditorButtonsSetupEvent $event): void
	{

		$subject = $event->getButtonsRegistry();
		$name    = $event->getEditorId();

		if (!$this->install_quantummanager)
		{
			return;
		}

		if (!$this->accessCheck())
		{
			return;
		}

		$function = 'function(){}';
		$link     = 'index.php?option=com_ajax&amp;plugin=quantummanagerbutton&amp;group=editors-xtd&amp;format=html&amp;tmpl=component&amp;plugin.task=getmodal&amp;e_name=' . $name . '&amp;asset=com_content&amp;author='
			. Factory::getApplication()->getSession()->getFormToken() . '=1&amp;function=' . $function;

		$button = new Button(
			$this->_name,
			[
				'modal'   => true,
				'class'   => 'btn',
				'link'    => $link,
				'text'    => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_BUTTON'),
				'icon'    => 'puzzle',
				'iconSVG' => '<svg viewBox="0 0 576 512" width="24" height="24"><path d="M519.442 288.651c-41.519 0-59.5 31.593-82.058 31.593C377.'
					. '409 320.244 432 144 432 144s-196.288 80-196.288-3.297c0-35.827 36.288-46.25 36.288-85.985C272 19.216 243.885 0 210.'
					. '539 0c-34.654 0-66.366 18.891-66.366 56.346 0 41.364 31.711 59.277 31.711 81.75C175.885 207.719 0 166.758 0 166.758'
					. 'v333.237s178.635 41.047 178.635-28.662c0-22.473-40-40.107-40-81.471 0-37.456 29.25-56.346 63.577-56.346 33.673 0 61'
					. '.788 19.216 61.788 54.717 0 39.735-36.288 50.158-36.288 85.985 0 60.803 129.675 25.73 181.23 25.73 0 0-34.725-120.1'
					. '01 25.827-120.101 35.962 0 46.423 36.152 86.308 36.152C556.712 416 576 387.99 576 354.443c0-34.199-18.962-65.792-56'
					. '.558-65.792z"></path></svg>',
				'name'    => $this->_type . '_' . $this->_name,
			]
		);

		$subject->add($button);
	}

	public function onAjaxQuantummanagerbutton(): void
	{

		if (!$this->install_quantummanager)
		{
			return;
		}

		$app  = Factory::getApplication();
		$data = $app->input->getArray();
		$task = $app->input->get('plugin_task');

		if (!$this->accessCheck())
		{
			return;
		}

		if ($task === 'getmodal')
		{
			QuantummanagerHelper::loadlang();
			$layout = new FileLayout('default', JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, [
					'plugins', 'editors-xtd', 'quantummanagerbutton', 'tmpl'
				]));
			echo $layout->render();
		}

		if ($task === 'prepareforcontent')
		{
			if (!isset($data['params'], $data['scope']))
			{
				$app->close();
			}

			$scope           = $data['scope'];
			$params          = json_decode($data['params'], JSON_OBJECT_AS_ARRAY);
			$file            = QuantummanagerHelper::preparePath($data['path'], false, $scope, true);
			$name            = explode('/', $file);
			$filename        = end($name);
			$type            = explode('.', $file);
			$filetype        = end($type);
			$filesize        = filesize(JPATH_ROOT . '/' . $file);
			$scopesTemplate  = $this->params->get('scopes', ButtonHelper::defaultValues());
			$scopesCustom    = $this->params->get('customscopes', []);
			$variables       = [];
			$variablesParams = [];
			$html            = '';

			$shortCode = false;
			$template  = '<a href="{file}" target="_blank">{name}</a>';

			if (is_array($scopesCustom))
			{
				$scopesCustom = [];
			}

			foreach ($scopesCustom as $scopeCustom)
			{
				$nameTmp                  = 'scopes' . count($scopesTemplate);
				$scopesTemplate->$nameTmp = $scopeCustom;
			}

			foreach ($scopesTemplate as $scopesTemplateCurrent)
			{

				$scopesTemplateCurrent = (object) $scopesTemplateCurrent;

				if ($scopesTemplateCurrent->id === $scope)
				{

					if (empty($scopesTemplateCurrent->templatelist))
					{
						foreach ($params['files'] as $item)
						{
							$file     = QuantummanagerHelper::preparePath($data['path'], false, $scope, true) . $item['file'];
							$name     = explode('/', $file);
							$filename = end($name);
							$type     = explode('.', $file);
							$filetype = mb_strtolower(end($type));
							$filesize = filesize(JPATH_ROOT . '/' . $file);

							$variables = [
								'{file}'     => $file,
								'{filename}' => $filename,
								'{type}'     => $filetype,
								'{size}'     => QuantummanagerHelper::formatFileSize($filesize),
							];

							if (file_exists(JPATH_ROOT . DIRECTORY_SEPARATOR . $file))
							{
								if (in_array($filetype, ['jpg', 'jpeg', 'png']))
								{
									list($width, $height, $type, $attr) = getimagesize(JPATH_ROOT . DIRECTORY_SEPARATOR . $file);
									$variables['{imagewidth}']  = $width;
									$variables['{imageheight}'] = $height;
								}
							}

							foreach ($item['fields'] as $key => $value)
							{
								if (preg_match("#^\{.*?\}$#isu", $key))
								{
									$variables[$key] = trim($value);
								}
							}

							$template         = '<a href="{file}" target="_blank">{name}</a>';
							$variablesFind    = [];
							$variablesReplace = [];

							foreach ($variables as $key => $value)
							{
								$variablesFind[]    = $key;
								$variablesReplace[] = $value;
							}

							$template = str_replace($variablesFind, $variablesReplace, $template);
							$html     .= preg_replace("#[\s\040]?[a-zA-Z0-9]{1,}\=\"\"#isu", '', $template);
						}
					}
					else
					{
						foreach ($scopesTemplateCurrent->templatelist as $templateList)
						{
							$templateList = (object) $templateList;
							if (isset($params['template']) && $templateList->templatename === $params['template'])
							{
								$templatebefore = '';
								$templateitems  = '';
								$templateafter  = '';
								$shortCode      = false;

								if (preg_match("#^\{\{.*?\}\}$#isu", trim($templateList->templatebefore)))
								{
									$templatebefore = '[before]' . $templateList->templatebefore . '[/before]';
									$shortCode      = true;
								}
								else
								{
									$templatebefore = $templateList->templatebefore;
								}

								$variablesForTemplate = [];
								foreach ($params['files'] as $item)
								{
									$file = QuantummanagerHelper::preparePath($data['path'], false, $scope, true) . DIRECTORY_SEPARATOR . $item['file'];

									if (!file_exists(JPATH_ROOT . DIRECTORY_SEPARATOR . $file))
									{
										continue;
									}

									$name     = explode(DIRECTORY_SEPARATOR, $file);
									$filename = end($name);
									$type     = explode('.', $file);
									$filetype = end($type);
									$filesize = filesize(JPATH_ROOT . DIRECTORY_SEPARATOR . $file);

									$variables = [
										'{file}'     => $file,
										'{filename}' => $filename,
										'{type}'     => $filetype,
										'{size}'     => QuantummanagerHelper::formatFileSize($filesize),
									];

									if (in_array($filetype, ['jpg', 'jpeg', 'png']))
									{
										list($width, $height, $type, $attr) = getimagesize(JPATH_ROOT . DIRECTORY_SEPARATOR . $file);
										$variables['{imagewidth}']  = $width;
										$variables['{imageheight}'] = $height;
									}

									foreach ($item['fields'] as $key => $value)
									{
										if (preg_match("#^\{.*?\}$#isu", $key))
										{
											$variables[$key] = trim($value);
										}
									}

									$variablesFind    = [];
									$variablesReplace = [];

									foreach ($variables as $key => $value)
									{
										$variablesFind[]    = $key;
										$variablesReplace[] = $value;
									}

									foreach ($variables as $key => $value)
									{
										$variables[$key] = str_replace($variablesFind, $variablesReplace, $value);
									}

									$variablesFind    = [];
									$variablesReplace = [];

									foreach ($variables as $key => $value)
									{
										$variablesFind[]    = $key;
										$variablesReplace[] = $value;
									}

									if (preg_match("#^\{\{.*?\}\}$#isu", trim($templateList->template)) || $shortCode)
									{
										$shortCode              = true;
										$variablesForTemplate[] = $variables;
									}
									else
									{
										$item          = str_replace($variablesFind, $variablesReplace, $templateList->template);
										$item          = preg_replace("#[\s\040]?[a-zA-Z0-9]{1,}\=\"\"#isu", '', $item);
										$templateitems .= $item;
									}

								}

								if ($shortCode)
								{
									$templateitems = '[item][variables]' . json_encode($variablesForTemplate) . '[/variables][template]' . $templateList->template . '[/template][/item]';
								}

								if (preg_match("#^\{\{.*?\}\}$#isu", trim($templateList->templateafter)))
								{
									$templateafter = '[after]' . $templateList->templateafter . '[/after]';
									$shortCode     = true;
								}
								else
								{
									$templateafter = $templateList->templateafter;
								}

								if ($shortCode)
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

	private function accessCheck()
	{
		if ($this->app->isClient('administrator'))
		{
			return true;
		}

		if (!(int) QuantummanagerHelper::getParamsComponentValue('front', 0))
		{
			return false;
		}

		if (Factory::getApplication()->getIdentity()->id === 0)
		{
			return false;
		}

		$actions = QuantummanagerHelper::getActions();

		if (!$actions->get('core.create'))
		{
			return false;
		}

		return true;
	}

}
