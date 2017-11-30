# Simple php crawler 

### Deployment:
- Step 1: Create database `simple_crawler` and import the table from `database/create_database.sql`
- Step 2: Create Virtual Host point to `public` folder
- Step 3: Configure `database` connection and Currency `API key` in `config/defines.php`

### Functions:
- `config/defines.php`: Config for application
- `src/bootstrap.php`: Bootstrap for application, include Composer's autoload
- `public/crawler.php`: Script of part 1, access target URL, get data and update to database
- `public/ajax_load.php`: Script for part 2, to load data from database
- `public/ajax_update.php`: Script for part 2, update quantity for pushing button Add/Update
- `public/index.html`: HTML, Javascript code for product table

### References:

##### Libraries:
- `guzzlehttp/guzzle`: Used to make HTTP request easily
- `symfony/dom-crawler`: Used to parse and query DOM element
- `symfony/css-selector`: Used to converts CSS selectors to XPath expressions 
- `phpoffice/phpexcel`: Used to read Excel file


##### Links:
- https://currencylayer.com/quickstart
- https://symfony.com/doc/current/components/css_selector.html
- https://symfony.com/doc/current/components/dom_crawler.html
- https://github.com/PHPOffice/PHPExcel/tree/1.8/Examples
- https://dev.mysql.com/doc/refman/5.7/en/insert-on-duplicate.html (Insert on duplicate)
- https://stackoverflow.com/questions/1176352/pdo-prepared-inserts-multiple-rows-in-single-query (Insert multi-rows using a single query)
