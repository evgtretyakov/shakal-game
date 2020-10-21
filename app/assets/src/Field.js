import React from 'react';

import Tile from "./Tile";
import { Spinner } from "react-bootstrap";
import CheckGameUpdate from "./CheckGameUpdate";

export default class Field extends React.Component{

  state = {
    // figures: this.props.figures,
    // player_name: this.props.player_name,
    // loading: false,
    // waiting: this.props.turn != this.props.player_num,
    // session_id: sessionStorage.getItem('session_id'),
    // tiles: this.props.tiles,
    // active_figure: {},
    // active_figure_selected: false,
    // player_num: this.props.player_num,
    // move_locked: this.props.move_locked
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

  render() {

    let { player_num, turn, game_id, tiles, waiting, getUpdate, chooseFigure, moveFigure } = this.props;

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
            chooseFigure={chooseFigure}
            moveFigure={moveFigure}
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
            getUpdate={getUpdate}
            game_id={game_id}
            player_num={player_num}
            waiting={waiting}
            showError={this.props.showError}
          />
      </div>
    )
  }
}