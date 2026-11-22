<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddProgrammeImages extends AbstractMigration
{
    public function up(): void
    {
        $this->table('programme_image')
            ->addColumn('programme_id', 'integer', ['null' => false])
            ->addForeignKey('programme_id', 'programme', ['id'], ['constraint' => 'programme_image_programme_id_fk'])
            ->addColumn('filename', 'text', ['null' => false])
            ->addColumn('thumb_filename', 'text', ['null' => true])
            ->addColumn('alt', 'text', ['null' => false, 'default' => ''])
            ->addColumn('sort_order', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('created_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addIndex(['programme_id', 'sort_order'], ['name' => 'programme_image_programme_idx'])
            ->addIndex(['filename'], ['name' => 'programme_image_filename_idx'])
            ->create();

        $this->execute(
            'INSERT INTO programme_image'
            . ' (programme_id, filename, thumb_filename, alt, sort_order, created_at, updated_at)'
            . " SELECT id, image_filename, NULL, '', 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP FROM programme"
            . ' WHERE image_filename IS NOT NULL AND LENGTH(image_filename) = 32'
        );

        $this->table('programme')
            ->removeColumn('image_filename')
            ->update();
    }

    public function down(): void
    {
        $this->table('programme')
            ->addColumn('image_filename', 'text', ['null' => true])
            ->update();
        $this->execute(
            'UPDATE programme SET image_filename = (SELECT pi.filename FROM programme_image pi'
            . ' WHERE pi.programme_id = programme.id ORDER BY pi.sort_order, pi.id LIMIT 1)'
            . ' WHERE EXISTS (SELECT 1 FROM programme_image pi WHERE pi.programme_id = programme.id)'
        );

        $this->table('programme_image')->drop()->save();
    }
}
