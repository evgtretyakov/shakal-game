<?php

//if (!$link) {
//    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
//    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
//    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
//    exit;
//}

//echo "Соединение с MySQL установлено!" . PHP_EOL;
//echo "Информация о сервере: " . mysqli_get_host_info($link) . PHP_EOL;

//mysqli_close($link);

require "UsersController.php";
require "GameController.php";
require "BoardController.php";
require "PlayersController.php";

class Handler
{

    public static function ConnectToDB() {
        $servername = "localhost";
        $username = "shakal_user";
        $password = "65NTmg1OPLW9akEP";
        $dbname = "shakal_bd";
        // Create connection
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        if (!$conn) {
            $error_msg = 'Ошибка подключения к базе:' . $conn->connect_error;
            self::Respond([], 11, $error_msg);
        }
        return $conn;
    }

    public static function Respond($resp = [], $error = 0, $error_msg = '') {
        $resp['error'] = $error;
        $resp['error_msg'] = $error_msg;
        $response = json_encode($resp);
        header('Content-Length: '.strlen($response));
        header('Content-Type: application/json');
        echo $response;
        exit;
    }

    function getKey($obj) {
        if (!$obj->name) {
            self::Respond([], 21, 'Имя не передано');
        } else {
            $name = $obj->name;
            $link = self::ConnectToDB();
            $user = UsersController::getUserByName($name, $link);
            $session_id = time() . $name;
            $session_id = md5($session_id);

            $found = false;
            $game_started = 0;
            $game_id = 0;
            $game_date = 0;
            $added = 0;

            if (!$user) { // register user
                $user['id'] = UsersController::registerUser($name, $session_id, $link);
                $added = 1;
            } else {
                $found = true;
                $game_id = $user['game'];
                UsersController::updateSessionId($session_id, $user['id'], $link);
            }
            if ($game_id != 0) {
                $game = GameController::getGameById($game_id, $link);
                if ($game['started'] == 1) {
                    $game_started = 1;
                    $game_date = date("d.m.y H:i:s", $game['ts']);
                }
            }
            $resp = [
                'session_id' => $session_id,
                'found'   => $found ? 1 : 0,
                'game_id' => $game_id,
                'game_started' => $game_started,
                'game_date' => $game_date,
                'added' => $added,
                'user_id' => $user['id']
            ];
            self::Respond($resp);
        }
    }

    public static function startNewGame($obj) {
        if (!$obj->name) {
            self::Respond([], 21, 'Имя не передано');
        } else {
            $name = $obj->name;
            $link = self::ConnectToDB();
            $user = UsersController::getUserByName($name, $link);
            if (!$user) {
                self::Respond([], 14, 'Мы вас не нашли :(');
            }

            $find_game = GameController::findNewGame($user['id'], $link);
            $game_status = $find_game['status'];

            if ($game_status == 'blocked') { // game not found or players = max_players
                $created_game = GameController::createNewGame($user, $link); // create game and add user
                $game         = $created_game['game'];
                $p_num        = $created_game['p_num'];
                $game_status = 'queue';
            } else {
                $game = $find_game['game'];
                $p_num = $find_game['p_num'];
            }
            GameController::connectUserToGame($game, $user, $game_status, $p_num, $link);
        }
    }

    public static function checkGameReady($obj) {
        $game_id = $obj->game_id;
        $user_id = $obj->user_id;
        $session_id = $obj->session_id;
        $link = self::ConnectToDB();
        $game = GameController::getGameById($game_id, $link);
        $response = [];
        $game_started = 0;
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
        if ($player_num == 0) {
            $error_msg = 'Игрок в игре не найден. ' . mysqli_error($link);
            self::Respond([], 32, $error_msg);
        } else { // player is found
            if ($game['p' . $player_num . '_updated'] == 1) { // updated
                $response['update'] = 0;
                self::Respond($response);
            } else { // not updated
                $update = 1;
                if ($game['started']) { // game started
                    if (UsersController::checkSession($user_id, $session_id, $link)) {
                        GameController::gameStarted($game, $player_num, $link, true);
                    }
                } else { // game not started
                    $players_list = [];
                    $this_player = UsersController::getUserById($game['p' . $player_num], $link);
                    for ($i = 1; $i < 5; $i++) {
                        if ($player_num != $i && $game['p' . $i] != 0) {
                            $new_user = UsersController::getUserById($game['p' . $i], $link);
                            $players_list[] = $new_user['name'];
                        }
                    }
                    shuffle($players_list);
                    array_unshift($players_list, $this_player['name']);

                    GameController::userUpdatedByPNum($game['id'], $player_num, $link);

                    $response = [
                        'game_started' => $game_started,
                        'player_connected' => 1,
                        'players_list' => $players_list,
                        'update' => $update
                    ];
                }
            }
        }
        self::Respond($response);
    }

    public static function continueTheGame($obj) {
        $session_id = $obj->session_id;
        $link = self::ConnectToDB();
        $user = UsersController::getUserByName($obj->name, $link);
        $game = GameController::getGameById($user['game'], $link);
        $response = [];
        if ($game['started'] == 1) {
            if (UsersController::checkSession($user['id'], $session_id, $link)) {
                $player_num = PlayersController::getPlayerNumByUserId($user['id'], $game);
                if ($player_num == 0) {
                    $error_msg = 'Игрок в игре не найден. ' . mysqli_error($link);
                    self::Respond([], 32, $error_msg);
                } else {
                    GameController::gameStarted($game, $player_num, $link, true);
                }
            }
        } else {
            $error_msg = 'Найденная игра не начата. ' . mysqli_error($link);
            self::Respond([], 37, $error_msg);
        }
        self::Respond($response);
    }

    public static function moveFigure($obj) {
        if (!(array)$obj->active_figure) {
            $error_msg = 'Не передан выбранный пират. ';
            self::Respond([], 48, $error_msg);
        }
        // cheats
        $infinity = false;
        if (isset($obj->infinity)) { // TODO remove cheats
            $infinity = $obj->infinity == 1;
        }
        // basic checks
        $session_id = $obj->session_id;
        $figure = $obj->active_figure; // TODO check if all data is safety
        if ($figure->type == 'ship') {
            $figure->alive = 1; // ship must be alive to go
        }
        $figure->move_locked = 0; // locked for some cases
        $link = self::ConnectToDB();
        $user = UsersController::getUserByName($obj->player_name, $link);
        $game = GameController::getGameById($user['game'], $link);
        $game_info = PlayersController::getPlayersInfo($game['ts'], false, false, $link);
        $players_info = $game_info['players_info'];
        $tiles = $game_info['tiles'];
        $old_tile = $tiles[$figure->tile];
        BoardController::checkFigureOnTile($figure, $tiles[$figure->tile]); // check if pirate is ok
        $p_num = $figure->p_num;
        $new_tile = $tiles[$obj->id]; // we doing it in case if there are other pirates
        if ($new_tile['closed'] == 1) { // now we change it in case if it closed (because we can't check the closed tile
            $new_tile = BoardController::getTileById($obj->id, $game, $link); // must work
            $new_tile['was_closed'] = 1; // fix problems
        } else {
            $new_tile['was_closed'] = 0; // fix problems
        }



//        $new_tile = $tiles[$obj->id];
//        $new_tile = BoardController::getTileById($obj->id, $game, $link);
//        $old_tile = BoardController::getTileById($figure->tile, $game, $link);
        $player_info = PlayersController::getPlayerById($p_num, $game['ts'], $link);
//        $player_info = $players_info[$p_num];
//        Handler::Respond([$player_info]);
        $check_move = BoardController::checkCanMove($new_tile, $old_tile, $figure, $game, $user, $player_info, $link);
        if ($check_move) { // check CanMove response ok
            if ($check_move['moved'] == 1) { // can move
                $figure = $check_move['figure']; // figure could be modified by checking
                $figures = [];
                if (isset($check_move['figures'])) {
                    $figures = $check_move['figures'];
                }
                if (GameController::securityGameCheck($user['id'], $session_id, $player_info['user_id'], $player_info[$figure->type . '_tile'], $figure, $game['turn'], $link)) {
                    $response = BoardController::updateFigurePosition($new_tile, $figure, $game['ts'], $game['turn'], $old_tile, $figures, $player_info, $players_info, $p_num, $link, $infinity);
                    if (BoardController::makeTurnAndUserUpdated($game['id'], $p_num, $response['turn'], $link)) { // set updated and not updated
                        self::Respond($response);
                    } else { // TODO откатить изменения в players_ и board_ если тут что-то пошло не так
                        self::Respond([], 45, 'Не обновили данные об обновлении по неизвестной причине.');
                    }
                }
            } else { // can't move for reason
                self::Respond($check_move);
            }
        } else { // can't move any reason
            self::Respond([
                'moved' => 0,
                'block_reason' => 'Причина неизвестна.'
            ]);
        }
    }

    public static function checkUpdate($obj) {
        $player_num = $obj->player_num;
        $game_id = $obj->game_id;
        $link = self::ConnectToDB();
        $game = GameController::getGameById($game_id, $link);

        $sql = 'SELECT `p' . $player_num . '_updated` FROM games WHERE `id`=' . $game_id . ' LIMIT 1';
        if ($result = mysqli_query($link, $sql)) { // check if enemy ship is not there
            while($row = mysqli_fetch_array($result)) {
                if ($row['p' . $player_num . '_updated'] == 0) {
                    GameController::gameStarted($game, $player_num, $link, false);
                } else {
                    $response = [
                        'updated' => 1
                    ];
                    self::Respond($response);
                }
            }
        } else {
            $error_msg = 'Не удалось узнать наличие обновлений ' . mysqli_error($link);
            self::Respond([], 46, $error_msg);
        }
        $error_msg = 'Не нашлось обновлений для обновлений ' . mysqli_error($link);
        self::Respond([], 47, $error_msg);
    }
}

$main = new Handler();

$json = file_get_contents('php://input');
if ($json) {
    $obj = json_decode($json);
    switch ($obj->req) {
        case 'getKey':
            $main->getKey($obj);
            break;
        case 'startNewGame':
            $main->startNewGame($obj);
            break;
        case 'checkGameReady':
            $main->checkGameReady($obj);
            break;
        case 'continueTheGame':
            $main->continueTheGame($obj);
            break;
        case 'moveFigure':
            $main->moveFigure($obj);
            break;
        case 'checkUpdate':
            $main->checkUpdate($obj);
            break;
        default:
            $main->Respond([], 33, 'Не найден такой запрос.');
    }
}