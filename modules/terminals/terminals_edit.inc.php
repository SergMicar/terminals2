<?php

/*
* @version 0.3 (auto-set)
*/

if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}

$table_name = 'terminals';

// add class and properties
addClass('Terminals', 'SDevices');
addClassProperty('Terminals', 'name');
addClassProperty('Terminals', 'media_vol_level');
addClassProperty('Terminals', 'message_vol_level');

$rec = getTerminalByID($id);

if ($rec['CANPLAY'] == '') {
        $rec['CANPLAY'] = 1;
}
if ($rec['PLAYER_TYPE'] == '') {
        $rec['PLAYER_TYPE'] = 'mainterm';
}


if ($this->mode == 'update') {
    $ok = 1;

    //updating 'NAME' (varchar, required)
    $rec['NAME'] = gr('name', 'trim');
    $rec['NAME'] = str_replace(' ', '', $rec['NAME']);
    if ($rec['NAME'] == '') {
        $out['ERR_NAME'] = 1;
        $ok = 0;
    }

    //updating 'TITLE' (varchar, required)
    $rec['TITLE'] = gr('title');
    if ($rec['TITLE'] == '') {
        $out['ERR_TITLE'] = 1;
        $ok = 0;
    }

    $rec['CANPLAY'] = gr('canplay', 'int');
    $rec['CANTTS'] = gr('cantts', 'int');

    $rec['MIN_MSG_LEVEL'] = gr('min_msg_level');

    //$rec['MAJORDROID_API'] = gr('majordroid_api', 'int');
    
    $rec['TTS_TYPE'] = gr('tts_type');
    $rec['PLAYER_TYPE'] = gr('player_type');
    $rec['PLAYER_PORT'] = gr('player_port');
    $rec['PLAYER_USERNAME'] = gr('player_username');
    $rec['PLAYER_PASSWORD'] = gr('player_password');
    $rec['LINKED_OBJECT'] = gr('linked_object');
    if ($rec['LINKED_OBJECT'] == '') {
		$objects = getObjectsByClass('Terminals');
		$maxterminal = max($objects)['TITLE'];
        $maxnomber = (str_replace("terminal", "", $maxterminal))+1;
        addClassObject('Terminals', 'terminal'.$maxnomber);
        $rec['LINKED_OBJECT'] = 'terminal'.$maxnomber;
    }
    $rec['PLAYER_CONTROL_ADDRESS'] = gr('player_control_address');

    $rec['HOST'] = gr('host');
    if (!$rec['HOST']) {
        $out['ERR_HOST'] = 1;
        $ok = 0;
    }

    //UPDATING RECORD
    if ($ok) {
        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        $out['OK'] = 1;
    } else {
        $out['ERR'] = 1;
    }
}

if (is_array($rec)) {
    foreach ($rec as $k => $v) {
        if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
        }
    }
}
outHash($rec, $out);

$out['TTS_ADDONS'] = array();
if (is_dir(DIR_MODULES . 'terminals/tts')) {
    $addons = scandir(DIR_MODULES . 'terminals/tts');
    if (is_array($addons)) {
        foreach ($addons as $addon_file) {
            $addon_file = DIR_MODULES . 'terminals/tts/' . $addon_file;
            if (is_file($addon_file)) {
                if (strtolower(substr($addon_file, -10)) == '.addon.php') {
                    $addon_name = basename($addon_file, '.addon.php');
                    $out['TTS_ADDONS'][] = array('NAME'=>$addon_name);
                }
            }
        }
    }
}

if (is_dir(DIR_MODULES . 'app_player/addons')) {
    include_once(DIR_MODULES . 'app_player/addons.php');
    $addons = scandir(DIR_MODULES . 'app_player/addons');
    if (is_array($addons)) {
        foreach ($addons as $addon_file) {
            $addon_file = DIR_MODULES . 'app_player/addons/' . $addon_file;
            if (is_file($addon_file)) {
                if (strtolower(substr($addon_file, -10)) == '.addon.php') {
                    $addon_name = basename($addon_file, '.addon.php');
                    include_once($addon_file);
                    if (class_exists($addon_name)) {
                        if (is_subclass_of($addon_name, 'app_player_addon', TRUE)) {
                            if ($player = new $addon_name(NULL)) {
                                // Get player features
                                $features = array();
                                $reflection = new ReflectionClass($player);
                                foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                                    if ($method->getDeclaringClass()->getName() == $reflection->getName()) {
                                        $method_name = $method->getName();
                                        if (substr($method_name, 0, 2) != '__' and !in_array($method_name, array('destroy', 'command'))) {
                                            $features[] = $method_name;
                                        }
                                    }
                                }
                                if (count($features)) {
                                    $player->description .= '<p><b>' . LANG_FEATURES_SUPPORTED . ':</b> ' . implode(', ', $features) . '.</p>';
                                } else {
                                    $player->description .= '<p style="color: #b94a48;"><b>' . LANG_NO_FEATURES_WARNING . '</b></p>';
                                }
                                // Results
                                $out['PLAYER_ADDONS'][] = array(
                                    'TITLE' => $player->title,
                                    'VALUE' => $addon_name,
                                    'DESCRIPTION' => $player->description,
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}

?>
