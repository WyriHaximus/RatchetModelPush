<?php

/**
 * This file is part of RatchetModelPush for CakePHP.
 *
 ** (c) 2013 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

App::uses('RatchetMessageQueueCommand', 'RatchetCommands.Lib/MessageQueue/Command');

class RatchetMessageQueueModelUpdateCommand extends RatchetMessageQueueCommand {

	const EVENT_PREFIX = 'Rachet.WampServer.ModelUpdate.';

	public function serialize() {
		return serialize(array(
			'event' => $this->event,
			'data' => $this->data,
		));
	}

	public function unserialize($commandString) {
		$commandString = unserialize($commandString);
		$this->setEvent($commandString['event']);
		$this->setData($commandString['data']);
	}

	public function setEvent($event) {
		$this->event = $event;
	}

	public function getEvent() {
		return $this->event;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function getData() {
		return $this->data;
	}

	public function execute($eventSubject) {
		$that = $this;

		$eventSubject->getLoop()->addTimer(.5, function() use ($that, $eventSubject) {
			$topics = $eventSubject->getTopics();
			if (isset($topics[RatchetMessageQueueModelUpdateCommand::EVENT_PREFIX . $that->getEvent()])) {
				$topics[RatchetMessageQueueModelUpdateCommand::EVENT_PREFIX . $that->getEvent()]->broadcast($that->getData());
			}
		});

		return true;
	}

	public function response($response) {
	}
}
