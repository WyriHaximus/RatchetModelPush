<?php

/**
 * This file is part of RatchetModelPush for CakePHP.
 *
 ** (c) 2013 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

App::uses('ModelBehavior', 'Model');
App::uses('TransportProxy', 'RatchetCommands.Lib/MessageQueue/Transports');
App::uses('RatchetMessageQueueModelUpdateCommand', 'RatchetModelPush.Lib/MessageQueue/Command');

class PushableBehavior extends ModelBehavior {

/**
 * Default options
 *
 * @var array
 */
	private $__defaults = array(
		'events' => array(),
	);

/**
 * Assign the combined defaults and pass settings to the linked model
 *
 * @param Model $Model
 * @param array $settings
 */
	public function setup(Model $Model, $settings = array()) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = $this->__defaults;
		}
		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], (array)$settings);
	}

/**
 * afterSave hook walking throught all the defined events for this model
 *
 * @param Model $Model
 * @param boolean $created
 */
	public function afterSave(Model $Model, $created) {
		array_walk($this->settings[$Model->alias]['events'], array($this, '_afterSaveEventCheck'), array(
			'id' => $Model->id,
			'data' => $Model->data,
			'created' => $created,
			'model' => $Model,
		));
	}

/**
 * Checking method called by afterSave, checks wether to fire the event or not and assigns the right data set to send with the event
 *
 * @param array $event
 * @param int $key
 * @param array $data
 * @return void
 */
	protected function _afterSaveEventCheck($event, $key, $data) {
		if (isset($event['created']) && $event['created'] !== $data['created']) {
			return;
		}

		if (isset($event['refetch']) && $event['refetch']) {
			$resultSet = $data['model']->findById($data['id']);
		} else {
			$resultSet = $data['data'];
		}

		$eventName = $this->_afterSavePrepareEventName($event['eventName'], $data['id'], $resultSet[$data['model']->alias]);

		$this->_afterSaveDispatchEvent($eventName, $resultSet);
	}

/**
 * Prepares the $eventName with any data from fields used in the $eventName
 *
 * @param string $eventName
 * @param int $id
 * @param array $data
 * @return string
 */
	protected function _afterSavePrepareEventName($eventName, $id, $data) {
		$before = array(
			'{id}',
		);
		$after = array(
			$id,
		);

		foreach ($data as $key => $value) {
			$before[] = '{' . $key . '}';
			$after[] = $value;
		}

		return str_replace($before, $after, $eventName);
	}

/**
 * Dispatches the event to the Ratchet server instance acompanied by the model data
 *
 * @param string $eventName
 * @param array $eventData
 */
	protected function _afterSaveDispatchEvent($eventName, $eventData) {
		$command = new RatchetMessageQueueModelUpdateCommand();
		$command->setEvent($eventName);
		$command->setData($eventData);
		TransportProxy::instance()->queueMessage($command);
	}

}
