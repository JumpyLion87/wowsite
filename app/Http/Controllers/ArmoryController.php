<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArmoryController extends Controller
{
    // Helper functions for faction, race, and class icons
    private function getFaction($race)
    {
        $alliance = [1, 3, 4, 7, 11, 22, 25, 29];
        return in_array($race, $alliance) ? __('alliance') : __('horde');
    }

    private function factionIconByName($faction)
    {
        return "/img/accountimg/faction/" . strtolower($faction) . ".png";
    }

    private function raceIcon($race, $gender)
    {
        $genderFolder = ($gender == 0) ? 'male' : 'female';
        $raceMap = [
            1 => 'human', 2 => 'orc', 3 => 'dwarf', 4 => 'nightelf',
            5 => 'undead', 6 => 'tauren', 7 => 'gnome', 8 => 'troll',
            9 => 'goblin', 10 => 'bloodelf', 11 => 'draenei',
            22 => 'worgen', 25 => 'pandaren_alliance', 26 => 'pandaren_horde',
            29 => 'voidelf'
        ];
        $raceName = isset($raceMap[$race]) ? $raceMap[$race] : 'unknown';
        return "/img/accountimg/race/{$genderFolder}/{$raceName}.png";
    }

    private function classIcon($class)
    {
        $classMap = [
            1 => 'warrior', 2 => 'paladin', 3 => 'hunter', 4 => 'rogue',
            5 => 'priest', 6 => 'deathknight', 7 => 'shaman', 8 => 'mage',
            9 => 'warlock', 10 => 'monk', 11 => 'druid', 12 => 'demonhunter'
        ];
        $className = isset($classMap[$class]) ? $classMap[$class] : 'unknown';
        return "/img/accountimg/class/{$className}.webp";
    }

    private function getTeamTypeName($type)
    {
        switch ($type) {
            case 2:
                return __('armory.type_2v2');
            case 3:
                return __('armory.type_3v3');
            case 5:
                return __('armory.type_5v5');
            default:
                return __('armory.type_unknown');
        }
    }

    // Arena team listings
    public function arena2v2()
    {
        $teams = DB::connection('mysql_char')->select("
            SELECT 
                at.arenaTeamId,
                at.name AS team_name,
                at.rating,
                at.seasonWins,
                (at.seasonGames - at.seasonWins) AS seasonLosses,
                CASE WHEN at.seasonGames > 0 
                    THEN ROUND((at.seasonWins / at.seasonGames) * 100, 1) 
                    ELSE 0 END AS winrate,
                c.race
            FROM arena_team at
            JOIN arena_team_member atm ON at.arenaTeamId = atm.arenaTeamId
            JOIN characters c ON atm.guid = c.guid
            WHERE at.type = 2
            AND atm.guid = at.captainGuid
            ORDER BY at.rating DESC
            LIMIT 50
        ");

        return view('armory.arena-2v2', [
            'teams' => $teams,
            'title' => __('armory.arena_2v2_title')
        ]);
    }

    public function arena3v3()
    {
        $teams = DB::connection('mysql_char')->select("
            SELECT 
                at.arenaTeamId,
                at.name AS team_name,
                at.rating,
                at.seasonWins,
                (at.seasonGames - at.seasonWins) AS seasonLosses,
                CASE WHEN at.seasonGames > 0 
                    THEN ROUND((at.seasonWins / at.seasonGames) * 100, 1) 
                    ELSE 0 END AS winrate,
                c.race
            FROM arena_team at
            JOIN arena_team_member atm ON at.arenaTeamId = atm.arenaTeamId
            JOIN characters c ON atm.guid = c.guid
            WHERE at.type = 3
            AND atm.guid = at.captainGuid
            ORDER BY at.rating DESC
            LIMIT 50
        ");

        return view('armory.arena-3v3', [
            'teams' => $teams,
            'title' => __('armory.arena_3v3_title')
        ]);
    }

    public function arena5v5()
    {
        $teams = DB::connection('mysql_char')->select("
            SELECT 
                at.arenaTeamId,
                at.name AS team_name,
                at.rating,
                at.seasonWins,
                (at.seasonGames - at.seasonWins) AS seasonLosses,
                CASE WHEN at.seasonGames > 0 
                    THEN ROUND((at.seasonWins / at.seasonGames) * 100, 1) 
                    ELSE 0 END AS winrate,
                c.race
            FROM arena_team at
            JOIN arena_team_member atm ON at.arenaTeamId = atm.arenaTeamId
            JOIN characters c ON atm.guid = c.guid
            WHERE at.type = 5
            AND atm.guid = at.captainGuid
            ORDER BY at.rating DESC
            LIMIT 50
        ");

        return view('armory.arena-5v5', [
            'teams' => $teams,
            'title' => __('armory.arena_5v5_title')
        ]);
    }

    // Arena team details
    public function arenateam(Request $request)
    {
        $arenaTeamId = $request->get('arenaTeamId', 0);

        $team = DB::connection('mysql_char')->selectOne("
            SELECT 
                at.arenaTeamId,
                at.name AS team_name,
                at.rating,
                at.seasonWins,
                at.seasonGames,
                (at.seasonGames - at.seasonWins) AS seasonLosses,
                CASE WHEN at.seasonGames > 0 
                    THEN ROUND((at.seasonWins / at.seasonGames) * 100, 1) 
                    ELSE 0 END AS winrate,
                at.weekWins,
                at.weekGames,
                (at.weekGames - at.weekWins) AS weekLosses,
                at.type,
                at.captainGuid
            FROM arena_team at
            WHERE at.arenaTeamId = ?
        ", [$arenaTeamId]);

        if (!$team) {
            return view('armory.arenateam', [
                'team' => null,
                'members' => [],
                'title' => __('armory.team_not_found')
            ]);
        }

        $membersData = DB::connection('mysql_char')->select("
            SELECT 
                c.guid,
                c.name,
                c.race,
                c.class,
                c.gender,
                atm.personalRating AS personal_rating
            FROM arena_team_member atm
            JOIN characters c ON atm.guid = c.guid
            WHERE atm.arenaTeamId = ?
            ORDER BY c.name ASC
        ", [$arenaTeamId]);

        $members = [];
        $captain = null;
        foreach ($membersData as $row) {
            if ($row->guid == $team->captainGuid) {
                $captain = $row;
            } else {
                $members[] = $row;
            }
        }

        // Place captain at the top
        $orderedMembers = [];
        if ($captain) {
            $orderedMembers[] = $captain;
        }
        $orderedMembers = array_merge($orderedMembers, $members);

        return view('armory.arenateam', [
            'team' => $team,
            'members' => $orderedMembers,
            'title' => $team->team_name . ' - ' . $this->getTeamTypeName($team->type)
        ]);
    }

    // Solo PvP rankings
    public function soloPvp()
    {
        $players = DB::connection('mysql_char')->select("
            SELECT c.guid, c.name, c.race, c.class, c.level, c.gender, c.totalKills, g.name AS guild_name
            FROM characters c
            LEFT JOIN guild_member gm ON c.guid = gm.guid
            LEFT JOIN guild g ON gm.guildid = g.guildid
            ORDER BY c.level DESC, c.totalKills DESC
            LIMIT 50
        ");

        // Add icon URLs to each player
        foreach ($players as $player) {
            $faction = $this->getFaction($player->race);
            $player->faction_icon = $this->factionIconByName($faction);
            $player->race_icon = $this->raceIcon($player->race, $player->gender);
            $player->class_icon = $this->classIcon($player->class);
        }

        return view('armory.solo-pvp', [
            'players' => $players,
            'title' => __('armory.solo_pvp_title')
        ]);
    }

    // Main armory index
    public function index()
    {
        // Get real statistics from database
        $totalPlayers = DB::connection('mysql_char')->table('characters')->count();
        $totalTeams = DB::connection('mysql_char')->table('arena_team')->count();
        
        // Get total PvP matches - arena games from arena_team table + battlegrounds
        $arenaMatches = DB::connection('mysql_char')->table('arena_team')->sum('seasonGames') ?? 0;
        $battlegroundMatches = DB::connection('mysql_char')->table('pvpstats_battlegrounds')->count() ?? 0;
        $totalMatches = $arenaMatches + $battlegroundMatches;

        return view('armory.index', [
            'title' => __('armory.title'),
            'totalPlayers' => $totalPlayers,
            'totalTeams' => $totalTeams,
            'totalMatches' => $totalMatches
        ]);
    }
}