<?php

namespace App\Service;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class DatabaseInternetService
{
    private $connection;

    public function __construct()
    {
        $this->connect();
    }

    /**
     * Configure et initialise une connexion manuelle à MySQL.
     *
     * @throws Exception
     */
    private function connect(): void
    {
        $connectionParams = [
            'dbname'   => 'florajet',
            'user'     => 'florajet',
            'password' => 'sMeU+gA2!6Mx94',
            'host'     => '172.16.42.20',
            'port'     => 3306,
            'driver'   => 'pdo_mysql',
        ];

        $this->connection = DriverManager::getConnection($connectionParams);
    }

    /**
     * Récupère les 10 dernières commandes par date décroissante.
     *
     * @return array
     * @throws Exception
     */
    public function getLast10Commands(): array
    {
        $sql = 'SELECT * FROM commande ORDER BY DateCommande DESC LIMIT 10';

        return $this->connection->fetchAllAssociative($sql);
    }

    public function countTodayCommands(): int
{
    $sql = 'SELECT COUNT(*) AS total FROM commande WHERE DATE(DateCommande) = CURDATE()';

    $result = $this->connection->fetchOne($sql);

    return (int) $result;
}

}
