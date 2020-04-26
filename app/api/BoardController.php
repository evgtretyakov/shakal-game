<?php


class BoardController
{
    const pirate_types = [
        'p1',
        'p2',
        'p3',
        'ben',
        'friday',
        'missionary'
    ];

    public static function createBoard($game, $players, $link) {
        $sql = 'CREATE TABLE `board_' . $game['ts'] . '`
            (
                `id` INT NOT NULL PRIMARY KEY,
                `closed` TINYINT(1) NOT NULL DEFAULT "0",
                `type` VARCHAR(30) NOT NULL DEFAULT "0" ,
                `direction` TINYINT(1) NOT NULL DEFAULT "0",
                `level0_coins` INT NOT NULL DEFAULT "0" ,
                `level1_coins` INT NOT NULL DEFAULT "0" ,
                `level2_coins` INT NOT NULL DEFAULT "0" ,
                `level3_coins` INT NOT NULL DEFAULT "0" ,
                `level4_coins` INT NOT NULL DEFAULT "0" ,
                `level0_chest` TINYINT(1) NOT NULL DEFAULT "0" ,
                `level1_chest` TINYINT(1) NOT NULL DEFAULT "0" ,
                `level2_chest` TINYINT(1) NOT NULL DEFAULT "0" ,
                `level3_chest` TINYINT(1) NOT NULL DEFAULT "0" ,
                `level4_chest` TINYINT(1) NOT NULL DEFAULT "0"
            )';
        if (!mysqli_query($link, $sql)) {
            $error_msg = 'Не удалось создать таблицу поля ' . mysqli_error($link) . ' ';
            GameController::stopTheGame($game, $players, $link, $error_msg);
            Handler::Respond([], 25, $error_msg);
        }
        require 'tile_types.php';
        // generate board
        shuffle($types);
//        shuffle($types); // double shuffle
        $box_tiles = [];
        $n = 0;
        for ($j=0;$j < 169; $j++) {
            // defaults
            $direction = 0;
            $i = $j+1;
            if (
                $i < 16 ||
                $i > 24 && $i < 28 ||
                $i > 38 && $i < 41 ||
                $i > 51 && $i < 54 ||
                $i > 64 && $i < 67 ||
                $i > 77 && $i < 80 ||
                $i > 90 && $i < 93 ||
                $i > 103 && $i < 106 ||
                $i > 116 && $i < 119 ||
                $i > 129 && $i < 132 ||
                $i > 142 && $i < 146 ||
                $i > 154
            ) {
                $type = 'water';
            } else {
//                $type_num = mt_rand(1, count($types)) - 1;
//                $type = $tile_types[$type_num];
                $type = $types[$n];
                $n++;
            }
            // rotate directions
            if ( $type == 'arrow1' || $type == 'arrow1d' || $type == 'arrow3' ) { // TODO add canon etc
                $direction = mt_rand(1, 4); // arrow1 - 1 - top; arrow1d 1 - top-right; arrow3 1 - top-right (and clockwise)
            } else if ( $type == 'arrow2' || $type == 'arrow2d') {
                $direction = mt_rand(1, 2); // arrow2 - 1 - vertically, 2 - horizontally; arrow2d - 1 - top-right; 2 - top-left
            }
            // final tile
            $box_tiles[] = [
                'id' => $i,
                'type' => $type,
                'closed' => $type == 'water' ? 0 : 1,
                'direction' => $direction
            ];
        }
        for ($i=0;$i < 169; $i++) {
            $sql = 'INSERT INTO `board_' . $game['ts'] . '`
                (
                    `id`,
                    `closed`,
                    `type`,
                    `direction`
                ) VALUES (
                    ' . $box_tiles[$i]["id"] . ',
                    ' . $box_tiles[$i]["closed"] . ',
                    "' . $box_tiles[$i]["type"] . '",
                    ' . $box_tiles[$i]["direction"] . '
                )';
            if (mysqli_query($link, $sql)) {
                // tile added
            } else {
                $insert_error = 'Клетка не была добавлена type=' . $i . ' ' . mysqli_error($link) . ' ';
                GameController::stopTheGame($game, $players, $link, $insert_error);
                $insert_error .= " Игра уничтожена.";
                Handler::Respond([], 19, $insert_error);
            }
        }
    }

    public static function getTileById($id, $game, $link) {
        $sql = 'SELECT * FROM board_' . $game['ts'] . ' WHERE `id`=' . $id . ' LIMIT 1';
        if ($result = mysqli_query($link, $sql)) {
            while($row = mysqli_fetch_array($result)) {
                $tile = [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'was_closed' => $row['closed'],
                    'closed' => 0,
                    'direction' => $row['direction'],
                    'level0_coins' => $row['level0_coins'],
                    'level1_coins' => $row['level1_coins'],
                    'level2_coins' => $row['level2_coins'],
                    'level3_coins' => $row['level3_coins'],
                    'level4_coins' => $row['level4_coins'],
                    'level0_chest' => $row['level0_chest'],
                    'level1_chest' => $row['level1_chest'],
                    'level2_chest' => $row['level2_chest'],
                    'level3_chest' => $row['level3_chest'],
                    'level4_chest' => $row['level4_chest']
                ];
                return $tile;
            }
        } else {
            $error_msg = 'Не удалось найти клетку на поле. ' . mysqli_error($link);
            Handler::Respond([], 38, $error_msg);
        }
        return null;
    }

    public static function getTiles($ts, $link) {
        $tiles = [];
        $sql = 'SELECT * FROM board_' . $ts;
        if ($result = mysqli_query($link, $sql)) {
            while($row = mysqli_fetch_array($result)) {
                if ($row['closed'] == 0) {
                    $tiles[$row['id']] = [
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'closed' => 0,
                        'figures' => [],
                        'direction' => $row['direction'],
                        'level0_coins' => $row['level0_coins'],
                        'level1_coins' => $row['level1_coins'],
                        'level2_coins' => $row['level2_coins'],
                        'level3_coins' => $row['level3_coins'],
                        'level4_coins' => $row['level4_coins'],
                        'level0_chest' => $row['level0_chest'],
                        'level1_chest' => $row['level1_chest'],
                        'level2_chest' => $row['level2_chest'],
                        'level3_chest' => $row['level3_chest'],
                        'level4_chest' => $row['level4_chest']
                    ];
                } else {
                    $tiles[$row['id']] = [
                        'id' => $row['id'],
//                        'type' => 'closed',
//                        'closed' => 1
                        'type' => $row['type'],
                        'closed' => 0,
                        'figures' => [],
                        'direction' => $row['direction'],
                    ];
                }
            }
        } else {
            Handler::Respond([], 35, 'Не удалось забрать данные игрового поля. ' . mysqli_error($link));
        }
        return $tiles;
    }

    public static function addFigureToTile($player, $name) {
        return [
            'p_num' => $player['id'],
            'type' => $name,
            'tile' => $player[$name . '_tile'],
            'aboard' => $player[$name . '_aboard'],
            'can_attack' => $player[$name . '_can_attack'],
            'chest' => $player[$name . '_chest'],
            'coins' => $player[$name . '_coins'],
            'disabled' => $player[$name . '_disabled'],
            'helicopter' => $player[$name . '_helicopter'],
            'level' => $player[$name . '_level'],
            'water' => $player[$name . '_water'],
            'alive' => $player[$name . '_alive'],
            'drunk' => $name == 'missionary' ? $player['is_missionary_drunk'] : 0
        ];
    }

    public static function makeTurnAndUserUpdated($game_id, $p_num, $turn, $link) {
        $turn = intval($turn);
//        $turn = 4; // TODO turn_block
        $sql = 'UPDATE games SET
                 `p1_updated`=' . ($p_num == 1 ? 1 : 0) . ',
                 `p2_updated`=' . ($p_num == 2 ? 1 : 0) . ',
                 `p3_updated`=' . ($p_num == 3 ? 1 : 0) . ',
                 `p4_updated`=' . ($p_num == 4 ? 1 : 0) . ',
                 `turn`=' . $turn . '
                  WHERE `id`=' . $game_id; // set figure to tile
        if (mysqli_query($link, $sql)) {
            return true;
        } else {
            Handler::Respond([], 44, 'Не обновили данные об обновлении. ' . mysqli_error($link));
        }
        return false;
    }

    public static function respawnPirate($ts, $p_num, $pirate, $tile_id, $link) {
        $sql = 'UPDATE players_' . $ts . ' SET
        `' . $pirate . '_alive`=1,
        `' . $pirate . '_can_attack`=1,
        `' . $pirate . '_aboard`=0,
        `' . $pirate . '_chest`=0,
        `' . $pirate . '_coins`=0,
        `' . $pirate . '_disabled`=0,
        `' . $pirate . '_helicopter`=0,
        `' . $pirate . '_level`=0,
        `' . $pirate . '_tile`=' . $tile_id . ',
        `' . $pirate . '_water`=0
         WHERE id=' . $p_num;
        if (mysqli_query($link, $sql)) {
            return true;
        } else {
            Handler::Respond([], 58, 'Не удалось воскресить пирата. ' . mysqli_error($link));
        }
    }

    public static function killThePirate($ts, $p_num, $pirate, $throw_out, $link, $ship_tile = 0) {
        $sql = 'UPDATE players_' . $ts . ' SET
        `' . $pirate . '_alive`=' . ($throw_out ? 0 : 1) . ',
        `' . $pirate . '_can_attack`=1,
        `' . $pirate . '_aboard`=' . ($throw_out ? 0 : 1) . ',
        `' . $pirate . '_chest`=0,
        `' . $pirate . '_coins`=0,
        `' . $pirate . '_disabled`=0,
        `' . $pirate . '_helicopter`=0,
        `' . $pirate . '_level`=0,
        `' . $pirate . '_tile`=' . ($throw_out ? 0 : $ship_tile) . ',
        `' . $pirate . '_water`=' . ($throw_out ? 0 : 1) . '
         WHERE id=' . $p_num;
        if (mysqli_query($link, $sql)) {
            return $ship_tile;
        } else {
            Handler::Respond([], 59, 'Не удалось прикончить пирата. ' . mysqli_error($link));
        }
    }

    public static function checkNewTile($ts, $figure, $tile, $old_tile, $player_info, $players_info, $link) {
//        $player_info = $players_info[$figure->p_num];
        $kill_pirates = [];
        $move_locked = 0;
        $new_figure = [];
        $prev_tile_id = $old_tile['id'];
        // TODO check tile type and stuff
        if ($figure->type == 'ship') { // if our figure is ship
            if (isset($tile['figures'])) { // check if there any pirates - foreach
                foreach ($tile['figures'] as &$tile_figure) {
                    if ($tile_figure['p_num'] == $player_info['id']) { // if our pirate
                        $tile_figure['aboard'] = 1; // make aboard
                        self::updateFigureInfo($tile_figure, $ts, $tile['id'], $prev_tile_id, $move_locked, $link, false, false, false);
                    } else { // else if enemy
                        if (
                            $tile_figure['type'] == 'p1' ||
                            $tile_figure['type'] == 'p2' ||
                            $tile_figure['type'] == 'p3' ||
                            $tile_figure['type'] == 'ben' ||
                            $tile_figure['type'] == 'missionary' && $tile_figure['drunk'] == 1
                        ) { // if p1 p2 p3 ben drunk_missionary
//                            self::updateFigureInfo($tile_figure, $ts, $tile['id'], $prev_tile_id, $move_locked, $link, false, true, false); // kill
                            $kill_pirates[] = self::killThePirate($ts, $tile_figure['p_num'], $tile_figure['type'], false, $link, $players_info[$tile_figure['p_num']]['ship_tile']); // kill
                        } else if ($tile_figure['type'] == 'missionary' && $tile_figure['drunk'] == 0) { // else if missionary_not_drunk
                            $tile_figure['aboard'] = 1;
                            self::updateFigureInfo($tile_figure, $ts, $tile['id'], $prev_tile_id, $move_locked, $link, false, false, false); // make him aboard
                        } else if ($tile_figure['type'] == 'friday') { // else if friday
                            $tile_figure['aboard'] = 1; // make him aboard
                            self::updateFigureInfo($tile_figure, $ts, $tile['id'], $prev_tile_id, $move_locked, $link, true, false, false); // make him ours
                            self::updateFigureInfo($tile_figure, $ts, $tile['id'], $prev_tile_id, $move_locked, $link, false, true, false); // make him not enemies
                        }
                    }

                }
            }
        } else { // pirate moves
            if (
                $tile['type'] == 'arrow1' ||
                $tile['type'] == 'arrow1' ||
                $tile['type'] == 'arrow1d' ||
                $tile['type'] == 'arrow2' ||
                $tile['type'] == 'arrow2d' ||
                $tile['type'] == 'arrow3' ||
                $tile['type'] == 'arrow4' ||
                $tile['type'] == 'arrow4d'
            ) {
                $move_locked = $figure->type; // TODO check cycling
            } else if ($tile['type'] == 'fort' || $tile['type'] == 'fort_sex') { // TODO check cycling
                if (isset($tile['figures'])) { // TODO check if there enemy figure (not friday or drunk missionary)
                    foreach ($tile['figures'] as $tile_figure) {
                        if ($tile_figure['p_num'] != $figure->p_num) {
                            $response = [
                                'moved' => 0,
                                'block_reason' => 'В занятый форт нельзя.'
                            ];
                            $response['figure'] = $figure;
                            Handler::Respond($response);
                        }
                    }
                } // TODO else if holding chest or gold - drop
                if ($tile['type'] == 'fort_sex') {
                    if ($player_info['p1_alive'] == 0) {
                        $new_figure = self::respawnPirate($ts, $player_info['id'], 'p1', $tile['id'], $link);
                    } else if ($player_info['p2_alive'] == 0) {
                        $new_figure = self::respawnPirate($ts, $player_info['id'], 'p2', $tile['id'], $link);
                    } else if ($player_info['p3_alive'] == 0) {
                        $new_figure = self::respawnPirate($ts, $player_info['id'], 'p3', $tile['id'], $link);
                    }
                }
            } else if ($tile['type'] == 'cannibal') {
                $figure->alive = 0; // kill pirate
            } else if($tile['was_closed'] == 1) { // tiles, that active when open
                if (
                    $tile['type'] == 'rum1' ||
                    $tile['type'] == 'rum2' ||
                    $tile['type'] == 'rum3' ||
                    $tile['type'] == 'rum_barrel'
                ) {
                    $rum = 0;
                    if ($tile['type'] == 'rum1') { $rum = 1; } else if ($tile['type'] == 'rum2') { $rum = 2; } else if ($tile['type'] == 'rum3') { $rum = 3; };
                    if ($figure->type == 'missionary' && $figure->drunk == 0) {
                        $figure->drunk = 1; // make missionary drunk
                        PlayersController::rumDisablePirate($ts, $figure->p_num, $figure->type, $link); // rum disable pirate
                        $rum--;
                    } else if ($figure->type == 'friday') {
                        $rum--;
//                    self::updateFigureInfo($figure, $ts, $tile['id'], $prev_tile_id, $move_locked, $link, false, true, true); // make friday dead
                        $figure->tile = 0;
                        $figure->aboard = 0;
                        $figure->can_attack = 0;
                        $figure->chest = 0;
                        $figure->coins = 0;
                        $figure->disabled = 0;
                        $figure->helicopter = 0;
                        $figure->level = 0;
                        $figure->water = 0;
                        $figure->alive = 0; // TODO check how it works
                    } else {
                        if ($rum < 1) {
                            PlayersController::rumDisablePirate($ts, $figure->p_num, $figure->type, $link); // rum disable pirate
                        }
                    }
                    if ($rum > 0) {
                        PlayersController::useRum($ts, $figure->p_num, (intval($player_info['rum']) + $rum), $link); // add rum
                    }
                }
            } else if ($tile['type'] == 'rum_barrel') {
                if ($figure->type == 'friday') {
//                    self::updateFigureInfo($figure, $ts, $tile['id'], $prev_tile_id, $move_locked, $link, false, true, true); // make friday dead
                    $figure->tile = 0;
                    $figure->aboard = 0;
                    $figure->can_attack = 0;
                    $figure->chest = 0;
                    $figure->coins = 0;
                    $figure->disabled = 0;
                    $figure->helicopter = 0;
                    $figure->level = 0;
                    $figure->water = 0;
                    $figure->alive = 0; // TODO check how it works
                } else {
                    PlayersController::rumDisablePirate($ts, $figure->p_num, $figure->type, $link); // rum disable pirate
                }
            } else if ($tile['type'] == 'crocodile' && $tile['closed'] == 0) {
                $figure->alive = 0; // kill pirate
            } else { // ordinary tile

            }
            if (isset($tile['figures'])) {
                if (
                    $figure->type == 'p1' ||
                    $figure->type == 'p2' ||
                    $figure->type == 'p3' ||
                    $figure->type == 'ben' ||
                    $figure->type == 'missionary' && $figure->drunk == 1
                ) { // TODO check if there enemy figure (not friday or not drunk missionary)
                    $can_attack = false;
                    $attack_figure = [];
                    foreach ($tile['figures'] as $tile_figure) {
                        if ($tile_figure['p_num'] == $player_info['id'] || $tile['type'] == 'water') { // if our pirate
                            if (self::checkIfThereShip($ts, $tile['id'], $link) && $player_info['ship_tile'] != $tile['id']) { // there is enemy ship
                                $figure->aboard = 1;
                                $figure->water = 1;
                                $figure->tile = $player_info['ship_tile'];
                            }
                            // do nothing
                        } else if ($tile_figure['type'] == 'missionary' && $tile_figure['drunk'] == 0) { // check if missionary not drunk
                            $can_attack = false;
                            $attack_figure = [];
                            break;
                        } else if (
                            $tile_figure['type'] == 'p1' ||
                            $tile_figure['type'] == 'p2' ||
                            $tile_figure['type'] == 'p3' ||
                            $tile_figure['type'] == 'ben' ||
                            $tile_figure['type'] == 'missionary' && $tile_figure['drunk'] == 1
                        ) {
                            if (!$can_attack) {
                                $can_attack = true;
                                $attack_figure = $tile_figure;
                            }
                        } // TODO else if there friday without guard - then conquer
                    }

                    if ($can_attack) {  // if can attack
                        $kill_pirates[] = self::killThePirate($ts, $attack_figure['p_num'], $attack_figure['type'], false, $link, $players_info[$attack_figure['p_num']]['ship_tile']); // kill figure attack_figure
                    }
                } // TODO else if our pirate is friday - then we will give it to another player
            }
        }
        return [
            'move_locked' => $move_locked,
            'new_figure' => $new_figure,
            'figure' => $figure,
            'new_tile' => $tile,
            'kill_pirates' => $kill_pirates,
            'prev_tile' => $old_tile
            // TODO return $players_info_changed, changed $figure etc
        ];
    }

    public static function updateFigurePosition($tile, $figure, $ts, $turn, $old_tile, $figures, $player_info, $players_info, $p_num, $link, $infinity = false) {
        // starting params
//        $player_info = $players_info[$p_num];
        $prev_tile_id = $old_tile['id'];
        $was_closed = $tile['was_closed'];
//        unset($tile['was_closed']);
        $turn = intval($turn);
        // $figure->move_locked; // 0 - not continue; 1 - continue without control; 2 - with control


        // form tiles with figures
        // form players_info
        // form turn
        // form move_locked

        // update
          // update games
          // update board_
          // update players_
        // return
          // return tiles
            // on each tile figures
          // return turn
          // return players_info if changed
          // return move_locked


//        $players_info = [];

//        Handler::Respond([$was_closed]);
        $move_result = self::checkNewTile($ts, $figure, $tile, $old_tile, $player_info, $players_info, $link);
        $move_locked = $move_result['move_locked'];
        $figure = $move_result['figure']; // updated figure
        $tile = $move_result['new_tile']; // new tile
        $kill_pirates = $move_result['kill_pirates']; // return killed pirates to the ship
        $prev_tile_id = $move_result['prev_tile']['id'];
//        Handler::Respond([$figure]);

        // TODO check if turn continues
        if ($move_locked === 0 && !$infinity) { // TODO check if turn is continue and can move by self
//        if (false) { // TODO turn_block
            $turn = $turn == 4 ? 1 : $turn + 1; // next turn
            $figure->active = 0;
        } else {
            $figure->active;
        }

        // TODO kill pirate
        if ($figure->alive == 0) { // TODO $players_info_changed
            $figure->tile = 0;
        }
        $players_info_changed = false;


        // TODO check if pirate is really carry coins or chest to the ship
        $players_info_changed = false;

//        Handler::Respond([var_dump($prev_tile_id)]);
        if (self::updateFigureInfo($figure, $ts, $tile['id'], $prev_tile_id, $move_locked, $link)) { // TODO coins, chest etc
            // figure updated
            $figure->tile = $tile['id'];
        } else {
            Handler::Respond([], 49, 'Не удалось изменить параметры пирата по неизвестной причине.'); // TODO  откатить изменения в players_54124 если тут что-то пошло не так
        }

        if ($figure->type == 'ship' && $figures != []) {
            foreach ($figures as $pirate) {
                self::updatePirateTile($ts, $figure->p_num, $pirate, $tile['id'], $link);
            }
        }

        ///////////////////////////// get all game info !!! IMPORTANT !! Do it after all changes ///////////////////////////
        $game_info = PlayersController::getPlayersInfo($ts, false, true, $link);

        $tile['figures'] = []; // for better goods
        if ($was_closed == 1) {
            self::openTile($tile['id'], $ts, $link); // set tile opened TODO откатить изменения в players_54124 если тут что-то пошло не так
            if ($figure->alive == 1) {
                $tile['figures'][] = $figure; // add our figure
            }
        } else {
            if (isset($game_info['tiles'][$tile['id']])) {
                foreach ($game_info['tiles'][$tile['id']]['figures'] as $new_figure) {
                    if ($figure->p_num == $new_figure['p_num'] && $figure->type == $new_figure['type']) {
                        $new_figure['active'] = $figure->active; // TODO turn is continue
                    }
                    $tile['figures'][] = $new_figure;
                }
            } // check if figures are alive
        }
//        Handler::Respond($game_info['tiles'][$tile['id']]['figures']);

//        if ($players_info_changed == 1) { // check if players_info changed
        $players_info = $game_info['players_info']; // TODO make changes to players info
        $player_info = $players_info[$p_num];
//        }

        // forming old tile figures - or empty if there is no one
        if (isset($game_info['tiles'][$prev_tile_id])) {
            $old_tile_figures = $game_info['tiles'][$prev_tile_id]['figures'];
        } else {
            $old_tile_figures = [];
        }

        $updated_tiles = [];
        foreach ($kill_pirates as $kill_pirate) { // killed pirates return to their ships
            $updated_tiles[$kill_pirate] = $game_info['tiles'][$kill_pirate];
        }

        // free all figures
//        Handler::Respond([$player_info['rum_disabled']]);
        if ($figure->type != $player_info['rum_disabled']) {
            PlayersController::rumDisablePirate($ts, $p_num, 0, $link);
        }

        // return tile and position and turn
        return [
            'old_tile_id' => $prev_tile_id,
            'new_tile_id' => $tile['id'],
            'new_tile' => $tile,
            'old_tile_figures' => $old_tile_figures,
            'figure' => $figure,
            'turn' => $turn,
            'moved' => 1,
            'players_info_changed' => $players_info_changed,
            'players_info' => $game_info['players_info'],
            'was_closed' => $was_closed,
            'move_locked' => $move_locked,
            'updated_tiles' => $updated_tiles
        ];
    }

    public static function updateFigureInfo($figure, $ts, $tile_id, $prev_tile_id, $move_locked, $link, $add_figure = false, $remove_figure = false, $is_object = true) {
        if ($is_object) {
            $figure = (array) $figure;
        }
        $name = $figure['type'];
        if ($remove_figure) {
            $tile_id = 0;
            foreach ($figure as &$item) {
                $item = 0;
            }
        }
        if ($add_figure) {
            $figure['p_num'] = $add_figure;
        }
        if ($figure['type'] == 'ship') {
            $sql = 'UPDATE players_' . $ts . ' SET
            `ship_tile`=' . $tile_id . '
            WHERE `id`=' . $figure['p_num'];
        } else {
            $sql = 'UPDATE players_' . $ts . ' SET
            `' . $name . '_water`=' . $figure['water'] . ',
            `' . $name . '_aboard`=' . $figure['aboard'] . ',
            `' . $name . '_can_attack`=' . $figure['can_attack'] . ',
            `' . $name . '_chest`=' . $figure['chest'] . ',
            `' . $name . '_coins`=' . $figure['coins'] . ',
            `' . $name . '_disabled`=' . $figure['disabled'] . ',
            `' . $name . '_helicopter`=' . $figure['helicopter'] . ',
            `' . $name . '_level`=' . $figure['level'] . ',
            `' . $name . '_alive`=' . $figure['alive'] . ',
            `' . $name . '_tile`=' . $tile_id . ',
            `prev_tile_id`=' . $prev_tile_id;
            if ($figure['type'] == 'missionary') {
                $sql .= ', `is_missionary_drunk`=' . $figure['drunk'];
            }
            if ($add_figure && $figure['type'] != 'p1' && $figure['type'] != 'p2' && $figure['type'] != 'p3') {
                $sql .= ', `is_' . $figure['type'] . '`=1';
            }
            if ($remove_figure && $figure['type'] != 'p1' && $figure['type'] != 'p2' && $figure['type'] != 'p3') {
                $sql .= ', `is_' . $figure['type'] . '`=0';
            }
            $sql .= ',
            `move_locked`="' . $move_locked . '"
            WHERE `id`=' . $figure['p_num'];
        }
        if (mysqli_query($link, $sql)) {
            return true;
        } else {
            Handler::Respond([], 50, 'Не удалось изменить параметры пирата в таблице. ' . mysqli_error($link));
        }
        return false;
    }

    public static function updatePirateTile($ts, $p_num, $pirate, $tile, $link) {
        $sql = 'UPDATE players_' . $ts . ' SET `' . $pirate . '_tile`=' . $tile . ' WHERE `id`=' . $p_num;
        if (mysqli_query($link, $sql)) {
            return true;
        } else {
            Handler::Respond([], 55, 'Не удалось экипаж переместить. ' . mysqli_error($link));
        }
    }

    public static function openTile($id, $ts, $link) {
        $sql = 'UPDATE board_' . $ts . ' SET `closed`=0 WHERE `id`=' . $id;
        if (mysqli_query($link, $sql)) {
            return true;
        } else {
            Handler::Respond([], 42, 'Не удалось открыть клетку в таблице. ' . mysqli_error($link));
        }
    }

    public static function checkCanMove($new_tile, $old_tile, $figure, $game, $user, $player, $link) {
        $new_tile_id = $new_tile['id'];
        $old_tile_id = $old_tile['id'];
        $can_move = false;
        $p_num = $figure->p_num;
        $figure_type = $figure->type;
        $figure->move_locked = $player['move_locked'];
        $response = [
            'moved' => 1
        ];

        if ($figure->move_locked != 0 && $figure->move_locked != $figure->type) {
            $response = [
                'moved' => 0,
//                'block_reason' => 'Здесь стоит чужой корабль.'
                'block_reason' => 'Ходить можно только другим пиратом.'
            ];
            $response['figure'] = $figure;
            Handler::Respond($response);
        }
        if ($figure_type == 'ship') { // check type if ship
            switch ($p_num) { // get ship line and get if tile is near
                case 1:
                    $can_move = $new_tile_id > 0 && $new_tile_id < 14 && ($new_tile_id - 1 == $old_tile_id || $new_tile_id + 1 == $old_tile_id);
                    break;
                case 2:
                    $can_move = ($new_tile_id == $old_tile_id + 13 || $new_tile_id == $old_tile_id - 13) && $new_tile_id != 182 && $new_tile_id != 0;
                    break;
                case 3:
                    $can_move = $new_tile_id > 156 && $new_tile_id < 170 && ($new_tile_id - 1 == $old_tile_id || $new_tile_id + 1 == $old_tile_id);
                    break;
                case 4:
                    $can_move = ($new_tile_id == $old_tile_id + 13 || $new_tile_id == $old_tile_id - 13) && $new_tile_id != 170 && $new_tile_id != -12;
                    break;
            }
            if ($can_move) { // ship moving right
                if (self::checkIfThereShip($game['ts'], $new_tile_id, $link)) { // there enemy ship
                    $response = [
                        'moved' => 0,
                        'block_reason' => 'Здесь стоит чужой корабль.'
                    ];
                } else { // there is no enemy ship
                    // sail with crew
                    $pirates = [
                        'p1',
                        'p2',
                        'p3',
                        'ben',
                        'missionary',
                        'friday'
                    ];
                    $sailing_pirates = [];
                    foreach ($pirates as $pirate) {
                        if ($player[$pirate . '_tile'] == $old_tile_id) {
                            $sailing_pirates[] = $pirate;
                        }
                    }
                    $response['figures'] = $sailing_pirates;
                    // TODO smash enemy pirates
                }
            } else { // ship moving not right
                $response = [
                    'moved' => 0,
                    'block_reason' => 'Корабль двигается только по своей линии на соседние клетки.'
                ];
            }
        } else { // type is pirate
            if ($new_tile_id == $figure->tile) { // check if tile to the same tile
                $response = [
                    'moved' => 0,
                    'block_reason' => 'Нельзя встать на ту же клетку.'
                ];
            } else { // tiles are different
                if ($player[$figure_type . '_alive'] == 0) { // check if not alive
                    $response = [
                        'moved' => 0,
                        'block_reason' => 'Пират не может ходить, потому что мертв.'
                    ];
                } else { // check if alive
                    if ($player[$figure_type . '_disabled'] == 1) { // check if disabled
                        $response = [
                            'moved' => 0,
                            'block_reason' => 'Пират не может ходить.'
                        ];
                    } else if ($player['rum_disabled'] == $figure_type) {
                        $response = [
                            'moved' => 0,
                            'block_reason' => 'Пират пьян и не может ходить.'
                        ];
                    } else { // check if not disabled
                        if ($player[$figure_type . '_water'] == 1) {  // old is water
                            if ($new_tile['type'] == 'water') {  // new tile is water
                                if ($player[$figure_type . '_aboard'] == 1) { // aboard
                                    $response = [
                                        'moved' => 0,
                                        'block_reason' => 'С корабля только на сушу.'
                                    ];
                                } else { // not aboard
                                    $ship_there = self::checkIfThereAnyShip($player['ship_tile'], $new_tile_id, $game['ts'], $figure, $link);
                                    if ($ship_there['ship_is_there']) { // ship is there
                                        $figure = $ship_there['figure'];
                                    } // there is no ship and we can swim then
                                }
                            } else {  // new tile is not water
                                if ($player[$figure_type . '_aboard'] == 1) { // aboard
                                    $go_front = false;
                                    switch ($p_num) { // go in front
                                        case 1:
                                            $go_front = $old_tile_id + 13 == $new_tile_id;
                                            break;
                                        case 2:
                                            $go_front = $old_tile_id - 1 == $new_tile_id;
                                            break;
                                        case 3:
                                            $go_front = $old_tile_id - 13 == $new_tile_id;
                                            break;
                                        case 4:
                                            $go_front = $old_tile_id + 1 == $new_tile_id;
                                            break;
                                    }
                                    if (!$go_front) {// not go front
                                        $response = [
                                            'moved' => 0,
                                            'block_reason' => 'С корабля только прямо.'
                                        ];
                                    } else { // go front
                                        $figure->aboard = 0; // make figure aboard TODO check if there we can go
                                        $figure->water = 0; // make figure not water
                                    }
                                } else { // not aboard
                                    $response = [
                                        'moved' => 0,
                                        'block_reason' => 'Из воды можно только на корабль.'
                                    ];
                                }
                            }
                        } else { // old tile is not water (earth)
                            // TODO arrow, ice, canon etc
                            $availability = self::getAvailableTiles($old_tile['type'], $old_tile['direction'], $old_tile_id);
                            if (in_array($new_tile_id, $availability['available_tiles'])) {
                                if ($new_tile['type'] != 'water') { // new tile is not water
                                    // TODO check new tile
                                } else { // new tile is water
                                    $ship_there = self::checkIfThereAnyShip($player['ship_tile'], $new_tile_id, $game['ts'], $figure, $link);
                                    if ($ship_there['ship_is_there']) { // ship is there
                                        $figure = $ship_there['figure'];
                                    } else { // there is no ship
                                        if ($availability['can_swim']) { // we can swim
                                            $figure->water = 1; // make figure in water
                                        } else { // we can't swim
                                            $response = [
                                                'moved' => 0,
                                                'block_reason' => $ship_there['reason']
                                            ];
                                        }
                                    }
                                }
                            } else {
                                $response = [
                                    'moved' => 0,
                                    'block_reason' => $availability['reason']
                                ];
                            }
                        }
                    }
                }
            }
        }
        if ($response['moved'] == 1) {
            // check tiles
            if ($new_tile['type'] == 'crocodile') {
                if ($old_tile['type'] == 'arrow1' || $new_tile['closed'] == 1) { // check if prev was arrow1
                    // we will kill him later
                } else {
                    $response = [
                        'moved' => 0,
                        'block_reason' => "АААА!!! Крокодил!"
                    ];
                }
            }
        }
        $response['figure'] = $figure;
        return $response;
    }

    public static function checkIfThereAnyShip($ship_tile, $new_tile_id, $game_ts, $figure, $link) {
        $ship_is_there = false;
        if ($ship_tile == $new_tile_id) { // there is our ship
            $ship_is_there = true;
            $figure->aboard = 1; // make figure aboard
            $figure->water = 1; // make figure water
            // TODO collect all coins and chest
        } else if (self::checkIfThereShip($game_ts, $new_tile_id, $link)) { // there enemy ship
            // TODO kill and give all coins and chest
            $ship_is_there = true;
//            $figure->alive = 0; // make figure dead
//            $figure->aboard = 0; // make figure not aboard
//            $figure->water = 0; // make figure not in water
//            $figure->tile = 0; // make figure nowhere
        } // there is no enemy ship
        return [ // TODO lost all coins and chest
            'ship_is_there' => $ship_is_there,
            'figure' => $figure,
            'reason' => 'Нельзя просто так броситься в воду.'
        ];
    }

    public static function getAvailableTiles($type, $n, $tile) {
        $available_tiles = [];
        $can_swim = false;
        $reason = 'Только на соседние клетки.';
        $default_tiles = [
            $tile - 13,
            $tile - 12,
            $tile + 1,
            $tile + 14,
            $tile + 13,
            $tile + 12,
            $tile - 1,
            $tile - 14
        ];
        switch ($type) {
            case 'arrow1':
                $av_num = ($n - 1) * 2;
                $available_tiles = [
                    $default_tiles[$av_num]
                ];
                $can_swim = true;
                $reason = 'Только по направлению стрелки.';
                break;
            case 'arrow1d':
                $av_num = $n * 2 - 1;
                $available_tiles = [
                    $default_tiles[$av_num]
                ];
                $can_swim = true;
                $reason = 'Только по направлению стрелки.';
                break;
            case 'arrow2':
                $av_num = ($n & 1) ? [0,4] : [2,6];
                foreach ($av_num as $num) {
                    $available_tiles[] = $default_tiles[$num];
                }
                $can_swim = true;
                $reason = 'Только по направлениям стрелок.';
                break;
            case 'arrow2d':
                $av_num = ($n & 1) ? [1,5] : [3,7];
                foreach ($av_num as $num) {
                    $available_tiles[] = $default_tiles[$num];
                }
                $can_swim = true;
                $reason = 'Только по направлениям стрелок.';
                break;
            case 'arrow3':
                $av_num[] = $n * 2 - 1;
                $temp_num = $n * 2 + 2;
                $av_num[] = $temp_num > 7 ? $temp_num - 8 : $temp_num;
                $temp_num = $n * 2 + 4;
                $av_num[] = $temp_num > 7 ? $temp_num - 8 : $temp_num;
                foreach ($av_num as $num) {
                    $available_tiles[] = $default_tiles[$num];
                }
                $can_swim = true;
                $reason = 'Только по направлениям стрелок.';
                break;
            case 'arrow4':
                $available_tiles = [
                    $default_tiles[0],
                    $default_tiles[2],
                    $default_tiles[4],
                    $default_tiles[6],
                ];
                $can_swim = true;
                $reason = 'Только по направлениям стрелок.';
                break;
            case 'arrow4d':
                $available_tiles = [
                    $default_tiles[1],
                    $default_tiles[3],
                    $default_tiles[5],
                    $default_tiles[7],
                ];
                $can_swim = true;
                $reason = 'Только по направлениям стрелок.';
                break;
            // example
            // 7 0 1
            // 6 - 2
            // 5 4 3
            default:
                $available_tiles = $default_tiles;
        }
        return [
            'available_tiles' => $available_tiles,
            'reason' => $reason,
            'can_swim' => $can_swim
        ];
    }

    public static function checkIfThereShip($ts, $new_tile_id, $link) {
        $sql = 'SELECT * FROM players_' . $ts . ' WHERE `ship_tile`="' . $new_tile_id . '" LIMIT 1';
        if ($result = mysqli_query($link, $sql)) { // check if enemy ship is not there
            while($row = mysqli_fetch_array($result)) {
                return true;
            }
        } else { // enemy ship is there
            $error_msg = 'Неудачная попытка найти на этой клетке чужой корабль ' . mysqli_error($link);
            Handler::Respond([], 39, $error_msg);
        }
        return false;
    }

    public static function getFiguresOnTiles($player, $tiles = []) {
        $tiles[$player['ship_tile']]['figures'][] = [
            'p_num' => $player['id'],
            'type' =>'ship',
            'tile' => $player['ship_tile'],
            'aboard' => 1
        ];
        $pirate_types = self::pirate_types;
        $pt = 0;
        foreach ($pirate_types as $pirate_type) {
            $add_type = true;
            if ($pt > 2) {
                $add_type = $player['is_' . $pirate_type] == 1;
            }
            if ($player[$pirate_type . '_alive'] == 1) {
                $tiles[$player[$pirate_type . '_tile']]['figures'][] = self::addFigureToTile($player, $pirate_type);
            }
            $pt++;
        }
        return $tiles;
    }

    public static function checkFigureOnTile($figure, $tile) {
        $pirate_ok = false;
        foreach ($tile['figures'] as $tile_figure) {
            if ($figure->type == $tile_figure['type'] && $figure->p_num == $tile_figure['p_num']) {
                $compares = $figure->type == 'ship'
                    ? [
                        'p_num',
                        'tile',
                        'aboard'
                    ] : [
                        'p_num',
                        'tile',
                        'aboard',
                        'can_attack',
                        'disabled',
                        'helicopter',
                        'level',
                        'water',
                        'drunk',
                        'alive'
                ];
                foreach ($compares as $compare) {
                    if (!isset($figure->$compare) || !isset($tile_figure[$compare]) || $figure->$compare != $tile_figure[$compare]) {
                        $response = [
                            'moved' => 0,
                            'block_reason' => 'С пиратом что-то не так, перезайдите.'
                        ];
                        $response['figure'] = $figure;
                        Handler::Respond($response);
                    }
                    $pirate_ok = true;
                }
            }
        }
        if (!$pirate_ok) {
            $resp = [
                'moved' => 0,
                'block_reason' => 'Пират не совпадает, перезайдите.'
            ];
            Handler::Respond($resp);
        }
    }
}
