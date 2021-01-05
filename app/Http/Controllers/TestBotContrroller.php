<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestBotContrroller extends Controller
{
    public function index()
    {
        if (!isset($_REQUEST)) {
            return;
        }

//Строка для подтверждения адреса сервера из настроек Callback API
        $confirmationToken = 'ef36bf17';

//Ключ доступа сообщества
        $token = 'testRudnBot';

// Secret key
        $secretKey = 'a519107354d179eedec85221ddcb330c298ffc45632c06a668b253e1958b5de16786215b1bbec21ac3512';

//Получаем и декодируем уведомление
        $data = json_decode(file_get_contents('php://input'));
        dd($data);
// проверяем secretKey
        if(strcmp($data->secret, $secretKey) !== 0 && strcmp($data->type, 'confirmation') !== 0)
            return;

//Проверяем, что находится в поле "type"
        switch ($data->type) {
            //Если это уведомление для подтверждения адреса сервера...
            case 'confirmation':
                //...отправляем строку для подтверждения адреса
                echo $confirmationToken;
                break;

            //Если это уведомление о новом сообщении...
            case 'message_new':
                //...получаем id его автора
                $userId = $data->object->user_id;
                //затем с помощью users.get получаем данные об авторе
                $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&v=5.0"));

                //и извлекаем из ответа его имя
                $user_name = $userInfo->response[0]->first_name;

                //С помощью messages.send и токена сообщества отправляем ответное сообщение
                $request_params = array(
                    'message' => "{$user_name}, ваше сообщение зарегистрировано!<br>".
                        "Мы постараемся ответить в ближайшее время.",
                    'user_id' => $userId,
                    'access_token' => $token,
                    'v' => '5.0'
                );

                $get_params = http_build_query($request_params);

                file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);

                //Возвращаем "ok" серверу Callback API
                echo('ok');

                break;

            // Если это уведомление о вступлении в группу
            case 'group_join':
                //...получаем id нового участника
                $userId = $data->object->user_id;

                //затем с помощью users.get получаем данные об авторе
                $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&v=5.0"));

                //и извлекаем из ответа его имя
                $user_name = $userInfo->response[0]->first_name;

                //С помощью messages.send и токена сообщества отправляем ответное сообщение
                $request_params = array(
                    'message' => "Добро пожаловать в наше сообщество МГТУ им. Баумана ИУ5 2016, {$user_name}!<br>" .
                        "Если у Вас возникнут вопросы, то вы всегда можете обратиться к администраторам сообщества.<br>" .
                        "Их контакты можно найти в соответсвующем разделе группы.<br>" .
                        "Успехов в учёбе!",
                    'user_id' => $userId,
                    'access_token' => $token,
                    'v' => '5.0'
                );

                $get_params = http_build_query($request_params);

                file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);

                //Возвращаем "ok" серверу Callback API
                echo('ok');

                break;
        }
    }
}
