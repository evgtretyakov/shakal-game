<?php


class GameController
{
    public static function findNewGame($user_id, $link) {
        if ($result = mysqli_query($link, "SELECT * FROM games WHERE `started` = 0 LIMIT 1")) {
            while($row = mysqli_fetch_array($result)) {
                $last_players = $row['max_players'] - $row['players'];
                $status = $last_players < 1 ? 'blocked' : $last_players == 1 ? 'start' : 'queue';
                $p_num = ($row['p1'] == $user_id) ? 1 : ((($row['p2'] == $user_id) ? 2 : ($row['p3'] == $user_id)) ? 3 : ($row['p4'] == $user_id) ? 4 : 0);
                return [
                    'status' =>  $status,
                    'game' => $row,
                    'p_num' => $p_num
                ];
            }
        }
        return [
            'status' =>  'blocked'
        ];
    }

    public static function getGameById($game_id, $link) {
        $sql = 'SELECT * FROM games WHERE `id` = ' . $game_id . ' LIMIT 1';
        if ($result = mysqli_query($link, $sql)) {
            while($row = mysqli_fetch_array($result)) {
                return $row;
            }
        } else {
            $error_msg = 'Не удалось найти игру. ' . mysqli_error($link);
            Handler::Respond([], 31, $error_msg);
        }
    }

    public static function formGameQueue($game, $user, $p_num, $link) {
        $players = [];
        $p_ids = [];
        for ($i = 1; $i < 5; $i++) {
            if ($game['p' . $i] != 0 && $i != $p_num) {
                array_push($p_ids, $game['p' . $i]);
            };
        }
        $players[] = $user['name'];
        foreach ($p_ids as $p_id) {
            array_push($players, UsersController::getUserById($p_id, $link)['name']);
        }
        $resp = [
            'game_started' => 0,
            'queue_players' => $players,
            'game_id' => $game['id'],
            'game_ts' => $game['ts']
        ];
        for ($i = 1; $i < 5; $i++) {
            $param = $i == $p_num ? 1 : 0;
            self::userUpdatedByPNum($game['id'], $i, $link, $param);
        }
        Handler::Respond($resp);
    }

    public static function connectUserToGame($game, $user, $game_status, $p_num, $link) {
        self::addGameToUser($user['id'], $game['id'], $link);
        if ($p_num == 0) { // get p_num
            $p_num = self::addUserToNewGame($user['id'], $game, $game_status, $link);
        }
        // add p_num to $game
        $game['p' . $p_num] = $user['id'];
        if ($game_status == 'start') {
            self::initializeGame($game, $p_num, $link);
        } else if ($game_status == 'queue') {
            self::formGameQueue($game, $user, $p_num, $link);
        } else {
            Handler::Respond([], 57, 'Непредвиденная ошибка, попробуйте еще раз. ' . mysqli_error($link));
        }
    }

    public static function userUpdatedByPNum($game_id, $player_num, $link, $updated = 1) {
        $sql = 'UPDATE games SET `p' . $player_num . '_updated`=' . $updated . ' WHERE `id` =' . $game_id;
        if (mysqli_query($link, $sql)) {
            return true;
        } else {
            Handler::Respond([], 26, 'Не записалось, что данные были забраны. ' . mysqli_error($link));
        }
        return false;
    }

    public static function addGameToUser($user_id, $game_id, $link) {
        $sql = 'UPDATE users SET `game`=' . $game_id . ' WHERE `id` =' . $user_id;
        if (mysqli_query($link, $sql)) {
            // connected
            return true;
        } else {
            Handler::Respond([], 16, 'Ошибка добавления в игру. ' . mysqli_error($link));
        }
    }

    public static function addUserToNewGame($user_id, $game, $game_status, $link) {
        $p_num = 0;
        $is_started = $game_status == 'start' ? 1 : 0;
        $players_count = $game['players'] + 1;

        // get p_num
        $empty_positions = [];
        for ($i = 1; $i < 5; $i++) {
            if ($game['p' . $i] == 0) {array_push($empty_positions, $i);};
        }
        $length = count($empty_positions);
        if ($length == 1) { // if last player
            $p_num = $empty_positions[0];
        } else if ($length == 0) {
            Handler::Respond([], 56, 'Игра уже началась, попробуйте начать новую игру.');
        } else {
            $new_num = mt_rand(1, $length) - 1;
            $p_num = $empty_positions[$new_num];
            if ($p_num == -1) {
                Handler::Respond([], 54, 'Не удалось добавиться в игру. Повторите попытку.');
            }
        }

        $sql = 'UPDATE games SET 
                 `p' . $p_num .'`=' . $user_id . ', 
                 `players`=' . $players_count . ', 
                 `turn`=' . $is_started . ', 
                 `started`=' . $is_started . '
                 WHERE `id` =' . $game["id"];
        if (mysqli_query($link, $sql)) {
            // connected
            $game['players'] = $players_count;
            $game['turn'] = $is_started;
            $game['started'] = $is_started;
            return $p_num;
        } else {
            if ($is_started == 1) {
                $error = 'Игроку не присвоен цвет - Последний игрок ' . mysqli_error($link);
            } else {
                $error = 'Игроку не присвоен цвет - не последний игрок. ' . mysqli_error($link);
            }
            Handler::Respond([], 51, $error);
        }
    }

    public static function initializeGame($game, $player_num, $link) {
        $players = PlayersController::createPlayers($game, $link);
        BoardController::createBoard($game, $players, $link);
        // send all info
        $game['turn'] = 1; // make 1 turn
        self::gameStarted($game, $player_num, $link, true);
    }

    public static function createNewGame($user, $link) {
        $ts = time();
        $max_players = 4;
        $new_num = mt_rand(1, $max_players);
        $game = [];
        $sql = 'INSERT INTO games (`players`, `max_players`, `p' . $new_num . '`, `ts`) VALUES ( 1, ' . $max_players . ', ' . $user["id"] . ', ' . $ts . ')';
        if (mysqli_query($link, $sql)) {
            // connected
            $game['id'] = mysqli_insert_id($link);
        } else {
            Handler::Respond([], 36, 'Ошибка при создании новой игры. ' . mysqli_error($link));
        }
        $game['ts'] = $ts;
        $game['p1'] = 0;
        $game['p2'] = 0;
        $game['p3'] = 0;
        $game['p4'] = 0;
        $game['players'] = 1;
        $game['max_players'] = $max_players;
        return [
            'game' => $game,
            'p_num' => $new_num
        ];
    }

    public static function stopTheGame($game, $players, $link, $error_msg = '') {
        $players_dissolved = true;
        $error_msg .= 'Не удалось создать таблицу с игроками. ';
        foreach ($players as $player_id) {
            $sql = 'UPDATE users SET `game`=0 WHERE `id` =' . $player_id;
            if (mysqli_query($link, $sql)) {
                // player set default
            } else {
                $players_dissolved = false;
                $error_msg .= 'Игрока ID=' . $player_id . ' не прогнали: ' . mysqli_error($link);
            }
        }
        $sql = "DROP TABLE IF EXISTS players_" . $game['ts'];
        if (mysqli_query($link, $sql)) {
            // players deleted
            $error_msg .= 'Таблица players_ удалена. ';
        }
        $sql = "DROP TABLE IF EXISTS board_" . $game['ts'];
        if (mysqli_query($link, $sql)) {
            // board deleted
            $error_msg .= 'Таблица board_ удалена. ';
        }
        $game_deleted = true;
        $sql = 'DELETE FROM `games` WHERE `id`=' . $game['id'];
        if (mysqli_query($link, $sql)) {
            // game deleted
        } else {
            $game_deleted = false;
            $error_msg .= 'Игру ID=' . $game['id'] . ' не уничтожили: ' . mysqli_error($link);
        }
        if (!$players_dissolved || !$game_deleted) {
            Handler::Respond([], 24, $error_msg);
        }
    }

    public static function getUserIdFromGame($game_id, $player_num, $link) {
        $sql = 'SELECT * FROM games WHERE `id` = ' . $game_id . ' LIMIT 1';
        if ($result = mysqli_query($link, $sql)) {
            while($row = mysqli_fetch_array($result)) {
                $column = 'p' . $player_num;
                return $row[$column];
            }
        } else {
            $error_msg = 'Игрок в этой игре не найден ' . mysqli_error($link);
            Handler::Respond([], 28, $error_msg);
        }
    }

    public static function gameStarted($game, $player_num, $link, $need_update = false, $show_tiles = false) {
        // get player_info and place figures
        $game_info = PlayersController::getPlayersInfo($game['ts'], false, false, $link, $show_tiles);

        $response = [
            'game_started' => 1,
            'player_num' => $player_num,
            'players_count' => $game['max_players'],
            'game_ts' => $game['ts'],
            'game_id' => $game['id'],
            'queue_players' => [],
            'turn' => $game['turn'],
            'players_info' => $game_info['players_info'],
            'tiles' => $game_info['tiles'],
            'update' => 1,
            'updated' => 0,
            'move_locked' => 0
        ];
        if ($need_update) { // need update
            for ($i = 1; $i < 5; $i++) {
                $param = $i == $player_num ? 1 : 0;
                self::userUpdatedByPNum($game['id'], $i, $link, $param);
            }
        } else { // no need update
            self::userUpdatedByPNum($game['id'], $player_num, $link, 1);
        }
        Handler::Respond($response);
    }

    public static function securityGameCheck($id, $session_id, $player_info_user_id, $original_tile, $figure, $turn, $link) {
        $check_move = [];
        if (UsersController::checkSession($id, $session_id, $link)) { // check if user is user
            if ($id == $player_info_user_id) { // this figure belongs to this player
                if ($figure->tile == $original_tile) { // check if this figure is on this tile
                    if ($figure->p_num == $turn) { // check if turn belongs to this player
                        return true;
                    } else { // turn belongs to another player
                        $check_move = [
                            'moved' => 0,
                            'block_reason' => 'Не ваш ход. Перезайдите.'
                        ];
                    }
                } else { // figure is not on this tile
                    $check_move = [
                        'moved' => 0,
                        'block_reason' => 'Фигура стоит не на этой клетке. Перезайдите.'
                    ];
                }
            } else { // this figure does not belong to this player
                $check_move = [
                    'moved' => 0,
                    'block_reason' => 'Фигура вам не принадлежит.'
                ];
            }
        }
        Handler::Respond($check_move);
    }
}