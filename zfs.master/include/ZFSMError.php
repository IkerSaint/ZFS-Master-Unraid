<?php

define("ZFSM_ERR_UNKOWN", 1000);
define("ZFSM_ERR_UNKOWN_STR", "Unknown Error");
define("ZFSM_ERR_NOT_IN_CONFIG", 1001);
define("ZFSM_ERR_NOT_IN_CONFIG_STR", "Property not set in the plugin config");
define("ZFSM_ERR_UNABLE_TO_SAVE", 1002);
define("ZFSM_ERR_UNABLE_TO_SAVE_STR", "Unable to save changes");


function resolve_error(int $error_code) {
    if ($error_code < 128):
        return posix_strerror($error_code);
    endif;

    switch ($error_code) {
        case 1001:
            return ZFSM_ERR_NOT_IN_CONFIG;
        case 1002:
            return ZFSM_ERR_UNABLE_TO_SAVE;
    }

    return ZFSM_ERR_UNKOWN;
}

?>