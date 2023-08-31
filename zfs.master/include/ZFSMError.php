<?php

define("ZFSM_ERR_UNKOWN", "Unknown Error");

function resolve_error(int $error_code) {
    if ($error_code < 128):
        return posix_strerror($error_code);
    endif;

    switch ($error_code) {
    }

    return ZFSM_ERR_UNKOWN;
}

?>