<?php

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/AppAjax.php';

$appAjax = new AppAjax();

$appAjax->loadDataTable();