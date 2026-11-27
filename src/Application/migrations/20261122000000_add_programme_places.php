<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddProgrammePlaces extends AbstractMigration
{
    public function up(): void
    {
        $this->table('programme_place')
            ->addColumn('event_id', 'integer', ['null' => false])
            ->addForeignKey('event_id', 'event', ['id'], ['constraint' => 'programme_place_event_id_fk'])
            ->addColumn('name', 'text', ['null' => false])
            ->addColumn('lat', 'decimal', ['precision' => 9, 'scale' => 6, 'null' => true])
            ->addColumn('lng', 'decimal', ['precision' => 9, 'scale' => 6, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addIndex(['event_id', 'name'], ['unique' => true, 'name' => 'programme_place_event_name_uidx'])
            ->create();

        $this->table('programme')
            ->addColumn('place_id', 'integer', ['null' => true])
            ->update();

        $this->execute(
            'INSERT INTO programme_place (event_id, name, created_at, updated_at)'
            . ' SELECT DISTINCT event_id, TRIM(place), CURRENT_TIMESTAMP, CURRENT_TIMESTAMP FROM programme'
            . " WHERE TRIM(place) <> ''",
        );
        $this->execute(
            'UPDATE programme SET place_id = (SELECT pp.id FROM programme_place pp'
            . ' WHERE pp.event_id = programme.event_id AND pp.name = TRIM(programme.place))'
            . " WHERE TRIM(place) <> ''",
        );

        $this->table('programme')
            ->addForeignKey('place_id', 'programme_place', ['id'], ['constraint' => 'programme_place_id_fk'])
            ->addIndex(['place_id'], ['name' => 'programme_place_idx'])
            ->removeColumn('place')
            ->update();
    }

    public function down(): void
    {
        $this->table('programme')
            ->addColumn('place', 'text', ['null' => true])
            ->update();
        $this->execute(
            'UPDATE programme SET place = COALESCE((SELECT pp.name FROM programme_place pp'
            . " WHERE pp.id = programme.place_id), '')",
        );
        $this->table('programme')
            ->changeColumn('place', 'text', ['null' => false])
            ->update();

        $this->table('programme')
            ->dropForeignKey('place_id')
            ->removeIndexByName('programme_place_idx')
            ->removeColumn('place_id')
            ->update();

        $this->table('programme_place')->drop()->save();
    }
}
