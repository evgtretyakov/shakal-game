import React, { Component, useEffect } from 'react';

import LoginForm from './LoginForm.js';
import { Card, Button } from "react-bootstrap";
import GameQueue from "./GameQueue";

export default class WelcomeScreen extends Component {

  state = {
    game_started: 0,
    show_all_tiles: 0
  };

  handleLoginSubmit = (name) => {
    this.props.handleLoginSubmit(name);
  };

  handleLogOut = () => {
    this.props.logOut();
  };

  startNewGame = () => {
    this.props.startNewGame();
  };

  startTheGame = (data) => {
    this.setState({
      game_started: 1
    });
    this.props.startTheGame(data);
  };

  render() {

    let { continueTheGame, updatePlayersList, changeShowAllTiles, showContinue, showQueue, logged_in, name, queue_players, game_date, game_id, session_id, user_id, loading, show_all_tiles }= this.props;

    // startCheckingGameReady(this.state.game_started);

    return (
      <Card className="WelcomeScreen">
        <Card.Body>
          {showQueue
            ? <GameQueue
              name={name}
              queue_players={queue_players}
              game_id={game_id}
              session_id={session_id}
              user_id={user_id}
              startTheGame={this.startTheGame}
              updatePlayersList={updatePlayersList}
              showError={this.props.showError}
            />
            : <React.Fragment>
              <img className="mb-4 shakal-icon" src="/app/assets/img/shakal.jpg" alt=""/>
              <h1 className="h3 mb-3 font-weight-normal">Ебучий шакал</h1>
              {!logged_in
                ? <LoginForm
                  name={name}
                  handleLoginSubmit={this.handleLoginSubmit}
                  showError={this.props.showError}
                  loading={loading}
                />
                : <React.Fragment>
                  <h2 className="h4 mb-3 font-weight-normal">{name}</h2>
                  <Button variant="success" size="lg" block onClick={this.startNewGame} disabled={loading}>Начать новую игру</Button>
                </React.Fragment>
              }
              {showContinue
                ? <Button variant="info" size="lg" block onClick={continueTheGame} disabled={loading}>Продолжить игру от {game_date}</Button>
                : null
              }
              <Button active={show_all_tiles == 1} size="lg" variant="primary" block onClick={changeShowAllTiles}>{show_all_tiles == 1 ? 'Выкл.' : 'Вкл.'} клетки</Button>
            </React.Fragment>
          }
          <br/>
          {logged_in
            ? <Button variant="outline-danger" size="lg" block onClick={this.handleLogOut} disabled={loading}>Выйти</Button>
            : null
          }
        </Card.Body>
      </Card>
    );
  }
}