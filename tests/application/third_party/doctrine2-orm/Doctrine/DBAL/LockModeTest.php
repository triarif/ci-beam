<?php

namespace Doctrine\DBAL;

require_once dirname(__FILE__) . '/../../../../../../application/third_party/doctrine2-orm/Doctrine/DBAL/LockMode.php';

/**
 * Test class for LockMode.
 * Generated by PHPUnit on 2012-03-18 at 15:22:52.
 */
class LockModeTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var LockMode
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new LockMode;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		
	}

}

?>