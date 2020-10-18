<?php


class PlayersController
{
    public static function getPlayerById($id, $ts, $link) {
        $sql = 'SELECT * FROM players_' . $ts . ' WHERE `id` = ' . $id . ' LIMIT 1';
        if ($result = mysqli_query($link, $sql)) {
            while($row = mysqli_fetch_array($result)) {
                return $row;
            }
        } else {
            $error_msg = 'Не удалось найти игрока №' . $id . ' в игре ' . mysqli_error($link);
            Handler::Respond([], 40, $error_msg);
        }
        $error_msg = 'Не удалось найти игрока №' . $id . ' в игре ' . mysqli_error($link);
        Handler::Respond([], 41, $error_msg);
    }

    public static function createPlayers($game, $link) {
        // create players
        $players = [];
        $players[] = $game['p1'];
        $players[] = $game['p2'];
        $players[] = $game['p3'];
        $players[] = $game['p4'];
        $sql = 'CREATE TABLE players_' . $game['ts'] . ' 
            (
                `id` INT NOT NULL PRIMARY KEY,
                `user_id` INT NOT NULL ,
                `name` VARCHAR(30) NOT NULL ,
                `score` INT NOT NULL ,
                `rum` INT NOT NULL ,
                `has_chest` TINYINT(1) NOT NULL ,
                `is_weed` TINYINT(1) NOT NULL ,
                `is_lighthouse` TINYINT(1) NOT NULL ,
                `is_ben` TINYINT(1) NOT NULL ,
                `is_missionary` TINYINT(1) NOT NULL ,
                `is_friday` TINYINT(1) NOT NULL ,
                `is_missionary_drunk` TINYINT(1) NOT NULL ,
                `ship_tile` INT NOT NULL,
                `p1_alive` TINYINT(1) NOT NULL ,
                `p2_alive` TINYINT(1) NOT NULL ,
                `p3_alive` TINYINT(1) NOT NULL ,
                `ben_alive` TINYINT(1) NOT NULL ,
                `missionary_alive` TINYINT(1) NOT NULL ,
                `friday_alive` TINYINT(1) NOT NULL ,
                `p1_disabled` TINYINT(1) NOT NULL ,
                `p2_disabled` TINYINT(1) NOT NULL ,
                `p3_disabled` TINYINT(1) NOT NULL ,
                `ben_disabled` TINYINT(1) NOT NULL ,
                `missionary_disabled` TINYINT(1) NOT NULL ,
                `friday_disabled` TINYINT(1) NOT NULL ,
                `p1_coins` INT NOT NULL ,
                `p2_coins` INT NOT NULL ,
                `p3_coins` INT NOT NULL ,
                `ben_coins` INT NOT NULL ,
                `missionary_coins` INT NOT NULL ,
                `friday_coins` INT NOT NULL ,
                `p1_chest` TINYINT(1) NOT NULL ,
                `p2_chest` TINYINT(1) NOT NULL ,
                `p3_chest` TINYINT(1) NOT NULL ,
                `ben_chest` TINYINT(1) NOT NULL ,
                `missionary_chest` TINYINT(1) NOT NULL ,
                `friday_chest` TINYINT(1) NOT NULL ,
                `p1_can_attack` TINYINT(1) NOT NULL ,
                `p2_can_attack` TINYINT(1) NOT NULL ,
                `p3_can_attack` TINYINT(1) NOT NULL ,
                `ben_can_attack` TINYINT(1) NOT NULL ,
                `missionary_can_attack` TINYINT(1) NOT NULL ,
                `friday_can_attack` TINYINT(1) NOT NULL ,
                `p1_water` TINYINT(1) NOT NULL ,
                `p2_water` TINYINT(1) NOT NULL ,
                `p3_water` TINYINT(1) NOT NULL ,
                `ben_water` TINYINT(1) NOT NULL ,
                `missionary_water` TINYINT(1) NOT NULL ,
                `friday_water` TINYINT(1) NOT NULL ,
                `p1_aboard` TINYINT(1) NOT NULL ,
                `p2_aboard` TINYINT(1) NOT NULL ,
                `p3_aboard` TINYINT(1) NOT NULL ,
                `ben_aboard` TINYINT(1) NOT NULL ,
                `missionary_aboard` TINYINT(1) NOT NULL ,
                `friday_aboard` TINYINT(1) NOT NULL ,
                `p1_helicopter` TINYINT(1) NOT NULL ,
                `p2_helicopter` TINYINT(1) NOT NULL ,
                `p3_helicopter` TINYINT(1) NOT NULL ,
                `ben_helicopter` TINYINT(1) NOT NULL ,
                `missionary_helicopter` TINYINT(1) NOT NULL ,
                `friday_helicopter` TINYINT(1) NOT NULL ,
                `p1_tile` INT NOT NULL ,
                `p2_tile` INT NOT NULL ,
                `p3_tile` INT NOT NULL ,
                `ben_tile` INT NOT NULL ,
                `missionary_tile` INT NOT NULL ,
                `friday_tile` INT NOT NULL ,
                `p1_level` INT NOT NULL ,
                `p2_level` INT NOT NULL ,
                `p3_level` INT NOT NULL ,
                `ben_level` INT NOT NULL ,
                `missionary_level` INT NOT NULL ,
                `friday_level` INT NOT NULL,
                `move_locked` VARCHAR(30) NOT NULL,
                `rum_disabled` VARCHAR(30) NOT NULL,
                `prev_tile_id` INT NOT NULL
            )';
        if (!mysqli_query($link, $sql)) {
            $error_msg = $sql . ' ' . mysqli_error($link) . ' ';
            GameController::stopTheGame($game, $players, $link, $error_msg);
            Handler::Respond([], 18, $error_msg);
        }
        // fill in players
        $insert_error = '';
        $p_num = 1;
        foreach ($players as $player_id) {
            $user = UsersController::getUserById($player_id, $link);
            $player_name = $user['name'];
            switch ($p_num) {
                case 2:
                    if (count($players) == 2) {
                        $ship_tile = 163;
                    } else {
                        $ship_tile = 91;
                    }
                    break;
                case 3:
                    $ship_tile = 163;
                    break;
                case 4:
                    $ship_tile = 79;
                    break;
                default:
                    $ship_tile = 7;
            }
            $sql = 'INSERT INTO `players_' . $game['ts'] . '` 
                (
                    `id`,
                    `user_id`,
                    `name`,
                    `ship_tile`,
                    `p1_alive`,
                    `p2_alive`,
                    `p3_alive`,
                    `p1_can_attack`,
                    `p2_can_attack`,
                    `p3_can_attack`,
                    `p1_aboard`,
                    `p2_aboard`,
                    `p3_aboard`,
                    `p1_tile`,
                    `p2_tile`,
                    `p3_tile`,
                    `p1_water`,
                    `p2_water`,
                    `p3_water`
                ) VALUES (
                    ' . $p_num . ',
                    ' . $player_id . ',
                    "' . $player_name . '",
                    ' . $ship_tile . ',
                    1,
                    1,
                    1,
                    1,
                    1,
                    1,
                    1,
                    1,
                    1,
                    ' . $ship_tile . ',
                    ' . $ship_tile . ',
                    ' . $ship_tile . ',
                    1,
                    1,
                    1
                )';
            if (mysqli_query($link, $sql)) {
                // player added
                $p_num++;
            } else {
                $insert_error .= 'Игрок ID=' . $player_id . ' не был назначен на игру. ' . mysqli_error($link) . ' ';
                GameController::stopTheGame($game, $players, $link, $insert_error);
                $insert_error .= " Игра уничтожена.";
                Handler::Respond([], 19, $insert_error);
            }
        }
        return $players;
    }

    public static function getPlayers($ts, $link) {
        $players = [];
        $sql = 'SELECT * FROM players_' . $ts;
        if ($result = mysqli_query($link, $sql)) {
            while($row = mysqli_fetch_array($result)) {
                $players[$row['id']] = [
                    'id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'name' => $row['name'],
                    'score' => $row['score'],
                    'rum' => $row['rum'],
                    'has_chest' => $row['has_chest'],
                    'is_weed' => $row['is_weed'],
                    'is_lighthouse' => $row['is_lighthouse'],
                    'is_ben' => $row['is_ben'],
                    'is_missionary' => $row['is_missionary'],
                    'is_friday' => $row['is_friday'],
                    'is_missionary_drunk' => $row['is_missionary_drunk'],
                    'ship_tile' => $row['ship_tile'],
                    'p1_alive' => $row['p1_alive'],
                    'p2_alive' => $row['p2_alive'],
                    'p3_alive' => $row['p3_alive'],
                    'ben_alive' => $row['ben_alive'],
                    'missionary_alive' => $row['missionary_alive'],
                    'friday_alive' => $row['friday_alive'],
                    'p1_disabled' => $row['p1_disabled'],
                    'p2_disabled' => $row['p2_disabled'],
                    'p3_disabled' => $row['p3_disabled'],
                    'ben_disabled' => $row['ben_disabled'],
                    'missionary_disabled' => $row['missionary_disabled'],
                    'friday_disabled' => $row['friday_disabled'],
                    'p1_coins' => $row['p1_coins'],
                    'p2_coins' => $row['p2_coins'],
                    'p3_coins' => $row['p3_coins'],
                    'ben_coins' => $row['ben_coins'],
                    'missionary_coins' => $row['missionary_coins'],
                    'friday_coins' => $row['friday_coins'],
                    'p1_chest' => $row['p1_chest'],
                    'p2_chest' => $row['p2_chest'],
                    'p3_chest' => $row['p3_chest'],
                    'ben_chest' => $row['ben_chest'],
                    'missionary_chest' => $row['missionary_chest'],
                    'friday_chest' => $row['friday_chest'],
                    'p1_can_attack' => $row['p1_can_attack'],
                    'p2_can_attack' => $row['p2_can_attack'],
                    'p3_can_attack' => $row['p3_can_attack'],
                    'ben_can_attack' => $row['ben_can_attack'],
                    'missionary_can_attack' => $row['missionary_can_attack'],
                    'friday_can_attack' => $row['friday_can_attack'],
                    'p1_water' => $row['p1_water'],
                    'p2_water' => $row['p2_water'],
                    'p3_water' => $row['p3_water'],
                    'ben_water' => $row['ben_water'],
                    'missionary_water' => $row['missionary_water'],
                    'friday_water' => $row['friday_water'],
                    'p1_aboard' => $row['p1_aboard'],
                    'p2_aboard' => $row['p2_aboard'],
                    'p3_aboard' => $row['p3_aboard'],
                    'ben_aboard' => $row['ben_aboard'],
                    'missionary_aboard' => $row['missionary_aboard'],
                    'friday_aboard' => $row['friday_aboard'],
                    'p1_helicopter' => $row['p1_helicopter'],
                    'p2_helicopter' => $row['p2_helicopter'],
                    'p3_helicopter' => $row['p3_helicopter'],
                    'ben_helicopter' => $row['ben_helicopter'],
                    'missionary_helicopter' => $row['missionary_helicopter'],
                    'friday_helicopter' => $row['friday_helicopter'],
                    'p1_tile' => $row['p1_tile'],
                    'p2_tile' => $row['p2_tile'],
                    'p3_tile' => $row['p3_tile'],
                    'ben_tile' => $row['ben_tile'],
                    'missionary_tile' => $row['missionary_tile'],
                    'friday_tile' => $row['friday_tile'],
                    'p1_level' => $row['p1_level'],
                    'p2_level' => $row['p2_level'],
                    'p3_level' => $row['p3_level'],
                    'ben_level' => $row['ben_level'],
                    'missionary_level' => $row['missionary_level'],
                    'friday_level' => $row['friday_level'],
                    'move_locked' => $row['move_locked'],
                    'rum_disabled' => $row['rum_disabled']
                ];
            }
        } else {
            Handler::Respond([], 34, 'Не удалось забрать данные игроков. ' . mysqli_error($link));
        }
        return $players;
    }

    public static function getPlayerNumByUserId($user_id, $game) {
        $player_num = 0;
        switch ($user_id) {
            case $game['p1']:
                $player_num = 1;
                break;
            case $game['p2']:
                $player_num = 2;
                break;
            case $game['p3']:
                $player_num = 3;
                break;
            case $game['p4']:
                $player_num = 4;
                break;
        }
        return $player_num;
    }

    public static function getPlayersInfo($ts, $players, $tiles, $link, $show_all_tiles = false) {
        if (!$players) {
            // get ships, pirates
            $players = self::getPlayers($ts, $link);
        }
        if (!$tiles) {
            // get tiles
            $tiles = BoardController::getTiles($ts, $link, $show_all_tiles);
        } else {
            $tiles = [];
        }
        $players_info = [];
        foreach ($players as $player) {
            $players_info[$player['id']] = [
                'p_num' => $player['id'],
                'name' => $player['name'],
                'score' => $player['score'],
                'rum' => $player['rum'],
                'has_chest' => $player['has_chest'],
                'is_weed' => $player['is_weed'],
                'is_lighthouse' => $player['is_lighthouse'],
                'p1_alive' => $player['p1_alive'],
                'p2_alive' => $player['p2_alive'],
                'p3_alive' => $player['p3_alive'],
                'is_ben' => $player['is_ben'],
                'is_missionary' => $player['is_missionary'],
                'is_friday' => $player['is_friday'],
                'is_missionary_drunk' => $player['is_missionary_drunk'],
                'move_locked' => $player['move_locked'],
                'ship_tile' => $player['ship_tile'],
                'rum_disabled' => $player['rum_disabled']
            ];

            $tiles = BoardController::getFiguresOnTiles($player, $tiles);
        }
        return [
            'players_info' => $players_info,
            'tiles' => $tiles
        ];
    }

    public static function useRum($ts, $p_num, $rum, $link) {
        $sql = 'UPDATE players_' . $ts . ' SET 
            `rum`=' . $rum . '
            WHERE `id`=' . $p_num;
        if (mysqli_query($link, $sql)) {
            return true;
        } else {
            Handler::Respond([], 60, 'Не удалось изменить количество рома. ' . mysqli_error($link));
        }
    }

    public static function rumDisablePirate($ts, $p_num, $pirate, $link) {
        $sql = 'UPDATE players_' . $ts . ' SET 
            `rum_disabled`="' . $pirate . '" 
            WHERE `id`=' . $p_num;
        if (mysqli_query($link, $sql)) {
            return true;
        } else {
            Handler::Respond([], 61, 'Не удалось изменить алкогольное состояние пирата. ' . mysqli_error($link));
        }
    }
}