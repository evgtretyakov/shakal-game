import React from 'react';
import ClickNHold from 'react-click-n-hold';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faSkullCrossbones } from "@fortawesome/free-solid-svg-icons";

import PlayerBlock from './PlayerBlock.js';
import LogOutButton from './LogOutButton.js';
import Button from "react-bootstrap/Button";

export default class Header extends React.Component{

  render() {

    let { logOut, changeInfinity, max_players, players_info, player_num, turn, infinity } = this.props;

    return(
      <div className="header">
        {/*<div><FontAwesomeIcon icon={faSkullCrossbones} /></div>*/}
        {Object.keys(players_info).map((key, value) =>
          <PlayerBlock
            key={players_info[key].p_num}
            player_info={players_info[key]}
            active={players_info[key].p_num == turn}
            your_player={players_info[key].p_num == player_num}
            has_chest={players_info[key].has_chest == 1}
            is_weed={players_info[key].is_weed == 1}
            is_lighthouse={players_info[key].is_lighthouse == 1}
            p1_alive={players_info[key].p1_alive == 1}
            p2_alive={players_info[key].p2_alive == 1}
            p3_alive={players_info[key].p3_alive == 1}
            is_ben={players_info[key].is_ben == 1}
            is_missionary={players_info[key].is_missionary == 1}
            is_friday={players_info[key].is_friday == 1}
            is_missionary_drunk={players_info[key].is_missionary_drunk == 1}
          />
        )}
        <LogOutButton
          logOut={logOut}
        />
        <Button active={infinity == 1} variant={"primary"} onClick={changeInfinity} className="cheats-button-1">{infinity == 1 ? 'Выкл.' : 'Вкл.'} ходы</Button>
      </div>
    )
  }
}