<?php
// http://89.218.86.178/api/doc.txt
require_once __DIR__ . '/baseCron.php';

use App\Models\Ket as KetModel;
use App\Repository\ApiCredentials;
use App\Repository\Ket;
use App\Repository\Products;
use App\Service\Curl;
use App\Service\Logger;

$ApiCredentials = new ApiCredentials();
$curl = new Curl();
$ketRepo = new Ket();
$productsRepo = new Products();

$credentials = $ApiCredentials->getCredentialsByName('ket.kz');
$products = $productsRepo->getProductList();
$orders = $ketRepo->getKzOrders(KetModel::ORDER_UPDATE);

batch(function ($batchOrders) use ($curl, $credentials, $products, $ketRepo) {
    try {
        $data = array();
        $ordersOldInfo = array();
        foreach ($batchOrders as $order) {
            $data[] = array('id' => $order['ket_order_id']);
            $ordersOldInfo[$order['ket_order_id']] = $order;
        }

        $data = json_encode($data);
        $hash = KetModel::generateHash($data, $credentials['login']);
        $curl->setUrl(
            KetModel::ORDER_STATUS_URL
            . "uid={$credentials['login']}"
            . "&s={$credentials['password']}"
            . "&hash=$hash"
            . "&" . KetModel::getStatusFieldsAsUrlQuery()
        );
        $curl->setData($data);
        $result = json_decode($curl->sendRequest(), true);
        
        if (isset($result['success']) && $result['success'] == false) {
            throw new Exception("Кет: Ошибка при получении статусов. {$result['success']}");
        }

        foreach ($result as $ketOrderId => $ketData) {
            $orderId = $ordersOldInfo[$ketOrderId]['id'];
            $orderFullPrice = $ketData['total_price'];
            $orderDeliveryDate = $ketData['date_delivery'];
            $orderPayDate = $ketData['return_date'];
            
            $postNumber = $ketData['kz_code'];
            $newDopComplect = KetModel::generateGetLidsOfferString($ketData['offer'], $products);
            $orderDopComplect = $newDopComplect['dop_complect'];
            $orderCdCount = $newDopComplect['cd_count'];
            
            $orderStatus = $ordersOldInfo[$ketOrderId]['status'];
            $orderStatusLs = $ordersOldInfo[$ketOrderId]['status_ls'];
            $ketSendStatus = $ketData['send_status'];
            $ketStatusKz = $ketData['status_kz'];

            if (array_key_exists($ketSendStatus, KetModel::$finalCourierStatusMap)) {
                if (isset(KetModel::$finalCourierStatusMap[$ketSendStatus]['status'])) {
                    $orderStatus = KetModel::$finalCourierStatusMap[$ketSendStatus]['status'];
                }
                if (isset(KetModel::$finalCourierStatusMap[$ketSendStatus]['status_ls'])) {
                    $orderStatusLs = KetModel::$finalCourierStatusMap[$ketSendStatus]['status_ls'];
                }
            } else {
                if (isset(KetModel::$courierStatusMap[$ketStatusKz]['status'])) {
                    $orderStatus = KetModel::$courierStatusMap[$ketStatusKz]['status'];
                }
                if (isset(KetModel::$courierStatusMap[$ketStatusKz]['status_ls'])) {
                    $orderStatusLs = KetModel::$courierStatusMap[$ketStatusKz]['status_ls'];
                }
            }

            $ketRepo->updateOrder($orderId, $ketOrderId, KetModel::ORDER_UPDATE, $ketStatusKz, $orderPayDate);
            $ketRepo->writeLog($orderId, $ketStatusKz, $ketSendStatus, $orderStatus, $orderStatusLs);
        }
    } catch (Exception $e) {
        Logger::writeLog(
            $e->getMessage(),
            null,
            __FILE__,
            __LINE__
        );
    }
}, $orders);
