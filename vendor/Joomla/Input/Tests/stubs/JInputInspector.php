<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Input\Input;

/**
 * Inspector class for the JInput library.
 *
 * @since  1.0
 */
class JInputInspector extends Input
{
	public $options;

	public $filter;

	public $data;

	public $inputs;

	public static $registered;

	/**
	 * Test...
	 *
	 * @return void
	 */
	public static function register()
	{
		return parent::register();
	}
}
