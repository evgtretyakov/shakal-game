<?php


class UsersController
{

    public static function getUserByName($name, $link) {
        $hash = md5(md5($name));

        $found = false;
        $user = [];

        if ($result = mysqli_query($link, "SELECT * FROM users WHERE `name` = '" . $name . "'")) {
            while($row = mysqli_fetch_array($result)) {
                if ($row['hash'] === $hash) {
                    $found = true;
                    $user = $row;
                }
            }
            if ($found) {
                return $user;
            } else {
                return false;
            }
        } else {
            Handler::Respond([], 12, 'Ошибка поиска в БД' . mysqli_error($link));
        }
    }

    public static function getUserById($id, $link) {
        if ($result = mysqli_query($link, "SELECT * FROM users WHERE `id` = " . $id)) {
            while($row = mysqli_fetch_array($result)) {
                return $row;
            }
        } else {
            $error_msg = 'Пользователь не найден ' . mysqli_error($link);
            Handler::Respond([], 23, $error_msg);
        }
    }

    public static function registerUser($name, $session_id, $link) {
        $hash = md5(md5($name));
        $sql = 'INSERT INTO users (`hash`, `name`, `session_id`) VALUES ("' . $hash . '","' . $name . '","' . $session_id . '")';
        if (mysqli_query($link, $sql)) {
            return mysqli_insert_id($link);
        } else {
            Handler::Respond([], 13, 'Не удалось зарегистрировать' . mysqli_error($link));
        }
    }

    public static function updateSessionId($session_id, $user_id, $link) {
        $sql = 'UPDATE users SET `session_id`="' . $session_id . '" WHERE `id`=' . $user_id;
        if (mysqli_query($link, $sql)) {
            // session set
        } else {
            Handler::Respond([], 27, 'Не удалось вставить сессию. ' . mysqli_error($link));
        }
        return true;
    }

    public static function checkSession($user_id, $session_id, $link) {
        $sql = 'SELECT * FROM users WHERE `id`=' . $user_id . ' LIMIT 1';
        if ($result = mysqli_query($link, $sql)) {
            while($row = mysqli_fetch_array($result)) {
                if ($session_id == $row['session_id']) {
                    return true;
                } else {
                    $error_msg = 'Сессия устарела или не совпадает. ' . mysqli_error($link);
                    Handler::Respond([], 30, $error_msg);
                }
            }
        } else {
            $error_msg = 'Проверка сессии не удалась. ' . mysqli_error($link);
            Handler::Respond([], 29, $error_msg);
        }
        return false;
    }
}