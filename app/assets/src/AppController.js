import React, { Component, useEffect } from 'react';
import Cookies from 'universal-cookie';
import { withAlert } from 'react-alert'

import 'bootstrap/dist/css/bootstrap.min.css';

import GamePage from './GamePage.js';
import Chat from './Chat.js';
import WelcomeScreen from './WelcomeScreen.js';
import { Spinner } from "react-bootstrap";



class AppController extends Component {

  cookies = new Cookies();

  state = {
    name: '',
    showWelcome: false,
    showGame: false,
    loading: true,
    logged_in: false,
    showContinue: false,
    showQueue: false,
    queue_players: [],
    players_info: {},
    tiles: [],
    game_id: 0,
    game_date: '',
    game_ts: 0,
    session_id: sessionStorage.getItem('session_id'),
    user_id: sessionStorage.getItem('user_id'),
    player_num: 0,
    turn: 0,
    max_players: 0
  };

  componentDidMount() {
    let cookie_name = this.cookies.get('name');
    if (cookie_name) {
      // console.log(cookie_name);
      this.getKey(cookie_name);
    } else {
      if (this.state.session_id && sessionStorage.getItem('user_name')) {
        // console.log(sessionStorage.getItem('user_name'));
        this.getKey(sessionStorage.getItem('user_name'));
      } else {
        this.setState({ showWelcome: true });
        this.loading();
      }
    }
  }

  loading = ($on = false) => {
    this.setState({ loading: $on });
  };

  getKey = (name) => {
    this.loading(true);
    this.setState({ name: name });
    let data = {
      req: 'getKey',
      name: name
    };
    fetch("/ajax", {
      method: 'POST',
      body: JSON.stringify(data)
    } )
      .then(res => res.json())
      .then(
        (result) => {
          if (result.error == 0) {
            if (result.found == 1) {
              this.setState({
                showWelcome: true,
                logged_in: true,
                session_id: result.session_id,
                user_id: result.user_id,
                user_name: result.user_name
              });
              sessionStorage.setItem('session_id', result.session_id);
              sessionStorage.setItem('user_id', result.user_id);
              sessionStorage.setItem('user_name', name);
              if (result.game_id != 0 && result.game_started == 1) {
                this.setState({
                  showContinue: true,
                  game_date: result.game_date,
                  game_id: result.game_id,
                });
              } else {
                this.setState({
                  showContinue: false,
                });
              }
            } else {
              if (result.added == 1) {
                this.setState({
                  showWelcome: true,
                  logged_in: true,
                  session_id: result.session_id,
                  user_id: result.user_id,
                  user_name: result.user_name
                });
                sessionStorage.setItem('session_id', result.session_id);
                sessionStorage.setItem('user_id', result.user_id);
                sessionStorage.setItem('user_name', name);
              } else {
                this.showError('Не добавлен игрок');
                // TODO unknown error msg
              }
            }
            this.loading(false);
          } else {
            this.showError('Ошибка: ' + result.error + '. ' + result.error_msg);
            this.loading(false);
          }
        },
        (error) => {
          this.showError('Ошибка! ' + error);
          this.loading(false);
        }
      );
  };

  handleLoginSubmit = (name) => {
    this.getKey(name);
    this.setState({ name: name });
    // this.cookies.set('name', name, { path: '/', expires: new Date(Date.now() + 24 * 60 * 60 * 1000)});
    this.cookies.set('name', name, { path: '/', expires: new Date(Date.now() + 60 * 1000)});
  };

  logOut = () => {
    this.cookies.remove('name', { path: '/'});
    sessionStorage.clear();
    this.setState({
      name: '',
      showWelcome: true,
      showGame: false,
      loading: false,
      logged_in: false,
      showContinue: false,
      showQueue: false,
      user_id: 0
    });
  };

  startNewGame = () => {
    this.setState({
      loading: true,
    });
    let data = {
      req: 'startNewGame',
      name: this.state.name
    };
    fetch("/ajax", {
      method: 'POST',
      body: JSON.stringify(data)
    } )
      .then(res => res.json())
      .then(
        (result) => {
          if (result.error == 0) {
                if (result.game_started == 1) {
                  this.startTheGame(result);
                } else {
                  this.setState({
                    queue_players: result.queue_players,
                    showQueue: true,
                    showWelcome: true,
                    logged_in: true,
                    showContinue: false,
                    game_id: result.game_id,
                    game_ts: result.game_ts
                  })
                }
            this.loading(false);
          } else {
            this.showError('Ошибка: ' + result.error + '. ' + result.error_msg);
            this.loading(false);
          }
        },
        (error) => {
          this.showError('Ошибка! ' + error);
          this.loading(false);
        }
      );
  };

  startTheGame = (data) => {
    sessionStorage.setItem('game_ts', data.game_ts);
    sessionStorage.setItem('game_id', data.game_id);
    this.setState({
      player_num: data.player_num,
      game_ts: data.game_ts,
      game_id: data.game_id,
      tiles: data.tiles,
      players_info: data.players_info,
      turn: data.turn,
      max_players: data.max_players,
      showQueue: false,
      showGame: true,
      showWelcome: false,
      logged_in: true,
      showContinue: false
    });
  };

  continueTheGame =() => {
    this.setState({
      loading: true,
    });
    let data = {
      req: 'continueTheGame',
      name: this.state.name,
      session_id: this.state.session_id
    };
    fetch("/ajax", {
      method: 'POST',
      body: JSON.stringify(data)
    } )
      .then(res => res.json())
      .then(
        (result) => {
          if (result.error == 0) {
            this.startTheGame(result);
            this.loading(false);
          } else {
            this.showError('Ошибка: ' + result.error + '. ' + result.error_msg);
            this.loading(false);
          }
        },
        (error) => {
          this.showError('Ошибка! ' + error);
          this.loading(false);
        }
      );
  };

  updatePlayersList = (players_list) => {
    this.setState({ queue_players: players_list });
  };

  showError = (msg, info = false) => {
    if (info) {
      this.props.alert.success(msg)
    } else {
      this.props.alert.error(msg)
    }
  };

  render() {

    // let cookieName = this.cookies.get('name');

    let {
      session_id,
      game_id,
      user_id,
      player_num,
      name,
      players_info,
      tiles,
      game_ts,
      turn,
      max_players,
      showContinue,
      showWelcome,
      showQueue,
      logged_in,
      queue_players,
      game_date,
      loading,
    } = this.state;

    return (
      <div className={this.state.showGame ? "app-controller show-game" : "app-controller"}>
        {this.state.showGame
          ? <GamePage
            player_num={player_num}
            player_name={name}
            players_info={players_info}
            tiles={tiles}
            game_ts={game_ts}
            game_id={game_id}
            logOut={this.logOut}
            turn={turn}
            max_players={max_players}
            showError={this.showError}
          />
          : null
        }
        {showWelcome
          ? <WelcomeScreen
            showContinue={showContinue}
            showQueue={showQueue}
            logged_in={logged_in}
            name={name}
            queue_players={queue_players}
            handleLoginSubmit={this.handleLoginSubmit}
            logOut={this.logOut}
            startNewGame={this.startNewGame}
            game_id={game_id}
            startTheGame={this.startTheGame}
            game_date={game_date}
            continueTheGame={this.continueTheGame}
            user_id={user_id}
            session_id={session_id}
            updatePlayersList={this.updatePlayersList}
            showError={this.showError}
            loading={loading}
          />
          : null
        }
        {loading
          ? <Spinner className="loading-block" animation="border" />
          : null
        }
        {this.state.game_ts != 0
          ? <Chat
            name={this.state.name}
            ts={this.state.game_ts}
            showError={this.showError}
          />
          : null
        }
      </div>
    );
  }
}

export default withAlert()(AppController)