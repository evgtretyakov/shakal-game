<?php


class ChatHandler
{

    function ConnectToDB()
    {
//        return mysqli_connect("localhost", "shakal_user", "65NTmg1OPLW9akEP", "shakal_bd");
        $servername = "localhost";
        $username   = "shakal_user";
        $password   = "65NTmg1OPLW9akEP";
        $dbname     = "shakal_bd";
        // Create connection
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        if ( ! $conn) {
            $error_msg = 'Ошибка подключения к базе:' . $conn->connect_error;
            $this->Respond([], 11, $error_msg);
        }

        return $conn;
    }

    function createChat($name, $ts, $link) {
        $sql = 'CREATE TABLE `chat_' . $ts . '` 
            (
                `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `name` VARCHAR(30) NOT NULL ,
                `message` VARCHAR(255) NOT NULL
            )';
        if (!mysqli_query($link, $sql)) {
            $error_msg = 'Не удалось создать таблицу поля ' . mysqli_error($link) . ' ';
            $this->Respond([], 25, $error_msg);
        } else {
            $resp = self::createMessage($name, $ts, ' ', $link);
            self::Respond($resp);
        }
        exit;
    }

    function getMessages($ts, $link, $id = 0) {
        $messages = [];
        $new_id = 0;
        $sql = 'SELECT * FROM chat_' . $ts;
        if ($result = mysqli_query($link, $sql)) {
            while($row = mysqli_fetch_array($result)) {
                $new_id = $row['id'];
                $messages[] = [
                    'name' => $row['name'],
                    'message' => $row['message'],
                ];
            }
        } else {
            $this->Respond([], 34, 'Не удалось забрать сообщения. ' . mysqli_error($link));
        }
        $update = $id != $new_id ? 1 : 0;
        $resp = [
            'update' => $update,
            'messages' => $messages,
            'id' => $new_id
        ];
        return $resp;
    }

    function checkChat($obj) {
        $link = self::ConnectToDB();
        $name = $obj->user_name;
        $ts = $obj->ts;
        $id = isset($obj->id) ? $obj->id : 0;
//        $id++;
        $sql ='SELECT MAX(id) FROM chat_' . $ts;
//        $sql = 'SELECT * FROM chat_' . $ts;
        if ($result = mysqli_query($link, $sql)) {
            while($row = mysqli_fetch_array($result)) {
                if ($row[0] == $id) {
                    $resp= [
                        'update' => 0
                    ];
                    self::Respond($resp);
                }
                $resp = self::getMessages($ts, $link, $id);
                self::Respond($resp);
            }
        } else {
            self::createChat($name, $ts, $link);
            $this->Respond([], 34, 'Не удалось забрать сообщения. ' . mysqli_error($link));
        }
        $resp = [
            'update' => 0
        ];
        self::Respond($resp);
    }

    function createMessage($name, $ts, $message, $link, $id = 0) {
        $id++;
        $sql = 'INSERT INTO chat_' . $ts . ' (`name`, `message`) VALUES ("' . $name . '", "' . $message . '")';
//        $sql = 'INSERT INTO chat_1586500919 (`name`, `message`) VALUES ("111", "222")';
        if (mysqli_query($link, $sql)) {
            $resp = self::getMessages($ts, $link, 0);
            $resp['id'] = $id;
            return $resp;
        } else {
            $this->Respond([], 13, 'Не удалось отправить. ' . mysqli_error($link));
        }
    }

    function sendMessage($obj) {
        $link = self::ConnectToDB();
        $name = $obj->user_name;
        $message = $obj->message;
        $ts = $obj->ts;
        $id = isset($obj->id) ? $obj->id : 0;
        $resp = self::createMessage($name, $ts, $message, $link, $id);
        self::Respond($resp);
    }

    function Respond($resp = [], $error = 0, $error_msg = '') {
        $resp['error'] = $error;
        $resp['error_msg'] = $error_msg;
        $response = json_encode($resp);
        header('Content-Length: '.strlen($response));
        header('Content-Type: application/json');
        echo $response;
        exit;
    }
}

$main = new ChatHandler();

$json = file_get_contents('php://input');
if ($json) {
    $obj = json_decode($json);
    switch ($obj->req) {
        case 'checkChat':
            $main->checkChat($obj);
            break;
        case 'sendMessage':
            $main->sendMessage($obj);
            break;
        default:
            $main->Respond([], 33, 'Не найден такой запрос.');
    }
}