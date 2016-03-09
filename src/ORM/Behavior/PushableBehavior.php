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

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use React\EventLoop\LoopInterface;
use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

class PushableBehavior extends Behavior
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var bool
     */
    protected $loopRunning = false;

    public function initialize(array $config)
    {
        $this->loop = \WyriHaximus\Ratchet\loopResolver();
        $this->loop->futureTick(function () {
            $this->loopRunning = true;
        });
    }

    /**
     * @param Event $event
     * @param Entity $entity
     */
    public function afterSave(Event $event, Entity $entity)
    {
        $this->iterateRealms($entity);
    }

    /**
     * @param Entity $entity
     */
    protected function iterateRealms(Entity $entity)
    {
        if (count($this->config('events')) === 0) {
            return;
        }

        foreach ($this->config('realms') as $realm) {
            $client = $this->newClient($realm);
            $this->publishEvents($client, $entity);
            $client->start(false);
        }

        if (!$this->loopRunning) {
            $this->loop->run();
            $this->loopRunning = false;
            $this->loop->futureTick(function () {
                $this->loopRunning = true;
            });
        }
    }

    /**
     * @param string $realm
     *
     * @return Client
     * @throws \Exception
     */
    protected function newClient($realm)
    {
        $defaults = [
            'secure' => false,
        ];

        $options = array_merge(
            $defaults,
            Configure::read('WyriHaximus.Ratchet.realms.' . $realm),
            Configure::read('WyriHaximus.Ratchet.external')
        );

        $client = new Client($realm, $this->loop);
        $client->setReconnectOptions([
            'max_retries' => 0,
        ]);

        $client->addTransportProvider(
            new PawlTransportProvider(
                \WyriHaximus\Ratchet\createUrl($options['secure'], $options['hostname'], $options['port'], $options['path'])
            )
        );
        return $client;
    }

    /**
     * @param Client $client
     * @param Entity $entity
     */
    protected function publishEvents(Client $client, Entity $entity)
    {
        $type = $entity->isNew() ? 'create' : 'update';
        $client->on('open', function (ClientSession $session) use ($type, $entity) {
            foreach ($this->config('events') as $event) {
                if ($event['type'] == $type) {
                    $session->publish(
                        $this->prepareEventName(
                            $event['name'],
                            $entity->toArray()
                        ),
                        [
                            (array)$entity->toArray()
                        ]
                    );
                }
            }

            $session->close();
        });
    }

    /**
     * @param string $eventName
     * @param array $data
     * @return string
     */
    protected function prepareEventName($eventName, array $data)
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
