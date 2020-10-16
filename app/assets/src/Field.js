import React from 'react';

import Tile from "./Tile";
import { Spinner } from "react-bootstrap";
import CheckGameUpdate from "./CheckGameUpdate";

export default class Field extends React.Component{

  state = {
    // figures: this.props.figures,
    tiles: this.props.tiles,
    // player_name: this.props.player_name,
    active_figure: {},
    active_figure_selected: false,
    loading: false,
    session_id: sessionStorage.getItem('session_id'),
    waiting: this.props.turn != this.props.player_num,
    player_num: this.props.player_num,
    move_locked: this.props.move_locked
  };

  loading = (load = false) => {
    this.setState({ loading: load});
  };

  rotateBoard = ($p, $i, $y) => {
    switch ($p) {
      case 1:
        return $y + $i * 13; // 1
      case 2:
        return $y * 13 - $i; // 2
      case 3:
        return 13 * (13 - $i) - $y + 1; // 3
      case 4:
        return 13 * (13 - $y) + $i + 1; // 4
      default:
        return $y + $i * 13; // 1
    }
  };

  chooseFigure = (features, player_num, turn) => {
    if (!this.state.loading) {
      if (player_num == features.p_num) {
        if (turn == player_num) {
          if (this.state.move_locked == 0 || this.state.move_locked == features.type) {
            let new_active = !features.active;
            this.setState(prevState => {
              if (prevState.active_figure_selected) {
                let old_figure = prevState.active_figure;
                prevState.tiles[old_figure.tile].figures.map( figure => {
                  figure.active = false;
                })
              }
              prevState.tiles[features.tile].figures[features.id].active = new_active;
              let new_active_figure = new_active ? features : {};
              return {
                tiles: prevState.tiles,
                active_figure: new_active_figure,
                active_figure_selected: new_active
              }
            })
          } else {
            this.props.showError('Вам надо продолжить ход!');
          }
        } else {
          this.props.showError('Это не ваш ход!');
        }
      } else {
        this.props.showError('Это не ваш пират!');
      }
    } else {
      this.props.showError('Нельзя выбрать фишку, пока идет загрузка.');
    }
  };

  moveFigure = (id) => {
    // console.log('MOVED');
    if (this.state.active_figure_selected && this.state.active_figure.tile != id) {
      // console.log(id)
      this.loading(true);
      let data = {
        req: 'moveFigure',
        id: id,
        session_id: this.state.session_id,
        active_figure: this.state.active_figure,
        player_name: this.props.player_name,
        infinity: this.props.infinity
      };
      fetch("/ajax", {
        method: 'POST',
        body: JSON.stringify(data)
      } )
        .then(res => res.json())
        .then(
          (result) => {
            if (result.error == 0) {
              if (result.moved == 1) {
                // if (result.players_info_changed == 1) {
                  this.props.changePlayersInfo(result.players_info);
                // }
                this.setState(prevState => {
                  let is_my_turn = result.turn == prevState.player_num;
                  prevState.tiles[result.old_tile_id].figures = result.old_tile_figures;
                  prevState.tiles[result.new_tile_id] = result.new_tile;
                  console.log(result.updated_tiles);
                  if (result.updated_tiles.length != []) {
                    {Object.keys(result.updated_tiles).map((key, value) => {
                      console.log('key = ' + key);
                      console.log('value = ' + value);
                      console.log(prevState.tiles[key].figures);
                      console.log(result.updated_tiles[key].figures);
                      prevState.tiles[key].figures = result.updated_tiles[key].figures;
                    })}
                  }
                  return {
                    tiles: prevState.tiles,
                    active_figure: (is_my_turn && result.figure.alive == 1) ? result.figure : {},
                    active_figure_selected: (is_my_turn && result.figure.alive == 1),
                    move_locked: result.move_locked
                  };
                });
                this.changeTurn(result.turn);
              } else {
                this.props.showError('Ход невозможен: ' + result.block_reason);
              }
              this.loading(false);
            } else {
              this.props.showError('Ошибка: ' + result.error + '. ' + result.error_msg);
              this.loading(false);
            }
          },
          (error) => {
            this.props.showError('Ошибка! ' + error);
            this.loading(false);
          }
        );
    }
  };

  getUpdate = (data) => {
    // console.log(data);
    // if (data.players_info_changed == 1) {
      this.props.changePlayersInfo(data.players_info);
    // }
    if (data.turn != this.props.turn) {
      this.changeTurn(data.turn);
    }
    this.setState({
        move_locked: data.move_locked,
        tiles: data.tiles
    });
  };

  changeTurn = (turn) => {
    this.props.changeTurn(turn);
    this.setState({
      waiting: turn != this.state.player_num
    });
  };

  render() {

    let { player_num, turn, game_id } = this.props;

    let { tiles, waiting } = this.state;

    // place tiles
    let rows = [];
    for (let $i = 0; $i < 13; $i++) {
      let cells = [];
      for (let $y = 1; $y < 14; $y++) {
        let $m = this.rotateBoard(player_num, $i, $y);
        cells.push(
          <Tile
            key={$m}
            type={tiles[$m].type}
            id={tiles[$m].id}
            direction={tiles[$m].direction}
            closed={tiles[$m].closed}
            figures={tiles[$m].figures}
            chooseFigure={this.chooseFigure}
            moveFigure={this.moveFigure}
            turn={turn}
            player_num={player_num}
          />
        )
      }
      rows.push(<div className="row-content" key={$i}>{cells}</div>);
    }

    return(
      <div className="field-block">
        <div className="field-content">{rows}</div>
        {this.state.loading
          ? <Spinner className="loading-field" animation="border" />
          : null
        }
        {/*{waiting*/}
          <CheckGameUpdate
            getUpdate={this.getUpdate}
            game_id={game_id}
            player_num={player_num}
            waiting={waiting}
            showError={this.props.showError}
          />
      </div>
    )
  }
}