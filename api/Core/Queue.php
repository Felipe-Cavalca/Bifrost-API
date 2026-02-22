<?php

namespace Bifrost\Core;

use Bifrost\Integration\Queue\RedisQueue;
use Bifrost\Interface\Queue as QueueInterface;

class Queue extends RedisQueue implements QueueInterface
{
}
