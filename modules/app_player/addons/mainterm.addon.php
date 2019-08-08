<?php

/*
    Addon Main terminal for app_player
*/

class mainterm extends app_player_addon {

    // Private properties
    private $address;
    
    // Constructor
    function __construct($terminal) {
        $this->title = 'Основной терминал Мажордомо';
        $this->description = 'Описание: Тип терминала для воспроизведения сообщений на локальном сервере.';
        
        $this->terminal = $terminal;
        $this->reset_properties();
        
    }

    // Get player status
    function status() {
        $this->reset_properties();
        // Defaults
        $track_id = -1;
        $length   = 0;
        $time     = 0;
        $state    = 'unknown';
        $volume   = 0;
        $random   = FALSE;
        $loop     = FALSE;
        $repeat   = FALSE;
 
        return $this->success;
    }
    
    // Playlist: Get
    function pl_get() {
        $this->success = FALSE;
        $this->message = 'Command execution error!';
        $track_id      = -1;
        $name          = 'unknow';
        $curren_url    = '';
     
        return $this->success;
    }
	
    // Say
    function say_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
		DebMes($message['FILE_LINK']);
		//если нету ссылки то возвращаем назад
		if (!$message['FILE_LINK']) {
            $this->success = FALSE;
            $this->message = 'Command execution error! Not found link';
			return $this->success;
		}
        $this->reset_properties();
        if($message['FILE_LINK']) {
            if(file_exists($message['FILE_LINK'])) {
                if (IsWindowsOS()){
                    safe_exec(DOC_ROOT . '/rc/madplay.exe ' . $message['FILE_LINK']);
                } else {
                    safe_exec('mplayer ' . $message['FILE_LINK'] . " >/dev/null 2>&1");
                }
                sleep ($message['TIME_MESSAGE']);
                $rec = SQLSelectOne("SELECT * FROM shouts WHERE ID = '".$message['ID']."'");
                $rec['SOURCE'] = str_replace($terminal['ID'] . '^', '', $message['SOURCE']);
                SQLUpdate('shouts', $rec);
                sg($terminal['LINKED_OBJECT'].'.BASY',0);
				
                $this->success = TRUE;
                $this->message = 'OK';
            } else {
                $this->success = FALSE;
                $this->message = 'Command execution error!';
            }
        } else {
            $this->success = FALSE;
            $this->message = 'Input is missing!';
        }
        return $this->message;
    }
}
?>