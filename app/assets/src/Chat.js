import React from 'react';
import { Card, ListGroup, ListGroupItem, Button, Form } from 'react-bootstrap';

import CheckChat from './CheckChat.js';
import WelcomeScreen from "./WelcomeScreen";

export default class Chat extends React.Component{

  state = {
    text: '',
    messages: [],
    waiting: true,
    id: 0,
    hidden: true
  };

  getChat = (data) => {
    this.setState({
      messages: data.messages,
      id: data.id
    });
  };

  changeText = (event) => {
    this.setState({text: event.target.value});
  };

  submitForm = (event) => {
    event.preventDefault();
    if (this.state.text != '') {
      let data = {
        req: 'sendMessage',
        user_name: this.props.name,
        ts: this.props.ts,
        message: this.state.text
      };
      fetch("/chat", {
        method: 'POST',
        body: JSON.stringify(data)
      } )
        .then(res => res.json())
        .then(
          (result) => {
            if (result.error == 0) {
              this.setState({
                text: ''
              });
            } else {
              this.props.showError('Ошибка: ' + result.error + '. ' + result.error_msg);
            }
          },
          (error) => {
            this.props.showError('Ошибка! ' + error);
          }
        );
    }
  };

  getMessageList = (messages) => {
    let message_list = [];
    messages.map((message, key) => {
      message_list.push(<React.Fragment key={key}>
        <Card.Subtitle className="mb-2 text-muted chat-name">{message.name}</Card.Subtitle>
        <ListGroupItem className={"chat-text"}>{message.message}</ListGroupItem>
      </React.Fragment>)
    });
    return <ListGroup className="list-group-flush">{message_list}</ListGroup>;
  };

  hideChat = (hidden) => {
    this.setState({ hidden: !hidden})
  };

  render() {

    let { name, ts } = this.props;

    let { hidden, messages, waiting, id } = this.state;

    return (
      <div className={hidden ? "chat-block hidden-chat" : "chat-block"}>
        <Card>
          <Button onClick={() => this.hideChat(hidden)} variant="link" className={"hide-chat"}>{hidden ? "Развернуть" : "Свернуть"}</Button>
          <Card.Body>
            <Card.Title>Чат</Card.Title>
          </Card.Body>
          <Card.Body className={"chat-messages"}>
            {this.getMessageList(messages)}
          </Card.Body>
          <Card.Body className={"chat-send"}>
            <Form onSubmit={this.submitForm}>
              <Form.Control type="text" placeholder="Сообщение..." value={this.state.text} onChange={this.changeText}/><br/>
              <Button variant="primary" type="submit">Отправить</Button>
            </Form>
          </Card.Body>
        </Card>
        <CheckChat
          name={name}
          ts={ts}
          waiting={waiting}
          getChat={this.getChat}
          id={id}
          showError={this.props.showError}
          hidden={hidden}
        />
      </div>
    );
  }
}