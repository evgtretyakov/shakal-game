import React, { Component } from 'react';
import { Form, Button } from 'react-bootstrap';

export default class LoginForm extends Component {

  state = {
    name: this.props.name ? this.props.name : '',
  };

  onChange = (event) => {
    this.setState({ name: event.target.value})
  };

  onSubmit = (event) => {
    event.preventDefault();
    this.props.handleLoginSubmit(this.state.name);
  };

  render() {

    let cookieName = this.state.name;

    let { loading } = this.props;

    return (
      <Form onSubmit={this.onSubmit}>
        <Form.Group controlId="formBasicEmail">
          <Form.Control
            type="text"
            placeholder="Имя..."
            value={cookieName}
            onChange={(e) => this.onChange(e)}
            required
            maxLength="30"
          />
        </Form.Group>
        <Button variant="primary" type="submit" size="lg" block disabled={loading}>
          Войти
        </Button>
      </Form>
    );
  }
}