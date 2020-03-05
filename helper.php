<?php

/**
 * @package    quantummanagerbutton
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

class QuantummanagerbuttonHelper
{


	/**
	 * @var
	 * @since version
	 */
	public static $template;


	public static function loadLang()
	{
		Factory::getLanguage()->load('plg_editors-xtd_quantummanagerbutton', JPATH_ADMINISTRATOR);
	}


	/**
	 *
	 * @return array
	 *
	 * @since version
	 */
	public static function getFieldsForScopes()
	{

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('params')))
			->from('#__extensions')
			->where( 'element=' . $db->quote('quantummanagerbutton'));
		$extension = $db->setQuery( $query )->loadObject();
		$params = json_decode($extension->params, JSON_OBJECT_AS_ARRAY);

		if(!isset($params['scopes']) || empty($params['scopes']) || count((array)$params['scopes']) === 0)
		{
			$scopes = self::defaultValues();
		}
		else
		{
			$scopes = $params['scopes'];
		}

		$output = [];

		foreach ($scopes as $scope)
		{
			$scope = (array)$scope;
			$output[$scope['id']] = [
				'title' => $scope['title'],
				'fieldsform' => $scope['fieldsform']
			];
		}

		return $output;
	}


	/**
	 *
	 * @return array
	 *
	 * @since version
	 */
	public static function getTemplateListForScopes()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('params')))
			->from('#__extensions')
			->where( 'element=' . $db->quote('quantummanagerbutton'));
		$extension = $db->setQuery( $query )->loadObject();
		$params = json_decode($extension->params, JSON_OBJECT_AS_ARRAY);

		if(!isset($params['scopes']) || empty($params['scopes']) || count((array)$params['scopes']) === 0)
		{
			$scopes = self::defaultValues();
		}
		else
		{
			$scopes = $params['scopes'];
		}

		$output = [];

		foreach ($scopes as $scope)
		{
			$scope = (array)$scope;

			$templatelist = [];
			$templatelistFromScope = $scope['templatelist'];

			foreach ($templatelistFromScope as $keyTemplate => $template)
			{
				$templateItem = '';
				if(preg_match("#^\{.*?\}$#isu", trim($template['template'])))
				{
					$layoutId = str_replace(['{', '}'], '', $template['template']);
					$templateItem = self::renderLayout($layoutId);
				}
				else
				{
					$templateItem = $template['template'];
				}

				$enablefields = [];
				$matches = [];
				preg_match_all("#\{(.*?)\}#isu", $templateItem, $matches);

				if(isset($matches[1]))
				{
					foreach ($matches[1] as $findField)
					{
						if(!in_array($findField, $enablefields))
						{
							$enablefields[] = $findField;
						}
					}

				}

				$templatelist[] = [
					'name' => $template['templatename'],
					'enablefields' => $enablefields,
				];

			}

			$output[$scope['id']] = [
				'title' => $scope['title'],
				'templatelist' => $templatelist
			];
		}

		return $output;
	}


	/**
	 * @param $layoutId
	 *
	 * @return string
	 *
	 * @throws Exception
	 * @since version
	 */
	public static function renderLayout($layoutId)
	{
		$app = Factory::getApplication();
		$template = $app->getTemplate();

		if(empty(self::$template))
		{
			$db = Factory::getDbo();
			$query = $db->getQuery( true );
			$query->select( 'template' )
				->from( '#__template_styles as e' )
				->where( 'e.client_id = 0')
				->where( 'e.home = 1')
				->setLimit(1);
			$db->setQuery($query);
			$template = $db->loadObject();
			if(isset($template->template))
			{
				self::$template = $template->template;
			}
		}

		$layout = new FileLayout($layoutId);
		$layout->addIncludePath([
			JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['templates', self::$template, 'layouts' , 'plg_quantummanagcontent']),
			JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['templates', self::$template, 'html' , 'layouts', 'plg_quantummanagcontent']),
			JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['templates', self::$template, 'html' , 'layouts', 'plg_content_quantummanagercontent']),
			JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['templates', self::$template, 'html' , 'plg_content_quantummanagercontent']),
			JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['templates', self::$template, 'html' , 'plg_quantummanagcontent']),
			JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['templates', self::$template, 'html' , 'plg_button_quantummanagerbutton']),
		]);

		$output = $layout->render();
		return $output;
	}


	/**
	 *
	 * @return array
	 *
	 * @since version
	 */
	public static function defaultValues()
	{
		$lang = Factory::getLanguage();
		$lang->load('plg_editors-xtd_quantummanagerbutton', JPATH_ADMINISTRATOR);
		$lang->load('com_quantummanager', JPATH_ROOT . '/administrator/components/com_quantummanager');

		return [
			'images' => (object)[
				'id' => 'images',
				'title' => Text::_('COM_QUANTUMMANAGER_SCOPE_IMAGES'),
				'templatelist' => [
					'templatelist0' => [
						'templatename' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_NAME_IMAGE'),
						'templatebefore' => '',
						'template' => '<img src="{file}" alt="{alt}" width="{width}" height="{height}" />',
						'templateafter' => '',
					]
				],
				'fieldsform' => [
					'fieldsform0' => [
						'nametemplate' => 'width',
						'name' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_IMAGES_FIELDSFORM_WIDTH_NAME'),
						'default' => '',
						'type' => 'number',
					],
					'fieldsform1' => [
						'nametemplate' => 'height',
						'name' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_IMAGES_FIELDSFORM_HEIGHT_NAME'),
						'default' => '',
						'type' => 'number',
					],
					'fieldsform3' => [
						'nametemplate' => 'alt',
						'name' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_IMAGES_FIELDSFORM_ALT_NAME'),
						'default' => '',
						'type' => 'text',
					]
				]
			],
			'docs' => (object)[
				'id' => 'docs',
				'title' => Text::_('COM_QUANTUMMANAGER_SCOPE_DOCS'),
				'templatelist' => [
					'templatelist0' => [
						'templatename' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_NAME_DOC'),
						'templatebefore' => '',
						'template' => '<a href="{file}" target="_blank">{name}</a>',
						'templateafter' => '',
					]
				],
				'fieldsform' => [
					'fieldsform0' => [
						'nametemplate' => 'name',
						'name' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_DOCS_FIELDSFORM_NAME_NAME'),
						'default' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_IMAGES_FIELDSFORM_DEFAULT_NAME'),
						'type' => 'text',
					],
				]
			],
			'music' => (object)[
				'id' => 'music',
				'title' => Text::_('COM_QUANTUMMANAGER_SCOPE_MUSIC'),
				'templatelist' => [
					'templatelist0' => [
						'templatename' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_NAME_AUDIO'),
						'templatebefore' => '',
						'template' => '<audio controls src="{file}"> ' . Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_MUSIC_TEMPLATE_TEXT') . '</audio>',
						'templateafter' => '',
					]
				],
				'fieldsform' => '',
			],
			'videos' => (object)[
				'id' => 'videos',
				'title' => Text::_('COM_QUANTUMMANAGER_SCOPE_VIDEOS'),
				'templatelist' => [
					'templatelist0' => [
						'templatename' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_NAME_VIDEO'),
						'templatebefore' => '',
						'template' => '<video src="{file}" autoplay>' . Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_VIDEOS_TEMPLATE_TEXT') . '</video>',
						'templateafter' => '',
					]
				],
				'fieldsform' => '',
			]
		];
	}


}