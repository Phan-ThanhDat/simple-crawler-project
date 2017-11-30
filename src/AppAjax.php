<?php

/**
 * Class AppAjax
 */
class AppAjax extends App {

    protected function responseJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function loadDataTable() {
        $pdo = $this->getPDOObject();

//        $stmt = $pdo->prepare('SELECT * FROM ' . Product::TABLE_NAME . ' limit 200'); // Fast development
        $stmt = $pdo->prepare('SELECT * FROM ' . Product::TABLE_NAME);

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->responseJson(['status' => '200', 'data' => $records]);
    }

    public function validateAndUpdateProduct() {
        if(!empty($_POST)) {
            $id = (int) $_POST['id'];
            $isAdd = ($_POST['isAdd'] == 'true');

            if($id) {
                $pdo = $this->getPDOObject();

                $stmt = $pdo->prepare('SELECT quantity FROM ' . Product::TABLE_NAME . ' WHERE id = ?');

                $stmt->execute([$id]);

                $record = $stmt->fetch(PDO::FETCH_ASSOC);
                $currentQuantity = (int) $record['quantity'];

                if($currentQuantity == 0 && !$isAdd) {
                    return $this->responseJson([ 'data' => -1 ]);
                }

                if($isAdd) {
                    $quantity = ++$currentQuantity;
                } else {
                //    $quantity = --$currentQuantity;
                    $quantity = 0;
                }

                if($quantity > 99999) { // Safe number
                    return $this->responseJson([ 'data' => -1 ]);
                }

                $stmt = $pdo->prepare('UPDATE ' . Product::TABLE_NAME . ' SET quantity = ? WHERE id = ?');

                $stmt->execute([$quantity, $id]);

                return $this->responseJson([ 'data' => $quantity ]);

            }
        }

        return $this->responseJson([ 'data' => -1 ]);
    }
}