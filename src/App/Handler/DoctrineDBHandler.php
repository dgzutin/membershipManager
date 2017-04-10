<?php

namespace App\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\FormatterInterface;
use App\Entity\UserLog;

class DoctrineDBHandler extends AbstractProcessingHandler
{

    public function __construct($container)
    {
        $this->em = $container['em'];
    }
    /**
     * {@inheritDoc}
     */

    protected function write(array $record)
    {
        $userLog = new UserLog();
        $userLog->setDateTime($record['datetime']);
        $userLog->setLevel($record['level']);
        $userLog->setChannel($record['channel']);
        $userLog->setUserId($record['context']['user_id']);
        $userLog->setUserRole($record['context']['user_role']);
        $userLog->setType($record['context']['type']);
        $userLog->setMessage($record['message']);
        $userLog->setContext(json_encode($record['context']));

        $this->em->persist($userLog);
        $this->em->flush();
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter();
    }
}
