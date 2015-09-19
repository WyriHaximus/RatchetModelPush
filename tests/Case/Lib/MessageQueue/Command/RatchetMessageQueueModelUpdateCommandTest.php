<?php

/**
 * This file is part of RatchetModelPush for CakePHP.
 *
 ** (c) 2013 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

App::uses('RatchetMessageQueueModelUpdateCommand', 'RatchetModelPush.Lib/MessageQueue/Command');
App::uses('AbstractCommandTest', 'RatchetCommands.tests/Case/Lib/MessageQueue/Command');

class RatchetMessageQueueModelUpdateCommandTest extends AbstractCommandTest {

/**
 * {@inheritdoc}
 */
	public function setUp() {
		parent::setUp();

		$this->Command = new RatchetMessageQueueModelUpdateCommand();
		$this->Command->setEvent('Ratchet.Model.test.1');
		$this->Command->setData(array(
			'Model' => array(
				'id' => 1,
				'foo' => 'bar',
			),
		));
	}

}
