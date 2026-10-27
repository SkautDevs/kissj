<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddProgrammeSections extends AbstractMigration
{
    public function up(): void
    {
        $this->table('programme_section')
            ->addColumn('event_id', 'integer', ['null' => false])
            ->addForeignKey('event_id', 'event', ['id'], ['constraint' => 'programme_section_event_id_fk'])
            ->addColumn('name', 'text', ['null' => false])
            ->addColumn('subtitle', 'text', ['null' => true])
            ->addColumn('is_exclusive', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('sort_order', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('created_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->create();

        $this->table('programme')
            ->addColumn('section_id', 'integer', ['null' => true])
            ->update();

        $this->execute(
            'INSERT INTO programme_section (event_id, name, is_exclusive, sort_order, created_at, updated_at)'
            . " SELECT DISTINCT event_id, 'Program', FALSE, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP FROM programme"
            . ' WHERE exclusive_group_name IS NULL'
        );
        $this->execute(
            'INSERT INTO programme_section (event_id, name, is_exclusive, sort_order, created_at, updated_at)'
            . ' SELECT g.event_id, g.exclusive_group_name, TRUE,'
            . ' (SELECT COUNT(DISTINCT p.exclusive_group_name) FROM programme p'
            . ' WHERE p.event_id = g.event_id AND p.exclusive_group_name <= g.exclusive_group_name),'
            . ' CURRENT_TIMESTAMP, CURRENT_TIMESTAMP'
            . ' FROM (SELECT DISTINCT event_id, exclusive_group_name FROM programme'
            . ' WHERE exclusive_group_name IS NOT NULL) g'
        );
        $this->execute(
            'UPDATE programme SET section_id = (SELECT s.id FROM programme_section s'
            . " WHERE s.event_id = programme.event_id AND s.is_exclusive = FALSE AND s.name = 'Program')"
            . ' WHERE exclusive_group_name IS NULL'
        );
        $this->execute(
            'UPDATE programme SET section_id = (SELECT s.id FROM programme_section s'
            . ' WHERE s.event_id = programme.event_id AND s.is_exclusive = TRUE'
            . ' AND s.name = programme.exclusive_group_name)'
            . ' WHERE exclusive_group_name IS NOT NULL'
        );

        $this->table('programme')
            ->changeColumn('section_id', 'integer', ['null' => false])
            ->addForeignKey('section_id', 'programme_section', ['id'], ['constraint' => 'programme_section_id_fk'])
            ->addIndex(['section_id'], ['name' => 'programme_section_idx'])
            ->removeColumn('exclusive_group_name')
            ->update();
    }

    public function down(): void
    {
        $this->table('programme')
            ->addColumn('exclusive_group_name', 'text', ['null' => true])
            ->update();

        $this->execute(
            'UPDATE programme SET exclusive_group_name = (SELECT s.name FROM programme_section s'
            . ' WHERE s.id = programme.section_id AND s.is_exclusive = TRUE)'
        );

        $this->table('programme')
            ->dropForeignKey('section_id')
            ->removeIndexByName('programme_section_idx')
            ->removeColumn('section_id')
            ->update();

        $this->table('programme_section')->drop()->save();
    }
}
