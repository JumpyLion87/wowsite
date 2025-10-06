<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WoW Server SOAP Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки для подключения к игровому серверу через SOAP API
    |
    */

    'soap_url' => env('WOW_SOAP_URL', 'http://localhost:7878'),
    'soap_username' => env('WOW_SOAP_USERNAME', 'admin'),
    'soap_password' => env('WOW_SOAP_PASSWORD', 'admin'),
    
    /*
    |--------------------------------------------------------------------------
    | Alternative Connection Methods
    |--------------------------------------------------------------------------
    |
    | Альтернативные способы связи с сервером
    |
    */
    
    'redis_enabled' => env('WOW_REDIS_ENABLED', false),
    'command_file_enabled' => env('WOW_COMMAND_FILE_ENABLED', false),
    'command_file_path' => env('WOW_COMMAND_FILE_PATH', '/tmp/wow_commands.txt'),
    'db_commands_enabled' => env('WOW_DB_COMMANDS_ENABLED', false),
    
    /*
    |--------------------------------------------------------------------------
    | Server Information
    |--------------------------------------------------------------------------
    |
    | Информация о сервере
    |
    */
    
    'server_name' => env('WOW_SERVER_NAME', 'AzerothCore'),
    'server_realm' => env('WOW_SERVER_REALM', 'AzerothCore'),
    'max_players' => env('WOW_MAX_PLAYERS', 100),
    
    /*
    |--------------------------------------------------------------------------
    | Game Commands (AzerothCore GM Commands)
    |--------------------------------------------------------------------------
    |
    | Доступные GM команды из AzerothCore
    | Источник: https://www.azerothcore.org/wiki/gm-commands
    |
    */
    
    'commands' => [
        // Player Management
        'kick' => '.kick {player} {reason}',
        'ban_account' => '.ban account {account} {duration} {reason}',
        'ban_character' => '.ban character {character} {duration} {reason}',
        'ban_ip' => '.ban ip {ip} {duration} {reason}',
        'unban_account' => '.unban account {account}',
        'unban_character' => '.unban character {character}',
        'unban_ip' => '.unban ip {ip}',
        
        // Teleportation
        'teleport' => '.teleport {player} {x} {y} {z} {map}',
        'appear' => '.appear {player}',
        'summon' => '.summon {player}',
        'unstuck' => '.unstuck {player} [inn/graveyard/startzone]',
        
        // Character Management
        'revive' => '.revive {player}',
        'heal' => '.heal {player}',
        'morph' => '.morph {player} {displayid}',
        'demorph' => '.demorph {player}',
        'freeze' => '.freeze {player}',
        'unfreeze' => '.unfreeze {player}',
        'mute' => '.mute {player} {duration} {reason}',
        'unmute' => '.unmute {player}',
        
        // Items and Currency
        'additem' => '.additem {player} {itemid} {count}',
        'addmoney' => '.addmoney {player} {amount}',
        'removemoney' => '.removemoney {player} {amount}',
        
        // Server Information
        'server_info' => '.server info',
        'online_players' => '.account onlinelist',
        'announce' => '.announce {message}',
        
        // Account Management
        'account_set_gmlevel' => '.account set gmlevel {account} {level} {realmid}',
        'account_set_password' => '.account set password {account} {password}',
        'account_create' => '.account create {account} {password} {email}',
        'account_delete' => '.account delete {account}',
        
        // Character Information
        'character_info' => '.character info {player}',
        'character_level' => '.character level {player} {level}',
        'character_rename' => '.character rename {player} {newname}',
    ],
];