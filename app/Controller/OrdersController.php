<?php


namespace App\Controller;


use App\Repository\ActionPay;
use App\Repository\Orders;

class OrdersController extends AbstractController
{
    public function indexAction() {}

    public function exportOrdersPage()
    {
        $dateStart = $this->request->getQueryKey('date_start', date("Y-m-d"));
        $dateEnd = $this->request->getQueryKey('date_end', date("Y-m-d"));
        $actionPay = $this->request->getQueryKey('action_pay');
        $actionPayRepo = new ActionPay();
        $actionPayOptions = $actionPayRepo->getByIds(array(3,4));
        $ordersRepo = new Orders();
        $orders = $ordersRepo->exportPage($dateStart, $dateEnd, $actionPay);
        $stats = $ordersRepo->exportPageStats($dateStart, $dateEnd, $actionPay);
        
        echo $this->render->render('export-orders-page/index.html.twig', array(
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'action_pay_options' => $actionPayOptions,
            'actionPay' => $actionPay,
            'orders' => $orders,
            'stats' => $stats,
        ));
    }
}
