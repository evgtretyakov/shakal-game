import React, { useState } from "react";

import useInterval from "./UseInterval.js";

function CheckGameStarted(props) {
  function checkGameReady() {
    let data = {
      req: "checkGameReady",
      session_id: props.session_id,
      game_id: props.game_id,
      user_id: props.user_id
    };
    fetch("/ajax", {
      method: "POST",
      body: JSON.stringify(data)
    })
      .then(res => res.json())
      .then(
        result => {
          if (result.error == 0) {
            // not error
            if (result.update == 1) {
              // not updated
              if (result.game_started == 1) {
                // game started
                props.startTheGame(result);
              } else {
                // not started
                if (result.player_connected == 1) {
                  props.updatePlayersList(result.players_list);
                }
                // console.log(result.game_started);
              }
            } else {
              // updated
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
    if (props.game_started == 0) {
      // console.log('THE GAME IS NOT STAAARTING');
      checkGameReady();
    }
  }, 2000);

  return null;
}

export default CheckGameStarted;
