<?php

require __DIR__ . '/../src/bootstrap.php';

require __DIR__ . '/../src/AppCrawler.php';

$appCrawler = new AppCrawler();

$appCrawler->doUpdateFromWebsite();