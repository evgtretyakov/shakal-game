import React, { Component } from 'react';

import Header from './Header.js';
import Field from './Field.js';
import CheckGameUpdate from "./CheckGameUpdate";

export default class GamePage extends Component {

  state = {
    turn: this.props.turn,
    player_num: this.props.player_num,
    players_info: this.props.players_info,
    tiles: this.props.tiles,
    max_players: this.props.max_players,
    player_name: this.props.player_name,
    game_id: this.props.game_id,
    infinity: 0,
    move_locked: this.props.players_info[this.props.player_num].move_locked,
    session_id: sessionStorage.getItem('session_id'),
    active_figure: {},
    active_figure_selected: false,
    loading: false,
    waiting: this.props.turn != this.props.player_num,
  };

  loading = (load = false) => {
    this.setState({ loading: load});
  };

  changePlayersInfo = (info) => {
    this.setState({players_info: info});
    // console.log(info);
  };

  changeTurn = (turn) => {
    this.setState({
      turn: turn,
      waiting: turn != this.state.player_num
    });
  };

  changeInfinity = () => {
    this.setState(prevState => {
      return {
        infinity: prevState.infinity == 1 ? 0 : 1
      }
    })
  };

  getUpdate = (data) => {
    this.changePlayersInfo(data.players_info);
    if (data.turn != this.state.turn) {
      this.changeTurn(data.turn);
    }
    this.setState({
      move_locked: data.move_locked,
      tiles: data.tiles
    });
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

  moveFigure = (id, lighthouse_end = false) => {
    // console.log('MOVED');
    if (this.state.active_figure_selected && this.state.active_figure.tile != id) {
      // console.log(id)
      this.loading(true);
      let data = {
        req: 'moveFigure',
        id: id,
        session_id: this.state.session_id,
        active_figure: this.state.active_figure,
        player_name: this.state.player_name,
        infinity: this.state.infinity,
        lighthouse_end: lighthouse_end ? 1 : 0
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
                this.changePlayersInfo(result.players_info);
                // }
                this.setState(prevState => {
                  let is_my_turn = result.turn == prevState.player_num;
                  prevState.tiles[result.old_tile_id].figures = result.old_tile_figures;
                  prevState.tiles[result.new_tile_id] = result.new_tile;
                  // console.log(result.updated_tiles);
                  if (result.updated_tiles.length != []) {
                    {Object.keys(result.updated_tiles).map((key, value) => {
                      console.log('key = ' + key);
                      console.log('value = ' + value);
                      console.log(prevState.tiles[key].figures);
                      console.log(result.updated_tiles[key].figures);
                      prevState.tiles[key] = result.updated_tiles[key];
                    })}
                  }
                  return {
                    tiles: prevState.tiles,
                    active_figure: (is_my_turn && result.figure.alive == 1) ? result.figure : {},
                    active_figure_selected: (is_my_turn && result.figure.alive == 1),
                    move_locked: result.move_locked
                  };
                });
                if (result.show_alert != undefined && result.show_alert != '') {
                  this.props.showError(result.show_alert);
                }
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
    } else {
      this.props.showError('Не выбрана ни одна фигура');
    }
  };

  endLighthouse = () => {
    this.moveFigure(1, true);
  };

  render() {

    let { logOut, showError }  = this.props;

    let { infinity, player_num, max_players, player_name, game_id, players_info, tiles, turn, move_locked, waiting }  = this.state;

    return (
      <div className="gamePage">
        <Header
          max_players={max_players}
          player_num={player_num}
          turn={turn}
          players_info={players_info}
          logOut={logOut}
          infinity={infinity}
          changeInfinity={this.changeInfinity}
          endLighthouse={this.endLighthouse}
        />
        <section className="stage">
          <figure className="ball"/>
        </section>
        <Field
          player_num={player_num}
          player_name={player_name}
          turn={turn}
          tiles={tiles}
          game_id={game_id}
          move_locked={move_locked}
          waiting={waiting}
          getUpdate={this.getUpdate}
          showError={showError}
          chooseFigure={this.chooseFigure}
          moveFigure={this.moveFigure}
        />
      </div>
    );
  }
}