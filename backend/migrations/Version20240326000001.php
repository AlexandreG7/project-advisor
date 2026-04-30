<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240326000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create recommendations and advice_requests tables';
    }

    public function up(Schema $schema): void
    {
        $recommendations = $schema->createTable('recommendations');
        $recommendations->addColumn('id', 'string', ['length' => 36]);
        $recommendations->addColumn('answers_json', 'text');
        $recommendations->addColumn('result_json', 'text');
        $recommendations->addColumn('created_at', 'datetime');
        $recommendations->setPrimaryKey(['id']);

        $advice = $schema->createTable('advice_requests');
        $advice->addColumn('id', 'integer', ['autoincrement' => true]);
        $advice->addColumn('name', 'string', ['length' => 255, 'notnull' => false]);
        $advice->addColumn('email', 'string', ['length' => 255]);
        $advice->addColumn('subject', 'string', ['length' => 255]);
        $advice->addColumn('message', 'text');
        $advice->addColumn('recommendation_id', 'string', ['length' => 36, 'notnull' => false]);
        $advice->addColumn('questionnaire_snapshot', 'text', ['notnull' => false]);
        $advice->addColumn('status', 'string', ['length' => 50, 'default' => 'pending']);
        $advice->addColumn('created_at', 'datetime');
        $advice->addColumn('answered_at', 'datetime', ['notnull' => false]);
        $advice->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('advice_requests');
        $schema->dropTable('recommendations');
    }
}
