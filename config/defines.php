<?php

/**
 * Constants use for application
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_NAME', 'simple_crawler');

define('CURRENCY_API_KEY', '9fa0de9694673f526e0d2041c809db20');
define('CURRENCY_EXCHANGE_URL', 'http://www.apilayer.net/api/live?access_key=' . CURRENCY_API_KEY);
// http://www.apilayer.net/api/live?access_key=9fa0de9694673f526e0d2041c809db20

//define('USE_TEST_HTML', true); // For fast development, we will use offline HTML code instead
define('USE_TEST_HTML', false);
//define('USE_TEST_EXCEL_FILE', true); // For fast development, we will use offline Excel file instead
define('USE_TEST_EXCEL_FILE', false);

define('TARGET_BASE_URL', 'https://www.alko.fi');
define('TARGET_HTML_URL', 'https://www.alko.fi/valikoimat-ja-hinnasto/hinnasto');

define('ROOT_DIR', __DIR__ . '/../');
