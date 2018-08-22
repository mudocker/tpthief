<?php

define('ROOT', dirname(__DIR__)); 

require ROOT."/vendor/autoload.php";

$setting = [
    
    
    
];

$document = FluentDOM::load(
  'Runtime/Html/index.html',
  'text/html',
  [FluentDOM\Loader\Options::ALLOW_FILE => TRUE]
);