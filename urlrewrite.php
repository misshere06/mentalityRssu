<?php
$arUrlRewrite=array (
  0 => 
  array (
    'CONDITION' => '#^/news/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/news/index.php',
    'SORT' => 100,
  ),
  1 => 
  array (
    'CONDITION' => '#^/psychology/booking/([0-9]+)/#',
    'RULE' => 'PSYCHOLOGIST_ID=$1',
    'PATH' => '/psychology/booking/index.php',
    'SORT' => 200,
  ),
);
