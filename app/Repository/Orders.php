<?php

namespace App\Repository;

use App\Service\Logger;

class Orders extends AbstractRepository
{
    public function updateOrderStatus($orderId, $status = null, $statusLs = null)
    {
        if (empty($orderId)) {
            Logger::writeLog(
                'orderId is empty',
                null,
                __CLASS__,
                __LINE__
            );
            return false;
        }

        if (!isset($status) && !isset($statusLs)) {
            Logger::writeLog(
                'status and status ls not set',
                null,
                __CLASS__,
                __LINE__
            );
            return false;
        }

        $query = sprintf('
            UPDATE orders o
            SET 
                %s%s
                %s
            WHERE o.id = :order_id
        ',
        isset($status) ? 'o.status = :status_id' : '',
        isset($status) && isset($statusLs) ? ',' : '',
        isset($statusLs) ? 'o.status_ls = :status_ls' : ''
        );

        $stmt = $this->db->prepare($query);
        isset($status) ? $stmt->bindParam(':status_id', $status) : null;
        isset($statusLs) ? $stmt->bindParam(':status_ls', $statusLs) : null;
        $stmt->bindParam(':order_id', $orderId);

        return $this->exec($stmt);
    }

    public function exportPage($dateStart, $dateEnd, $actionPay)
    {
        $actionPay = !empty($actionPay) ? implode(',', $actionPay) : null;

        $query = sprintf('
            SELECT o.post_date, o.id, ns.name, o.full_price, pp_user_id, nu.login, d.name as d_name, 
                   o.d_dost, o.phone, o.name as client_name
            FROM orders o 
                INNER JOIN new_user nu ON o.final_user = nu.id
                INNER JOIN new_status ns ON o.status = ns.id
                INNER JOIN new_dostavka d ON  o.action_pay = d.id
            WHERE nu.user_group = 28
            %s 
            %s 
            %s
            ORDER BY o.post_date',
            isset($dateStart) ? 'AND DATE(o.post_date) >= :dateStart' : '',
            isset($dateEnd) ? 'AND DATE(o.post_date) <= :dateEnd' : '',
            !empty($actionPay) ? "AND o.action_pay IN ($actionPay)" : ''
        );
        
        $stmt = $this->db->prepare($query);
        isset($dateStart) ? $stmt->bindParam(':dateStart', $dateStart) : null;
        isset($dateEnd) ? $stmt->bindParam(':dateEnd', $dateEnd) : null;

        return $this->fetchAll($stmt);
    }

    public function exportPageStats($dateStart, $dateEnd, $actionPay)
    {
        $actionPay = !empty($actionPay) ? implode(',', $actionPay) : null;

        $query = sprintf('
            SELECT 
                SUM(CASE WHEN o.status = 1 THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN o.status = 3 THEN 1 ELSE 0 END) as canceled,
                SUM(CASE WHEN o.status = 0 THEN 1 ELSE 0 END) as send,
                SUM(CASE WHEN o.status = 4 THEN 1 ELSE 0 END) as payed,
                SUM(CASE WHEN o.status = 9 THEN 1 ELSE 0 END) as returned,
                SUM(CASE WHEN o.status = 12 THEN 1 ELSE 0 END) as incorrect
            FROM orders o 
                INNER JOIN new_user nu ON o.final_user = nu.id
                INNER JOIN new_status ns ON o.status = ns.id
                INNER JOIN new_dostavka d ON  o.action_pay = d.id
            WHERE nu.user_group = 28
            %s 
            %s 
            %s',
            isset($dateStart) ? 'AND DATE(o.post_date) >= :dateStart' : '',
            isset($dateEnd) ? 'AND DATE(o.post_date) <= :dateEnd' : '',
            !empty($actionPay) ? "AND o.action_pay IN ($actionPay)" : ''
        );

        $stmt = $this->db->prepare($query);
        isset($dateStart) ? $stmt->bindParam(':dateStart', $dateStart) : null;
        isset($dateEnd) ? $stmt->bindParam(':dateEnd', $dateEnd) : null;

        return $this->fetchOne($stmt);
    }
}
