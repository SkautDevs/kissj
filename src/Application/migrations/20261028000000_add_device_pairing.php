<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddDevicePairing extends AbstractMigration
{
    public function up(): void
    {
        $this->table('device_session')
            ->addColumn('participant_id', 'integer', ['null' => false])
            ->addForeignKey(
                'participant_id',
                'participant',
                ['id'],
                ['constraint' => 'device_session_participant_id_fk', 'delete' => 'CASCADE'],
            )
            ->addColumn('token_hash', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('expires_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addColumn('created_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addIndex(['token_hash'], ['unique' => true, 'name' => 'device_session_token_hash_idx'])
            ->addIndex(['participant_id'], ['name' => 'device_session_participant_id_idx'])
            ->create();

        $this->table('device_pairing')
            ->addColumn('event_id', 'integer', ['null' => false])
            ->addForeignKey('event_id', 'event', ['id'], ['constraint' => 'device_pairing_event_id_fk'])
            ->addColumn('device_id', 'string', [
                'limit' => 64,
                'null' => false,
                'comment' => 'SHA-256 hex of the eventApp session id, one pending pairing per device and event',
            ])
            ->addColumn('device_code', 'string', [
                'limit' => 32,
                'null' => false,
                'comment' => 'secret the eventApp polls with, never shown to the participant',
            ])
            ->addColumn('user_code', 'string', [
                'limit' => 6,
                'null' => false,
                'comment' => 'six digits the participant types on the kissj pairing page, shown as 483-291',
            ])
            ->addColumn('status', 'string', ['null' => false])
            ->addColumn('participant_id', 'integer', ['null' => true])
            ->addForeignKey(
                'participant_id',
                'participant',
                ['id'],
                ['constraint' => 'device_pairing_participant_id_fk', 'delete' => 'CASCADE'],
            )
            ->addColumn('device_session_id', 'integer', ['null' => true])
            ->addForeignKey(
                'device_session_id',
                'device_session',
                ['id'],
                ['constraint' => 'device_pairing_device_session_id_fk', 'delete' => 'SET_NULL'],
            )
            ->addColumn('expires_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addColumn('last_polled_at', 'timestamp', ['null' => true, 'timezone' => true])
            ->addColumn('created_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addIndex(['device_code'], ['unique' => true, 'name' => 'device_pairing_device_code_idx'])
            ->addIndex(['event_id', 'user_code'], ['name' => 'device_pairing_event_id_user_code_idx'])
            ->addIndex(['event_id', 'status'], ['name' => 'device_pairing_event_id_status_idx'])
            ->addIndex(['event_id', 'device_id'], ['name' => 'device_pairing_event_id_device_id_idx'])
            ->addIndex(['participant_id'], ['name' => 'device_pairing_participant_id_idx'])
            ->create();

        $this->table('device_pairing_attempt')
            ->addColumn('participant_id', 'integer', ['null' => false])
            ->addForeignKey(
                'participant_id',
                'participant',
                ['id'],
                ['constraint' => 'device_pairing_attempt_participant_id_fk', 'delete' => 'CASCADE'],
            )
            ->addColumn('created_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => false, 'timezone' => true])
            ->addIndex(['participant_id'], ['name' => 'device_pairing_attempt_participant_id_idx'])
            ->create();
    }

    public function down(): void
    {
        $this->table('device_pairing_attempt')->drop()->save();
        $this->table('device_pairing')->drop()->save();
        $this->table('device_session')->drop()->save();
    }
}
