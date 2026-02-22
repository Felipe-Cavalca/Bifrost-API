<?php

namespace Bifrost\Interface;

interface Queue
{
    public function addToFront(Task $task): void;

    public function addToEnd(Task $task): void;

    public function addScheduledTask(Task $task, int $seconds): void;

    public function getNextTask(): ?Task;

    public function acknowledgeTask(Task $task): void;

    public function requeueTask(Task $task): void;
}
