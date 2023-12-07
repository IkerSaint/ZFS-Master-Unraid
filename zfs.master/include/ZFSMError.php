<?php

define("ZFSM_ERR_UNKOWN", "Unknown Error");
define("ZFSM_ERR_UNABLE_TO_SAVE", "Unable to save changes");

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