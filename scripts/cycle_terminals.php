<?php

chdir(dirname(__FILE__) . '/../');

include_once './config.php';
include_once './lib/loader.php';
include_once './lib/threads.php';

set_time_limit(0);

include_once("./load_settings.php");

include_once(DIR_MODULES . 'terminals/terminals.class.php');

$terminals = new terminals();

$checked_time = 0;

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);

// set all message clear
SQLExec("UPDATE shouts SET SOURCE='' ");
		
// set all terminal as free when restart cycle
$terminals = SQLSelect("SELECT * FROM terminals");
foreach ($terminals as $terminal) {
    sg($terminal['LINKED_OBJECT'] . '.basy', 0);
} 

// get number last message
$number_message = SQLSelectOne("SELECT * FROM shouts ORDER BY ID DESC")['ID'];
$number_message = $number_message + 1;
DebMes('Start terminals cycle');
while (1) {
	// time update cicle of terminal
    if (time() - $checked_time > 10) {
        $checked_time = time();
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
    }
    //время жизни сообщений
    if (time() - $clear_message > 300) {
        $clear_message = time();
        SQLExec("UPDATE shouts SET SOURCE = '' WHERE ADDED < (NOW() - INTERVAL 5 MINUTE)");
    }

	// CHEK next message for terminals ready
    $message = SQLSelectOne("SELECT * FROM shouts WHERE ID='" . $number_message ."'");

	if ($message ) {
	    $number_message = $number_message + 1;
	} else {
		usleep(200000);	
	}
  	// chek all old message and send message to terminals
    $out_terminals = getObjectsByProperty('basy', '==', '0');
	foreach ($out_terminals as $terminals) {
		if (!$terminals) {
			continue;
		}
		$terminal = SQLSelectOne("SELECT * FROM terminals WHERE LINKED_OBJECT = '" . $terminals . "'");
		$old_message = SQLSelectOne("SELECT * FROM shouts WHERE ID <= '" . $number_message ."' AND SOURCE LIKE '%" . $terminal['ID'] . "^%' ORDER BY ID ASC");
	    // esli est soobshenie to puskem ego
		if ($old_message) {
			$old_message['SOURCE'] = str_replace($terminal['ID'] . '^', '', $old_message['SOURCE']);
			SQLUpdate('shouts', $old_message);
			//sg($terminal['LINKED_OBJECT'].'.restoredata',getPlayerStatus($terminal['NAME']));
			sg($terminal['LINKED_OBJECT'] . '.basy', 1);
			send_messageSafe($old_message, $terminal);
		} else {
			// inache vosstanavlivaem vosproizvodimoe
			//setPlayerVolume($terminal['HOST'], $terminal['TERMINAL_VOLUME_LEVEL']);
            //playMedia($message['CACHED_FILENAME'], $terminal['NAME']);
			//seekPlayerPosition($terminal['NAME'], $time = 0);
		}
	}   

    
    if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
        exit;
    }
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
