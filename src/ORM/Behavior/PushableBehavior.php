<?php

/**
 * This file is part of RatchetModelPush for CakePHP.
 *
 ** (c) 2013 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WyriHaximus\Ratchet\ModelPush\ORM\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use React\Promise\FulfilledPromise;
use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

class PushableBehavior extends Behavior
{
    public function afterSave(Event $event, Entity $entity)
    {
        $this->publishEvents($entity, $entity->isNew() ? 'create' : 'update');
    }

    protected function publishEvents(Entity $entity, $type)
    {
        if (count($this->config('events')) === 0) {
            return;
        }

        $client = new Client('first');
        $client->setReconnectOptions([
            'max_retries' => 0,
        ]);
        $client->addTransportProvider(new PawlTransportProvider('ws://127.0.0.1:9000/'));

        $client->on('open', function (ClientSession $session) use ($type, $entity) {
            foreach ($this->config('events') as $event) {
                if ($event['type'] == $type) {
                    $session->publish($this->prepareEventName($event['name'], $entity->toArray()), [(array)$entity->toArray()]);
                }
            }

            $session->close();
        });

        $client->start();
    }

    protected function prepareEventName($eventName, $data)
    {
        $before = [];
        $after  = [];

        foreach ($data as $key => $value) {
            $before[] = '{' . $key . '}';
            $after[] = $value;
        }

        return str_replace($before, $after, $eventName);
    }
}
