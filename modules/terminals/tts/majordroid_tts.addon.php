<?php

class majordroid_tts extends tts_addon
{
    
    function __construct($terminal)
    {
        $this->title       = "MajorDroid";
        $this->setting     = json_decode($this->terminal['TTS_SETING'], true);
        $this->description = '<b>Описание:</b>&nbsp; Используется на устройствах которые поддерживаают MajorDroid API.<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;service_ping (пингование устройства проводится проверкой состояния сервиса).<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 7999 (если по умолчанию, можно не указывать).<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;say(), sayTo(), sayReply(), ask().';

	            parent::__construct($terminal);
        if (!$this->terminal['HOST']) return false;
	    $this->port = empty($this->setting['TTS_PORT']) ? 7999 : $this->setting['TTS_PORT'];
        register_shutdown_function("catchTimeoutTerminals");
    }
    
    function say_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        $this->sendMajorDroidCommand('tts:' . $message['MESSAGE']);
		return true;
    }
    
    function ask($phrase, $level = 0)
    {
        $this->sendMajorDroidCommand('ask:' . $phrase);
		return true;
    }
    
    function set_volume($volume = 0)
    {
        $this->sendMajorDroidCommand('volume:' . $volume);
		return true;
    }
    
    function stop()
    {
        $this->sendMajorDroidCommand('pause');
		return true;
    }
    
    private function sendMajorDroidCommand($cmd)
    {
        if ($this->terminal['HOST']) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket === false) {
                return false;
            }
            $result = socket_connect($socket, $this->terminal['HOST'], $this->port);
            if ($result === false) {
                return false;
            }
            $result = socket_write($socket, $cmd, strlen($cmd));
            usleep(500000);
            socket_close($socket);
            return $result;
        }
    }
    
    // Get terminal status
    function terminal_status()
    {
        // Defaults
        $listening_keyphrase = -1;
        $volume_media        = -1;
        $volume_ring         = -1;
        $volume_alarm        = -1;
        $volume_notification = -1;
        $brightness_auto     = -1;
        $recognition         = -1;
        $fullscreen          = -1;
        $brightness          = -1;
        $display_state       = -1;
        $battery             = -1;
        
        $out_data = array(
            'listening_keyphrase' => (string) strtolower($listening_keyphrase), // ключевое слово терминал для  начала распознавания (-1 - не поддерживается терминалом)
            'volume_media' => (int) $volume_media, // громкость медиа на терминале (-1 - не поддерживается терминалом)
            'volume_ring' => (int) $volume_ring, // громкость звонка к пользователям на терминале (-1 - не поддерживается терминалом)
            'volume_alarm' => (int) $volume_alarm, // громкость аварийных сообщений на терминале (-1 - не поддерживается терминалом)
            'volume_notification' => (int) $volume_notification, // громкость простых сообщений на терминале (-1 - не поддерживается терминалом)
            'brightness_auto' => (int) $brightness_auto, // автояркость включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
            'recognition' => (int) $recognition, // распознавание на терминале включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
            'fullscreen' => (int) $recognition, // полноекранный режим на терминале включена или выключена 1 или 0 (-1 - не поддерживается терминалом)
            'brightness' => (int) $brightness, // яркость екрана (-1 - не поддерживается терминалом)
            'battery' => (int) $battery, // заряд акумулятора терминала в процентах (-1 - не поддерживается терминалом)
            'display_state' => (int) $display_state // 1, 0  - состояние дисплея (-1 - не поддерживается терминалом)
        );
        
        // удаляем из массива пустые данные
        foreach ($out_data as $key => $value) {
            if ($value == '-1')
                unset($out_data[$key]);
            ;
        }
        return $out_data;
    }
}
