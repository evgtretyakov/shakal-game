import React, { Component } from 'react';
import { ListGroup, Spinner } from "react-bootstrap";
import CheckGameStarted from "./CheckGameStarted";

export default class GameQueue extends Component {

  state = {
    game_started: 0
  };

  PlayersRows = () => {
    let { queue_players } = this.props;
    let $arr = [];
    for(let $i = 0; $i < queue_players.length; $i++) {
      $arr.push(
        <ListGroup.Item variant="success" key={$i}>{queue_players[$i]}</ListGroup.Item>
      )
    }
    for(let $i = $arr.length; $i < 4; $i++) {
      $arr.push(
        <ListGroup.Item key={$i}><Spinner animation="border" size="sm" className="little-spinner"/></ListGroup.Item>
      )
    }
    return $arr;
  };

  startTheGame = (data) => {
    this.setState({
      game_started: 1
    });
    this.props.startTheGame(data);
  };

  render() {

    let { updatePlayersList, user_id, game_id, session_id } = this.props;

    return(
      <div>
        <h4>
          Ожидание игроков
        </h4>
        {this.PlayersRows()}
        {this.state.game_started == 0
          ? <CheckGameStarted
            startTheGame={this.startTheGame}
            session_id={session_id}
            game_id={game_id}
            user_id={user_id}
            game_started={this.state.game_started}
            updatePlayersList={updatePlayersList}
            showError={this.props.showError}
          />
          : null
        }
      </div>
    )
  }
}