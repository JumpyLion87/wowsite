<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $charDb = config('database.connections.mysql_char.database');
        $worldDb = config('database.connections.mysql_world.database');

        // Drop if exists
        DB::connection('mysql_char')->unprepared('DROP PROCEDURE IF EXISTS `SendStoreItem`');

        // Create procedure
        DB::connection('mysql_char')->unprepared(<<<SQL
CREATE PROCEDURE `SendStoreItem`(
    IN p_character_guid INT UNSIGNED,
    IN p_item_id INT UNSIGNED
)
BEGIN
    DECLARE v_item_guid INT UNSIGNED;
    DECLARE v_mail_id INT UNSIGNED;

    IF NOT EXISTS (SELECT guid FROM `{$charDb}`.`characters` WHERE guid = p_character_guid) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid character GUID';
    END IF;

    IF NOT EXISTS (SELECT entry FROM `{$worldDb}`.`item_template` WHERE entry = p_item_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid item ID';
    END IF;

    START TRANSACTION;

    SELECT MAX(guid) + 1 INTO v_item_guid FROM `{$charDb}`.`item_instance`;
    IF v_item_guid IS NULL THEN
        SET v_item_guid = 1;
    END IF;

    INSERT INTO `{$charDb}`.`item_instance` (guid, itemEntry, owner_guid, count, enchantments)
    VALUES (v_item_guid, p_item_id, p_character_guid, 1, '0 0 0 0 0 0 0 0 0 0 0 0 0 0 0');

    INSERT INTO `{$charDb}`.`mail` (messageType, stationery, mailTemplateId, sender, receiver, subject, body, has_items, expire_time, deliver_time, money, cod, checked)
    VALUES (0, 41, 0, 0, p_character_guid, 'Store Purchase', 'Thank you for your purchase!', 1, UNIX_TIMESTAMP() + 30*24*3600, UNIX_TIMESTAMP(), 0, 0, 0);

    SET v_mail_id = LAST_INSERT_ID();

    INSERT INTO `{$charDb}`.`mail_items` (mail_id, item_guid, receiver)
    VALUES (v_mail_id, v_item_guid, p_character_guid);

    COMMIT;
END
SQL);
    }

    public function down(): void
    {
        DB::connection('mysql_char')->unprepared('DROP PROCEDURE IF EXISTS `SendStoreItem`');
    }
};
