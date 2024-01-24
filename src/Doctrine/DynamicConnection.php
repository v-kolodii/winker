<?php

namespace App\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;

class DynamicConnection extends Connection
{
    public function __construct(array $params, Driver $driver, $config, $eventManager)
    {
        parent::__construct($params, $driver, $config, $eventManager);
    }
    public function changeDatabase(string $dbName): void
    {
        $host = 'database';
        $port = 5432;
        $user = 'app';
        $password = 'root';
        $params = $this->getParams();
        if ($this->isConnected()) {
            $this->close();
        }

        $params['url'] = "postgresql://app:root@database:5432/".$dbName."?serverVersion=15&charset=utf8";
        $params['host'] = $host;
        $params['port'] = $port;
        $params['dbname'] = $dbName;
        $params['user'] = $user;
        $params['password'] = $password;

        parent::__construct(
            $params,
            $this->_driver,
            $this->_config,
            $this->_eventManager
        );
    }
}
