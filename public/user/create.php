<?php
include '../../private/initialize.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    h(HTTP );
   exit;
}