<?php
/*
 * @package    plg_system_adminpath
 * @version    1.0.0
 * @author     Ilya A.Zhulin <ilya.zhulin@hotmail.com>
 * @copyright  ©Ilya A.Zhulin, 2026
 * @license    GNU General Public License version 2 or later;
 *
 * The last change: 12.02.2026, 16:06
 */

namespace Joomla\Plugin\System\AdminPath\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;

defined('_JEXEC') or die;

class AdminPath extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterInitialise'    => 'onAfterInitialise',
			'onContentPrepareForm' => 'onContentPrepareForm',
			'onAjaxAdminpath'      => 'onAjaxAdminpath',
			'onBeforeCompileHead'  => 'onBeforeCompileHead',
		];
	}

	/**
	 * AJAX handler for session status check
	 *
	 * @param   EventInterface  $event  The event object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onAjaxAdminpath()
	{
		$app = Factory::getApplication();

		if (!$app->isClient('administrator'))
		{
			echo new JsonResponse('', 'Client is not administrator', true);
			$app->close();
		}

		$session = $app->getSession();
		$allowed = $session->get('adminpath_allowed', 0);

		echo new JsonResponse((bool) $allowed, '', false);

		$app->close();
	}

	/**
	 * Before compile head event - load language strings for JavaScript
	 *
	 * @param   EventInterface  $event  The event object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onBeforeCompileHead(EventInterface $event = null)
	{
		$app = Factory::getApplication();

		if (!$app->isClient('administrator'))
		{
			return;
		}

		$input = $app->getInput();

		// Загружаем языковые строки только на странице настроек плагина
		if ($input->get('option') === 'com_plugins' &&
			$input->get('view') === 'plugin')
		{
			// Получаем текущий плагин из базы
			$pluginId = (int) $input->get('extension_id');
			$db       = Factory::getContainer()->get('DatabaseDriver');
			$query    = $db->getQuery(true)
				->select($db->quoteName('element'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('extension_id') . ' = :id')
				->bind(':id', $pluginId);
			$db->setQuery($query);
			$element = $db->loadResult();
			// Если это наш плагин
			if ($element === 'adminpath')
			{
				Text::script('PLG_SYSTEM_ADMINPATH_YOUR_ADMIN_URL');
				Text::script('PLG_SYSTEM_ADMINPATH_COPY_LINK');
				Text::script('PLG_SYSTEM_ADMINPATH_OPEN_LINK');
				Text::script('PLG_SYSTEM_ADMINPATH_COPIED');
				Text::script('PLG_SYSTEM_ADMINPATH_WARNING_BLOCK');
			}
		}
	}

	/**
	 * After initialise event - handle admin access with secret parameter
	 *
	 * @param   EventInterface  $event  The event object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onAfterInitialise(EventInterface $event = null)
	{
		$app = Factory::getApplication();

		// Работаем только в админке
		if (!$app->isClient('administrator'))
		{
			return;
		}
		
		$secretParam  = $this->params->get('secret_path', 'secretadmin');
		$blockDirect  = (bool) $this->params->get('block_direct', 0);
		$redirectType = $this->params->get('redirect_type', '404');
		$customUrl    = $this->params->get('custom_redirect_url', '');

		$input       = $app->getInput();
		$session     = $app->getSession();
		$uri         = Uri::getInstance();
		$currentPath = $uri->getPath();

		// Получаем все параметры запроса
		$allParams = $input->getArray();

		// Проверяем наличие секретного параметра
		$hasSecretParam = isset($allParams[$secretParam]);

		// Получаем флаг из сессии
		$allowed = $session->get('adminpath_allowed', 0);

		// Если есть секретный параметр - разрешаем доступ
		if ($hasSecretParam)
		{
			$session->set('adminpath_allowed', 1);

			// Удаляем секретный параметр из URL (чистый редирект)
			$cleanUri = clone $uri;
			$cleanUri->delVar($secretParam);

			$app->redirect($cleanUri->toString());
		}

		// Если прямой доступ заблокирован
		if ($blockDirect && !$allowed)
		{
			// Предотвращаем циклический редирект
			if (strpos($currentPath, '404') === false &&
				strpos($currentPath, 'error') === false)
			{
				$root = Uri::root();

				switch ($redirectType)
				{
					case 'home':
						$redirectUrl = $root;
						break;
					case 'custom':
						$redirectUrl = $customUrl ?: $root;
						break;
					default:
						$redirectUrl = $root . '404';
						break;
				}

				$app->redirect($redirectUrl);
			}
		}
	}

	/**
	 * Content prepare form event - load JavaScript for admin URL display
	 *
	 * @param   EventInterface  $event  The event object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepareForm(EventInterface $event)
	{
		$app = Factory::getApplication();

		if (!$app->isClient('administrator'))
		{
			return;
		}

		$form = $event->getArgument('0');
		$data = $event->getArgument('1');

		// Загружаем скрипт только для формы нашего плагина
		if ($form && $form->getName() === 'com_plugins.plugin')
		{
			if (is_object($data) && isset($data->element) && $data->element === 'adminpath')
			{
				$wa = $app->getDocument()->getWebAssetManager();
				$wa->registerAndUseScript(
					'plg_system.adminpath.adminpath',
					'plg_system_adminpath/adminpath.min.js',
					[],
					['defer' => true],
					['core']
				);
			}
		}
	}


}
