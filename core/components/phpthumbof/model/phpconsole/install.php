<?php

include_once('phpconsole.php');

global $phpconsole;
$phpconsole = new Phpconsole();
$phpconsole->set_backtrace_depth(1);

/*
==============================================
USER'S SETTINGS
==============================================
*/

$phpconsole->set_domain('.jgrant.modxcloud.com');  // don't forget to use leading dot, like so: .your-domain.com
$phpconsole->add_user('jason', '1aIX7BQsVyWrkXjYSOWwXKoH1zj63FIAWDkHbwSzg4oyueSi1kefQDYsx7Fecqwy', 'Aw4gl628RnJbJjR6pjGBflqpwj6rIRRGVePo2QavtXngIJWNdv3y0PuKGSA7jG9D'); // you can add more developers, just execute another add_user()




function phpconsole($data_sent, $user = false) {
    global $phpconsole;
    return $phpconsole->send($data_sent, $user);
}

function phpcounter($number = 1, $user = false) {
    global $phpconsole;
    $phpconsole->count($number, $user);
}

function phpconsole_cookie($name) {
    global $phpconsole;
    $phpconsole->set_user_cookie($name);
}

function phpconsole_destroy_cookie($name) {
    global $phpconsole;
    $phpconsole->destroy_user_cookie($name);
}

/*
Shorthand functions for lazy developers (author included)
*/

function p($data_sent, $user = false) {
    global $phpconsole;
    return $phpconsole->send($data_sent, $user);
}

function pc($number = 1, $user = false) {
    global $phpconsole;
    $phpconsole->count($number, $user);
}
