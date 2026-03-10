<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSemanticUploadFields extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('participant');

        $table->renameColumn('uploaded_filename', 'uploaded_parental_consent_filename');
        $table->renameColumn('uploaded_original_filename', 'uploaded_parental_consent_original_filename');
        $table->renameColumn('uploaded_contenttype', 'uploaded_parental_consent_contenttype');

        $table->addColumn('uploaded_hospital_consent_filename', 'text', ['null' => true, 'default' => null]);
        $table->addColumn('uploaded_hospital_consent_original_filename', 'text', ['null' => true, 'default' => null]);
        $table->addColumn('uploaded_hospital_consent_contenttype', 'text', ['null' => true, 'default' => null]);

        $table->addColumn('uploaded_child_work_cert_filename', 'text', ['null' => true, 'default' => null]);
        $table->addColumn('uploaded_child_work_cert_original_filename', 'text', ['null' => true, 'default' => null]);
        $table->addColumn('uploaded_child_work_cert_contenttype', 'text', ['null' => true, 'default' => null]);

        $table->addColumn('uploaded_adult_event_cert_filename', 'text', ['null' => true, 'default' => null]);
        $table->addColumn('uploaded_adult_event_cert_original_filename', 'text', ['null' => true, 'default' => null]);
        $table->addColumn('uploaded_adult_event_cert_contenttype', 'text', ['null' => true, 'default' => null]);

        $table->save();
    }

    public function down(): void
    {
        $table = $this->table('participant');

        $table->removeColumn('uploaded_hospital_consent_filename');
        $table->removeColumn('uploaded_hospital_consent_original_filename');
        $table->removeColumn('uploaded_hospital_consent_contenttype');

        $table->removeColumn('uploaded_child_work_cert_filename');
        $table->removeColumn('uploaded_child_work_cert_original_filename');
        $table->removeColumn('uploaded_child_work_cert_contenttype');

        $table->removeColumn('uploaded_adult_event_cert_filename');
        $table->removeColumn('uploaded_adult_event_cert_original_filename');
        $table->removeColumn('uploaded_adult_event_cert_contenttype');

        $table->renameColumn('uploaded_parental_consent_filename', 'uploaded_filename');
        $table->renameColumn('uploaded_parental_consent_original_filename', 'uploaded_original_filename');
        $table->renameColumn('uploaded_parental_consent_contenttype', 'uploaded_contenttype');

        $table->save();
    }
}
