<?php

// Рабочее пространство имен

namespace is;

use is\Helpers\System;

// Базовые константы

if (!defined('isENGINE')) { define('isENGINE', microtime(true)); }
if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }
if (!defined('DP')) { define('DP', '..' . DIRECTORY_SEPARATOR); }
if (!defined('DR')) { define('DR', realpath(__DIR__ . DS . DP . DP . DP) . DS); }

// Подключение классов

require_once __DIR__ . DS . 'helpers' . DS . 'system.php';

// helpers
System::include('helpers:prepare');
System::include('helpers:strings');
System::include('helpers:objects');
System::include('helpers:match');
System::include('helpers:parser');
System::include('helpers:sessions');
System::include('helpers:local');

// interfaces

// traits

// parents
System::include('parents:data');
System::include('parents:entry');
System::include('parents:collection');
System::include('parents:catalog');
System::include('parents:singleton');
System::include('parents:constants');
System::include('parents:globals');
System::include('parents:path');
System::include('parents:local');

// constants
System::include('model:constants:config');

// globals
System::include('model:globals:session');
System::include('model:globals:uri');

// data
System::include('model:data:localdata');

?>