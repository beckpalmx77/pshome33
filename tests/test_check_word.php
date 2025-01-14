<?php

$data = "section";

if (preg_match('(bad|naughty)', $data) === 1) {
    echo " 1 ";
} else if (preg_match('(ok|type)', $data) === 1) {
    echo " 2 ";
} else if (preg_match('(no|section)', $data) === 1) {
    echo " 3 ";
} else if (preg_match('(edge|sql)', $data) === 1) {
    echo " 4 ";
}


