import React, { Component } from 'react';
import { render } from 'react-dom';
import { transitions, positions, Provider as AlertProvider } from 'react-alert'

import AppController from "../assets/src/AppController";
import AlertTemplate from "react-alert-template-basic";

/* globals __webpack_public_path__ */
__webpack_public_path__ = `${window.STATIC_URL}/app/assets/bundle/`;


// optional cofiguration
const options = {
  // you can also just use 'bottom center'
  position: positions.BOTTOM_CENTER,
  timeout: 5000,
  offset: '30px',
  // you can also just use 'scale'
  transition: transitions.SCALE
};


class Myapp extends Component {
  render() {
    return (
      <AlertProvider template={AlertTemplate} {...options}>
        <div className="App">
          <AppController/>
        </div>
      </AlertProvider>
    );
  }
}

render(<Myapp/>, document.getElementById('app'));