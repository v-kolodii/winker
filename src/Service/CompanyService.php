<?php

namespace App\Service;

use App\Doctrine\CompanyEntityManager;
use App\Entity\Company;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CompanyService
{
    private string $prefix;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CompanyEntityManager   $companyEntityManagerService,
        ParameterBagInterface                   $parameterBag)
    {
        $this->prefix = $parameterBag->get('customer_db_prefix');
    }

    /**
     * @throws Exception|MissingMappingDriverImplementation
     */
    public function addNewDbForCompany(Company $company): void
    {
        $name = sprintf('%s%s', $this->prefix, $company->getId());

        $schemaManager = $this->entityManager->getConnection()->createSchemaManager();

        $schemaManager->createDatabase($name);

        $company->setDbUrl($name);
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        $this->runSql($name, $company->getId());
    }

    /**
     * @throws MissingMappingDriverImplementation
     * @throws Exception
     */
    private function runSql(string $dbName, int $id): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->changeDatabase($dbName);

        $newManager = $this->companyEntityManagerService->getEntityManager();

        $connection = $newManager->getConnection();

        foreach ($this->getSqls($id) as $sql) {
            $connection->executeStatement($sql);
        }
    }

    private function getSqls(int $id): array
    {
        return [
            'CREATE SEQUENCE task_id_seq_' . $id . ' INCREMENT BY 1 MINVALUE 1 START 1',
            'CREATE SEQUENCE task_has_comment_id_seq_' . $id . ' INCREMENT BY 1 MINVALUE 1 START 1',
            'CREATE SEQUENCE task_has_file_id_seq_' . $id . ' INCREMENT BY 1 MINVALUE 1 START 1',
            'CREATE SEQUENCE task_history_id_seq_' . $id . ' INCREMENT BY 1 MINVALUE 1 START 1',
            'CREATE TABLE task (id INT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, task_type SMALLINT DEFAULT NULL, type_base_plane_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type_reg_daily_finished_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type_reg_weekly_day VARCHAR(8) DEFAULT NULL, type_reg_weekly_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, type_reg_month_day INT DEFAULT NULL, type_reg_month_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL, finished_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, wink_type SMALLINT DEFAULT NULL, status SMALLINT DEFAULT NULL, user_id INT NOT NULL, performer_id INT DEFAULT NULL, PRIMARY KEY(id))',
            'CREATE INDEX IDX_527EDB25727ACA70_' . $id . '  ON task (parent_id)',
            'COMMENT ON COLUMN task.task_type IS \'0:Typical, 1:Regular(DC2Type:SMALLINT)\'',
            'COMMENT ON COLUMN task.wink_type IS \'0:Medium, 1:High, 2:Asap(DC2Type:SMALLINT)\'',
            'COMMENT ON COLUMN task.status IS \'0:New, 1:InProgress, 2:Finished(DC2Type:SMALLINT)\'',
            'CREATE TABLE task_has_comment (id INT NOT NULL, task_id INT DEFAULT NULL, comment TEXT DEFAULT NULL, user_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))',
            'CREATE INDEX IDX_B5F097858DB60186_' . $id . '  ON task_has_comment (task_id)',
            'CREATE TABLE task_has_file (id INT NOT NULL, task_id INT DEFAULT NULL, url VARCHAR(512) DEFAULT NULL, user_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))',
            'CREATE INDEX IDX_910307038DB60186_' . $id . '  ON task_has_file (task_id)',
            'CREATE TABLE task_history (id INT NOT NULL, task_id INT DEFAULT NULL, user_id INT DEFAULT NULL, history VARCHAR(516) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))',
            'ALTER TABLE task ADD CONSTRAINT FK_527EDB25727ACA70_' . $id . '  FOREIGN KEY (parent_id) REFERENCES task (id) NOT DEFERRABLE INITIALLY IMMEDIATE',
            'ALTER TABLE task_has_comment ADD CONSTRAINT FK_B5F097858DB60186_' . $id . '  FOREIGN KEY (task_id) REFERENCES task (id) NOT DEFERRABLE INITIALLY IMMEDIATE',
            'ALTER TABLE task_has_file ADD CONSTRAINT FK_910307038DB60186_' . $id . '  FOREIGN KEY (task_id) REFERENCES task (id) NOT DEFERRABLE INITIALLY IMMEDIATE',
        ];
    }
}