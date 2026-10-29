<?php
if (array_key_exists('my_chat_member',$request)){
    $Data['chat']['id'] =  $request['my_chat_member']['chat']['id'];
    $Data['chat']['title'] =  $request['my_chat_member']['chat']['title'];
}else{
    $Type = $request['message']['chat']['type'];
    if ($Type == 'private'){

        $Data['form']['id'] = $request['message']['from']['id'];
        $Data['form']['first_name'] = $request['message']['from']['first_name'];
        $Data['form']['last_name'] = $request['message']['from']['last_name'];
        $Data['form']['username'] = $request['message']['from']['username'];

        $Data['chat']['id'] = $request['message']['chat']['id'];
        $Data['chat']['first_name'] = $request['message']['chat']['first_name'];
        $Data['chat']['last_name'] = $request['message']['chat']['last_name'];
        $Data['chat']['username'] = $request['message']['chat']['username'];
    }
    elseif ($Type == 'supergroup'){
        $Data['form']['id'] = $request['message']['from']['id'];
        $Data['form']['first_name'] = $request['message']['from']['first_name'];
        $Data['form']['last_name'] = $request['message']['from']['last_name'];
        $Data['form']['username'] = $request['message']['from']['username'];

        $Data['chat']['id'] =  $request['message']['chat']['id'];
        $Data['chat']['title'] =  $request['message']['chat']['title'];
    }
    $Data['Text'] =  $request['message']['text'];
}
//$userTelegramId = $request['message']['from']['id'];

