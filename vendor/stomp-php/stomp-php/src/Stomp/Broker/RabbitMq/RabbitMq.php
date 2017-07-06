<?php

/*
 * This file is part of the Stomp package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stomp\Broker\RabbitMq;

use Stomp\Protocol\Protocol;
use Stomp\Transport\Frame;

/**
 * RabbitMq Stomp dialect.
 *
 *
 * @package Stomp
 * @author Hiram Chirino <hiram@hiramchirino.com>
 * @author Dejan Bosanac <dejan@nighttale.net>
 * @author Michael Caplan <mcaplan@labnet.net>
 * @author Jens Radtke <swefl.oss@fin-sn.de>
 */
class RabbitMq extends Protocol
{

    /**
     * Prefetch Size for subscriptions.
     *
     * @var int
     */

    private $prefetchCount = 1;

    /**
     * RabbitMq subscribe frame.
     *
     * @param string $destination
     * @param string $subscriptionId
     * @param string $ack
     * @param string $selector
     * @param boolean|false $durable durable subscription
     * @return Frame
     */
    public function getSubscribeFrame(
        $destination,
        $subscriptionId = null,
        $ack = 'auto',
        $selector = null,
        $durable = false
    ) {
        $frame = parent::getSubscribeFrame($destination, $subscriptionId, $ack, $selector);
        $frame['prefetch-count'] = $this->prefetchCount;
        if ($durable) {
            $frame['persistent'] = 'true';
        }
        return $frame;
    }

    /**
     * RabbitMq unsubscribe frame.
     *
     * @param string $destination
     * @param string $subscriptionId
     * @param bool|false $durable
     * @return \Stomp\Transport\Frame
     */
    public function getUnsubscribeFrame($destination, $subscriptionId = null, $durable = false)
    {
        $frame = parent::getUnsubscribeFrame($destination, $subscriptionId);
        if ($durable) {
            $frame['persistent'] = 'true';
        }
        return $frame;
    }


    /**
     * Prefetch Count for subscriptions
     *
     * @return int
     */
    public function getPrefetchCount()
    {
        return $this->prefetchCount;
    }

    /**
     * Prefetch Count for subscriptions
     *
     * @param int $prefetchCount
     */
    public function setPrefetchCount($prefetchCount)
    {
        $this->prefetchCount = $prefetchCount;
    }
}
