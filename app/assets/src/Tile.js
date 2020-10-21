import React from 'react';

import Teonka from './Teonka.js'
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faArrowsAlt, faArrowsAltV, faArrowUp, faLongArrowAltUp } from "@fortawesome/free-solid-svg-icons";
import Button from "react-bootstrap/Button";
import ClickNHold from "react-click-n-hold";

export default class Tile extends React.Component{

  getTileIcon = (type, index) => {

    let tileIcons = [
      <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="long-arrow-alt-up"
           className="svg-inline--fa fa-long-arrow-alt-up fa-w-8 tile-icon" role="img"
           xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512">
        <path fill="currentColor"
              d="M88 166.059V468c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12V166.059h46.059c21.382 0 32.09-25.851 16.971-40.971l-86.059-86.059c-9.373-9.373-24.569-9.373-33.941 0l-86.059 86.059c-15.119 15.119-4.411 40.971 16.971 40.971H88z" stroke="white" strokeWidth="10"/>
      </svg>,
      <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="arrows-alt-v"
           className="svg-inline--fa fa-arrows-alt-v fa-w-8 tile-icon" role="img"
           xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512">
        <path fill="currentColor"
              d="M214.059 377.941H168V134.059h46.059c21.382 0 32.09-25.851 16.971-40.971L144.971 7.029c-9.373-9.373-24.568-9.373-33.941 0L24.971 93.088c-15.119 15.119-4.411 40.971 16.971 40.971H88v243.882H41.941c-21.382 0-32.09 25.851-16.971 40.971l86.059 86.059c9.373 9.373 24.568 9.373 33.941 0l86.059-86.059c15.12-15.119 4.412-40.971-16.97-40.971z" stroke="white" strokeWidth="10"/>
      </svg>,
      <svg aria-hidden="true" className="svg-inline--fa fa-arrows-alt fa-w-16 tile-icon" viewBox="0 0 512 512">
        <path fill="currentColor"
          d="m 49.835613,262.26122 1.5e-5,-126.19606 c -1.5e-5,-14.93555 12.106352,-27.0419 27.041899,-27.0419 l 126.196073,1e-5 c 24.09158,0 36.1573,29.12853 19.12078,46.16348 l -40.76242,40.76245 80.31542,80.31382 80.31382,-80.31382 -40.76245,-40.76246 c -17.03572,-17.03573 -4.97078,-46.16347 19.1216,-46.16429 l 126.1961,2e-5 c 14.93554,1e-5 27.04268,12.10714 27.04189,27.04191 l -1e-5,126.19606 c 0,24.09161 -29.12854,36.15733 -46.16427,19.12157 l -40.81583,-40.81579 -93.41373,91.42102 0.13308,106.13957 60.13644,0.25509 c 24.09203,0.1022 31.97587,28.83172 18.99041,41.46564 l -92.92937,93.62013 c -10.52184,10.60007 -28.60674,9.27033 -38.89095,-1.56047 l -86.7302,-91.33975 c -17.03942,-17.94503 -6.03409,-43.05248 13.89989,-42.92894 l 62.41968,0.38691 -0.0777,-105.97762 -93.3992,-91.48155 -40.816613,40.81658 c -17.035716,17.03575 -46.163468,4.97085 -46.164265,-19.12157 z" stroke="white" strokeWidth="10"/>
      </svg>,
      <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="arrows-alt"
           className="svg-inline--fa fa-arrows-alt fa-w-16 tile-icon" role="img"
           xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path fill="currentColor"
              d="M352.201 425.775l-79.196 79.196c-9.373 9.373-24.568 9.373-33.941 0l-79.196-79.196c-15.119-15.119-4.411-40.971 16.971-40.97h51.162L228 284H127.196v51.162c0 21.382-25.851 32.09-40.971 16.971L7.029 272.937c-9.373-9.373-9.373-24.569 0-33.941L86.225 159.8c15.119-15.119 40.971-4.411 40.971 16.971V228H228V127.196h-51.23c-21.382 0-32.09-25.851-16.971-40.971l79.196-79.196c9.373-9.373 24.568-9.373 33.941 0l79.196 79.196c15.119 15.119 4.411 40.971-16.971 40.971h-51.162V228h100.804v-51.162c0-21.382 25.851-32.09 40.97-16.971l79.196 79.196c9.373 9.373 9.373 24.569 0 33.941L425.773 352.2c-15.119 15.119-40.971 4.411-40.97-16.971V284H284v100.804h51.23c21.382 0 32.09 25.851 16.971 40.971z" stroke="white" strokeWidth="10"/>
      </svg>
    ];

    return <div className={type + "-icon tile-icon"}>
      {tileIcons[index]}
    </div>
  };

  defaultFaIcon = <FontAwesomeIcon
    className={"tile-icon"}
    icon={faArrowsAlt}
  />;
  iconFa = <i className="fa-icon-tile"></i>;

  start = (e) => {
    console.log('START');
  };

  end = (e, enough) => {
    console.log('END');
    console.log(enough ? 'Click released after enough time': 'Click released too soon');
  };

  clickNHold = (e) => {
    console.log('CLICK AND HOLD');
  };

  render() {

    let { moveFigure, chooseFigure, id, figures, player_num, turn, type, closed, direction } = this.props;

    let icon = null;
    switch (type) {
      case 'arrow1':
      case 'arrow1d':
        icon = this.getTileIcon(type, 0);
        break;
      case 'arrow2':
      case 'arrow2d':
        icon = this.getTileIcon(type, 1);
        break;
      case 'arrow3':
        icon = this.getTileIcon(type, 2);
        break;
      case 'arrow4':
      case 'arrow4d':
        icon = this.getTileIcon(type, 3);
        break;
      default:
        // icon = <div className="ordinary-tile">{id}</div>;
        icon = null;
    }

    return(
      <ClickNHold
        time={1.5} // Time to keep pressing. Default is 2
        // onStart={this.start} // Start callback
        onClickNHold={() => moveFigure(id)} //Timeout callback
        // onEnd={this.end} // Click release callback
      >
        <div className={"field-cell cell-" + type} onDoubleClick={() => moveFigure(id)}>
          <div className={"tile-icon-block direction_" + direction + "_p" + player_num}>{icon}</div>
          <span className="tile-text">{id}</span>
          {closed == 0 && figures != undefined
            ? figures.map((value, key) => {
              let count = figures.length;
              count = value.aboard == 1 ? count - 1 : count;
              value.id = key;
              return <Teonka
                tile_id={id}
                id={key}
                key={key}
                features={value}
                count={count}
                player_num={player_num}
                turn={turn}
                chooseFigure={chooseFigure}
                moveFigure={moveFigure}
              />
          })
            : null
          }
        </div>
      </ClickNHold>
    )
  }
}