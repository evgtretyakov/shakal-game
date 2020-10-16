import React, { useState } from "react";

import useInterval from './useInterval.js';

function CheckChat(props) {

  function CheckChatUpdate() {
    let data = {
      req: 'checkChat',
      user_name: props.name,
      ts: props.ts,
      id: props.id
    };
    fetch("/chat", {
      method: 'POST',
      body: JSON.stringify(data)
    } )
      .then(res => res.json())
      .then(
        (result) => {
          if (result.error == 0) {
            if (result.update == 1) {
              props.getChat(result);
            } else {
              // console.log(result.updated);
            }
          } else {
            this.props.showError('Ошибка: ' + result.error + '. ' + result.error_msg);
          }
        },
        (error) => {
          this.props.showError('Ошибка! ' + error);
        }
      );
  }

  useInterval(() => {
    // Your custom logic here
    if (!props.hidden) {
      // console.log('There is no updates');
      CheckChatUpdate();
    }
  }, 2000);

  return null;

}

export default CheckChat;
