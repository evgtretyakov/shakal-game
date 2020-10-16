import React from 'react';
import { Button } from "react-bootstrap";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faTimes } from "@fortawesome/free-solid-svg-icons";

export default class LogOutButton extends React.Component {
  render() {

    let { logOut } = this.props;

    return(
      <Button
        className="header-logout"
        variant="outline-danger"
        size="lg"
        block
        onClick={logOut}
      >
        <FontAwesomeIcon icon={faTimes} />
      </Button>
    )
  }
}