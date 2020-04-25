import React, { Component } from 'react';

import Header from './Header.js';
import Field from './Field.js';

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
    move_locked: this.props.players_info[this.props.player_num].move_locked
  };

  changePlayersInfo = (info) => {
    this.setState({players_info: info});
    // console.log(info);
  };

  changeTurn = (turn) => {
    this.setState({
      turn: turn
    });
  };

  changeInfinity = () => {
    this.setState(prevState => {
      return {
        infinity: prevState.infinity == 1 ? 0 : 1
      }
    })
  };

  render() {


    let { logOut }  = this.props;

    let { infinity, player_num, max_players, player_name, game_id, players_info, tiles, turn, move_locked }  = this.state;

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
          changeTurn={this.changeTurn}
          changePlayersInfo={this.changePlayersInfo}
          showError={this.props.showError}
          infinity={infinity}
          move_locked={move_locked}
        />
      </div>
    );
  }
}