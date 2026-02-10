<?php
/*
 * @package    plg_system_adminpath
 * @version    __DEPLOY_VERSION__
 * @author     Ilya A.Zhulin <ilya.zhulin@hotmail.com>
 * @copyright  ©Ilya A.Zhulin, 2026
 * @license    GNU General Public License version 2 or later;
 *
 * The last change: 10.02.2026, 23:52
 */

namespace Joomla\Plugin\System\AdminPath\Extension;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherInterface;

defined('_JEXEC') or die;

/**
 * AdminPath plugin to protect administrator access
 *
 * @since  1.0.0
 */
class AdminPath extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;

	/**
	 * Session object
	 *
	 * @var    Session
	 * @since  1.0.0
	 */
	protected $session;

	/**
	 * Secret path for admin access
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $secretPath = 'admin-bolashaq-2026';

	/**
	 * Block direct access flag
	 *
	 * @var    bool
	 * @since  1.0.0
	 */
	protected $blockDirect = true;

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  $subject  The object to observe
	 * @param   array                $config   An optional associative array of configuration settings
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$subject, $config = [])
	{
		parent::__construct($subject, $config);

		$this->secretPath  = $this->params->get('secret_path', 'admin-bolashaq-2026');
		$this->blockDirect = (bool) $this->params->get('block_direct', 1);
		$this->session     = Factory::getSession();
	}

	/**
	 * After initialise event - check admin access
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onAfterInitialise()
	{
		// Only in administrator
		if (!$this->app->isClient('administrator'))
		{
			return;
		}

		// If blocking direct access
		if ($this->blockDirect)
		{
			$allowed = $this->session->get('adminpath_allowed', false);

			if (!$allowed)
			{
				// Get 404 page URL
				$uri  = Uri::getInstance();
				$path = $uri->getPath();

				// Check if we're already on 404 to prevent loop
				if (strpos($path, '404') === false && strpos($path, 'error') === false)
				{
					$this->app->redirect(Uri::root() . '404');
					$this->app->close();
				}
			}
		}
	}

	/**
	 * After route event - handle secret path access
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onAfterRoute()
	{
		// Only in frontend
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$uri  = Uri::getInstance();
		$path = trim($uri->getPath(), '/');

		// Remove language prefix if exists
		$path = preg_replace('#^(ru|kk)/#', '', $path);

		// Check if secret path matches
		if ($path === $this->secretPath)
		{
			// Set session flag
			$this->session->set('adminpath_allowed', true);

			// Redirect to administrator
			$this->app->redirect(Uri::root() . 'administrator');
		}
	}
}
