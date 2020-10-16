import React from 'react';

import { noop } from "./trigger-double-click/utils";
import pleaseStopTriggeringClicksOnDoubleClick from "./trigger-double-click/please-stop-triggering-clicks-on-double-click.js";

const ClickableBox = ({ type, className, onClick, onDoubleClick }) => (
  <div className={className}
    onClick={onClick}
    onDoubleClick={onDoubleClick}
  >
    {type != "ship"
      ? <figure className="figure-ball"></figure>
      : null
    }
  </div>
);

ClickableBox.defaultProps = {
  onClick: noop,
  onDoubleClick: noop,
};

const EnhancedClickableBox = pleaseStopTriggeringClicksOnDoubleClick(ClickableBox);

// const DoubleClick = () => (
//   <EnhancedClickableBox
//     onClick={() => console.log("on click")}
//     onDoubleClick={() => console.log("on double click")}
//   />
// );

export default class Teonka extends React.Component{

  stopIt = (event) => {
    event.preventDefault();
  };

  render() {

    let { chooseFigure, moveFigure, tile_id, features, id, count, player_num, turn } = this.props;

    let class_name = 'tile_figure tile_' + features.type + ' ' + features.type + '_' + features.p_num;
    class_name += features.type == 'ship' ? '' : ' figures_count_' + count + ' order_' + (features.aboard == 1 ? id : (id + 1));
    class_name += features.active ? ' active' : '';
    class_name += features.disabled == 1 ? ' disabled' : '';

    return(
      <EnhancedClickableBox
        type={features.type}
        className={class_name}
        onClick={() => chooseFigure(features, player_num, turn)}
        onDoubleClick={() => this.stopIt}
      />
    )
  }
}