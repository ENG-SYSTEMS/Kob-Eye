<?php
$vars['filters'] = Sys::getData('Reservation','Genre/FrontFilter=1',0,20,'ASC','Ordre');
$vars['allfilters'] = Sys::getData('Reservation','Genre',0,200,'ASC','Ordre');
$vars['search'] = $_GET['search'];
$vars['date'] = $_GET['date'];
$vars['genre'] = $_GET['genre'];