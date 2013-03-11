<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\Storage;
use Joomla\Factory;
use RuntimeException;

/**
 * Memcache session storage handler for PHP
 *
 * @since  1.0
 */
class Memcache extends Storage
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   1.0
	 * @throws  RuntimeException
	 */
	public function __construct($options = array())
	{
		if (!self::isSupported())
		{
			throw new RuntimeException('Memcache Extension is not available', 404);
		}

		parent::__construct($options);

		$config = Factory::getConfig();

		// This will be an array of loveliness
		// @todo: multiple servers
		$this->_servers = array(
			array(
				'host' => $config->get('memcache_server_host', 'localhost'),
				'port' => $config->get('memcache_server_port', 11211)
			)
		);
	}

	/**
	 * Register the functions of this class with PHP's session handler
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function register()
	{
		ini_set('session.save_path', $this->_servers['host'] . ':' . $this->_servers['port']);
		ini_set('session.save_handler', 'memcache');
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	static public function isSupported()
	{
		return (extension_loaded('memcache') && class_exists('Memcache'));
	}
}
