<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $authDb = config('database.connections.mysql_auth.database');
        $siteDb = config('database.connections.mysql.database');

        // Drop existing triggers if they exist
        DB::connection('mysql_auth')->unprepared("DROP TRIGGER IF EXISTS `after_account_insert`");
        DB::connection('mysql_auth')->unprepared("DROP TRIGGER IF EXISTS `after_account_update`");

        // Create AFTER INSERT trigger on acore_auth.account
        DB::connection('mysql_auth')->unprepared(<<<SQL
CREATE TRIGGER `after_account_insert` AFTER INSERT ON `account` FOR EACH ROW
BEGIN
    INSERT INTO `{$siteDb}`.`user_currencies` (account_id, username, email, points, tokens, avatar)
    VALUES (NEW.id, NEW.username, NEW.email, 0, 0, NULL)
    ON DUPLICATE KEY UPDATE username = VALUES(username), email = VALUES(email);
END
SQL);

        // Create AFTER UPDATE trigger on acore_auth.account
        DB::connection('mysql_auth')->unprepared(<<<SQL
CREATE TRIGGER `after_account_update` AFTER UPDATE ON `account` FOR EACH ROW
BEGIN
    UPDATE `{$siteDb}`.`user_currencies`
    SET username = NEW.username, email = NEW.email
    WHERE account_id = NEW.id;
END
SQL);
    }

    public function down(): void
    {
        // Remove triggers
        DB::connection('mysql_auth')->unprepared("DROP TRIGGER IF EXISTS `after_account_insert`");
        DB::connection('mysql_auth')->unprepared("DROP TRIGGER IF EXISTS `after_account_update`");
    }
};
