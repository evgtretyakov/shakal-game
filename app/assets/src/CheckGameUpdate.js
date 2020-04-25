import React, { useState } from "react";

import useInterval from "./UseInterval.js";

function CheckGameUpdate(props) {
  function checkGameUpdate() {
    let data = {
      req: "checkUpdate",
      game_id: props.game_id,
      player_num: props.player_num
    };
    fetch("/ajax", {
      method: "POST",
      body: JSON.stringify(data)
    })
      .then(res => res.json())
      .then(
        result => {
          if (result.error == 0) {
            if (result.updated == 0) {
              props.getUpdate(result);
            } else {
              // console.log(result.updated);
            }
          } else {
            props.showError(
              "Ошибка: " + result.error + ". " + result.error_msg
            );
          }
        },
        error => {
          props.showError("Ошибка! " + error);
        }
      );
  }

  useInterval(() => {
    // Your custom logic here
    if (props.waiting) {
      // console.log('There is no updates');
      checkGameUpdate();
    }
  }, 2000);

  return null;
}

export default CheckGameUpdate;
