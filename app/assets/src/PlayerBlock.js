import React from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faUser, faCoins, faGem, faWineBottle, faTimes } from '@fortawesome/free-solid-svg-icons'
import LogOutButton from "./LogOutButton";
import Button from "react-bootstrap/Button";

export default class PlayerBlock extends React.Component{

  pirateIcon = (alive, id) => {
    let class_name = 'pirate_icon';
    class_name += ' player_' + id;
    class_name += alive ? '' : ' dead';
    return(
      <span
        className={class_name}
      >
        <FontAwesomeIcon
          icon={faUser}
        />
        {!alive
          ? <FontAwesomeIcon
            icon={faTimes}
            className="dead-icon"
          />
          : null
        }
      </span>
    )
  };

  render() {

    let {
      active,
      player_info,
      // name,
      // id,
      your_player,
      has_chest,
      // score,
      // rum,
      p1_alive,
      p2_alive,
      p3_alive,
      is_ben,
      is_missionary,
      is_friday,
      is_missionary_drunk,
      is_weed,
      is_lighthouse,
      endLighthouse
    } = this.props;

    return(
      <div className="player-block">
        <h2 className={active ? "player-active" : ''}>{player_info.name}</h2>
        <div className={your_player ? "player-content your_player" : "player-content"}>
          <div className="pirates-block">
            {this.pirateIcon(p1_alive, player_info.p_num)}
            {this.pirateIcon(p2_alive, player_info.p_num)}
            {this.pirateIcon(p3_alive, player_info.p_num)}
            {is_ben
              ? this.pirateIcon(true, 'ben')
              : null
            }
            {is_friday
              ? this.pirateIcon(true, 'friday')
              : null
            }
            {is_missionary
              ? this.pirateIcon(true, is_missionary_drunk ? 'missionary-drunk' : 'missionary')
              : null
            }
          </div>
          <p><FontAwesomeIcon icon={faCoins} /> {player_info.score}
            {has_chest
              ? <React.Fragment>+ <FontAwesomeIcon icon={faGem} /></React.Fragment>
              : null
            }
          </p>
          <p><FontAwesomeIcon icon={faWineBottle} /> {player_info.rum}</p>
          <p>
            {is_weed
              ? <span>WEED</span>
              : null
            }
            {is_lighthouse > 1
              ? <span>L: {is_lighthouse - 1}</span>
              : null
            }
          </p>
          {is_lighthouse == 1 && your_player
            ? <Button variant={"primary"} onClick={endLighthouse} className="endlighthouse-button">Покинуть маяк</Button>
            : null
          }
        </div>
      </div>
    )
  }
}