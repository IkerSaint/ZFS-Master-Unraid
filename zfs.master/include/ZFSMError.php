<?php

# Special case for Rsync
define("ZFSM_ERR_UNKOWN", 1000);
define("ZFSM_ERR_UNKOWN_STR", "Unknown Error");
define("ZFSM_ERR_NOT_IN_CONFIG", 1001);
define("ZFSM_ERR_NOT_IN_CONFIG_STR", "Property not set in plugin config");
define("ZFSM_ERR_UNABLE_TO_SAVE", 1002);
define("ZFSM_ERR_UNABLE_TO_SAVE_STR", "Unable to save changes");
define("ZFSM_ERR_ALREADY_SET_IN_CONFIG", 1003);
define("ZFSM_ERR_ALREADY_SET_IN_CONFIG_STR", "Property already set in plugin config");
define("ZFSM_ERR_UNABLE_TO_CREATE_PROC", 1004);
define("ZFSM_ERR_UNABLE_TO_CREATE_PROC_STR", "Unable to create the process");


function resolve_error(int $error_code) {
    if ($error_code < 128):
        return posix_strerror($error_code);
    endif;

    $ret = '';

    switch ($error_code) {
        case ZFSM_ERR_NOT_IN_CONFIG:
            $ret = ZFSM_ERR_NOT_IN_CONFIG_STR;
            break;
        case ZFSM_ERR_UNABLE_TO_SAVE:
            $ret = ZFSM_ERR_UNABLE_TO_SAVE_STR;
            break;
        case ZFSM_ERR_ALREADY_SET_IN_CONFIG:
            $ret = ZFSM_ERR_ALREADY_SET_IN_CONFIG_STR;
            break;
        case ZFSM_ERR_UNABLE_TO_CREATE_PROC:
            $ret = ZFSM_ERR_UNABLE_TO_CREATE_PROC_STR;
            break;
        default:
            $ret = ZFSM_ERR_UNKNOWN_STR;
            break;
    }

    return $ret;
}

?>