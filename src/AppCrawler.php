<?php

/**
 * Class AppCrawler
 * @property PDO $pdoObject
 */
class AppCrawler extends App{

    protected
        $httpClient,
        $usdToEuro,
        $usdToPound;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->httpClient = new \GuzzleHttp\Client();
    }

    /**
     * Get HTML code from source
     * @return bool|string
     */
    protected function getHtmlFromSource() {
        $this->flushMessageToBrowser('Getting HTML code from target URL: ' . TARGET_HTML_URL);

        if( USE_TEST_HTML ) {
            $this->flushMessageToBrowser('Done!');

            return file_get_contents(ROOT_DIR . '/test/target.html');
        }

        $response = $this->httpClient->get(TARGET_HTML_URL);

        $this->flushMessageToBrowser('Done!');

        return $response->getBody()->getContents();
    }

    /**
     * @param $html
     * @return string
     */
    protected function getExcelLinkFromHtml($html) {
        $this->flushMessageToBrowser('Parsing HTML Code to get download link:');

        $domCrawler = new \Symfony\Component\DomCrawler\Crawler($html);

        $cssToXpathConverter = new \Symfony\Component\CssSelector\CssSelectorConverter();

        $crawler = $domCrawler->filterXPath($cssToXpathConverter->toXPath('a.teaser-heading'));

        $this->flushMessageToBrowser('Done!');

        return TARGET_BASE_URL . $crawler->getNode(0)->getAttribute('href');
    }

    /**
     * @param $url
     * @param $tmpExcelFile
     * @return string
     */
    protected function downloadAndGetTmpExcelFilePath($url, $tmpExcelFile) {
        $this->flushMessageToBrowser('Downloading Excel file:');

        if(USE_TEST_EXCEL_FILE) {
            $this->flushMessageToBrowser('Done!');

            return ROOT_DIR . '/test/alkon-hinnasto-tekstitiedostona27.11.2017.small.xls';
        }

        $this->flushMessageToBrowser('Link: ' . $url);

        $response = $this->httpClient->get($url);
        $metaData = stream_get_meta_data($tmpExcelFile);

        fwrite($tmpExcelFile, $response->getBody()->getContents());

        $this->flushMessageToBrowser('Done!');

        return $metaData['uri'];
    }

    /**
     * @param string $excelFilePath
     * @return array
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Exception
     */
    protected function readExcelFile($excelFilePath = '') {
        $this->flushMessageToBrowser('Start to read excel tmp file:');

        $objReader = new PHPExcel_Reader_Excel5();
        $objPHPExcel = $objReader->load($excelFilePath);

        $products = [];

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            foreach ($worksheet->getRowIterator() as $row) {
                if($row->getRowIndex() >= 5) { // Read data from 5th row
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set

                    $product = new Product();

                    /** @var PHPExcel_Cell $cell */
                    foreach ($cellIterator as $cell) {
                        if (!is_null($cell)) {

                            if(isset(Product::COLUMNS_MAP[$cell->getColumn()])) {
                                $property = Product::COLUMNS_MAP[$cell->getColumn()];
                                $product->$property = $cell->getValue();
                            }
                        }
                    }

                    array_push($products, $product);

                }
            }

            break; // Only read first sheet
        }

        $this->flushMessageToBrowser('Done!');

        return $products;
    }

    /**
     *
     * SQL Example:
     * INSERT INTO `products` (`number`, `name`, `manufacturer`, `bottle_size`, `price`, `price_per_liter`, `alcohol`
     * VALUES ('11', 'GGGGZZZfdsafZSSASASASASASASA', 'fdsaf', '324', '4324', '4234', '324324')
     * ON DUPLICATE KEY UPDATE  name = VALUES(name)
     *
     * @param $products
     */
    protected function bulkUpdateProductToDatabase($products) {
        $this->flushMessageToBrowser('Start bulk update product to database:');

        $pdo = $this->getPDOObject();
        $questionMarksTemplate =  '(' . implode(',', array_fill(0, count(Product::COLUMNS_MAP), '?')) . ')';

        $rowPerTransaction = 500; // Block insert to prevent overhead
        $productsChunk = array_chunk($products, $rowPerTransaction); // Block insert to prevent overhead

        // Generate ON DUPLICATE statement
        $onDuplicateStatement = ' ON DUPLICATE KEY UPDATE ';
        $valueArray = [];
        foreach (Product::COLUMNS_MAP as $column) {
            array_push($valueArray, ' ' . $column . '=' . 'VALUES(' . $column . ')');
        }
        $onDuplicateStatement .= implode(', ',$valueArray);

        $pdo->exec('LOCK TABLES ' . Product::TABLE_NAME . ' WRITE' );  // Lock table for safe writing

        // Repeat for each block of products
        foreach ($productsChunk as $productsArray) { // Block insert to prevent overhead
            $this->flushMessageToBrowser('Inserting ' . count($productsArray) . ' products...');

            $questionMarks = array_fill(0, count($productsArray), $questionMarksTemplate);

            $insertValues = [];

            /** @var Product $product */
            foreach($productsArray as $product) {

                $product->bottle_size = (float) str_replace(',', '.', $product->bottle_size);
                $product->price = $this->convertEuroToPound((float) $product->price);
                $product->price_per_liter = $this->convertEuroToPound((float) $product->price_per_liter);

                foreach (Product::COLUMNS_MAP as $index => $property) {
                    array_push($insertValues, $product->$property);
                }
            }

            $pdo->beginTransaction(); // Speed up insert

            $sql = 'INSERT INTO ' . Product::TABLE_NAME . ' (' . implode(',', Product::COLUMNS_MAP ) . ') VALUES ' . implode(',', $questionMarks) . $onDuplicateStatement;

            $stmt = $pdo->prepare($sql);

            try {
                $stmt->execute($insertValues);
            } catch (PDOException $e){
                echo $e->getMessage();
            }

            $pdo->commit();

            $this->flushMessageToBrowser('Done!');
        }

        $pdo->exec('UNLOCK TABLES'); // Unlock locked table
    }

    protected function getCurrencyExchangeRate() {
        $this->flushMessageToBrowser('Fetching currency exchange rate:');

        $response = $this->httpClient->get(CURRENCY_EXCHANGE_URL);
        $data = json_decode($response->getBody()->getContents());

        $this->usdToEuro = $data->quotes->USDEUR;
        $this->usdToPound = $data->quotes->USDGBP;

        $this->flushMessageToBrowser('Done!');
    }

    /**
     * @param $euro
     * @return int
     */
    protected function convertEuroToPound($euro = 1) {
        if(!$this->usdToEuro) {
            $this->getCurrencyExchangeRate();
        }

        return floor(($euro * $this->usdToPound / $this->usdToEuro) * 100) / 100;
    }

    /**
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function doUpdateFromWebsite() {
        $tmpExcelFile = tmpfile();

        $html = $this->getHtmlFromSource();

        $excelLink = $this->getExcelLinkFromHtml($html);

        $tmpExcelFilePath = $this->downloadAndGetTmpExcelFilePath($excelLink, $tmpExcelFile);

        $products = $this->readExcelFile($tmpExcelFilePath);

        $this->bulkUpdateProductToDatabase($products);

        $this->flushMessageToBrowser('Update completed!<br/><a href="/">Click here to go back</a>');
    }

    /**
     * This function will flush message to browser before request finished
     * @param $message
     */
    protected function flushMessageToBrowser($message) {
        if(!headers_sent()) { // Buffering control for NGINX
            header('X-Accel-Buffering: no');
        }

        echo ' - ' . $message . '<br/>';

        flush();
        ob_flush();
    }
}

