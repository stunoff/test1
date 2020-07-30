<?php

namespace App\Repository;

class Ket extends AbstractRepository
{
    public function getKzOrders($kzStatus = 1)
    {
        $stmt = $this->db->prepare("
            SELECT
                o.id,
                o.phone,
                o.phone2,
                o.full_price,
                o.name,
                o.zip,
                o.region,
                o.address,
                o.d_dost,
                o.deliveryComment,
                o.pp_user_id as web_id,
                o.company_id as pp_id,
                o.dop_complect,
                o.status,
                o.status_ls,
                o.post_number,
                ket_orders.send,
                o.pp_user_id,
                   
                CASE
                    WHEN o.action_pay = 3 THEN 32
                    WHEN o.action_pay = 4 THEN oadd.ket_city
                    ELSE 32
                END AS post_type,
                   
                CASE
                    WHEN 
                        o.action_pay = 4 
                        AND status_ls = 53 
                        AND DATE(d_dost) = CURDATE() 
                    THEN 1
                    ELSE 0
                END AS is_dvd,
                   
                ket_orders.ket_order_id,
                ket_orders.prev_ket_status AS `prev_ket_status`,
                np.id AS product_id,
                nu.login as operator_name
            FROM ket_orders
                LEFT JOIN orders o ON o.id = ket_orders.order_id
                LEFT JOIN orders_additional_data oadd ON o.id = oadd.order_id
                LEFT JOIN new_product np ON np.site_id = o.site_id
                LEFT JOIN new_user nu ON o.final_user = nu.id
            WHERE 
                send IN (:kz_status)
                AND o.status NOT IN (3,12,4,9)
                AND o.status_ls NOT IN (41)
                AND o.dop_complect > ''
                AND o.action_pay IN (3,4)
                AND o.country_id = 248
        ");

        $stmt->bindParam(':kz_status', $kzStatus);

        return $this->fetchAll($stmt);
    }

    public function updateOrder($orderId, $ketOrderId, $send, $statusKz = 0, $payDate = '0000-00-00 00:00:00')
    {
        $stmt = $this->db->prepare('
            UPDATE ket_orders
            SET 
                ket_order_id = :ket_order_id, 
                send = :send, 
                prev_ket_status = :status_kz, 
                pay_date = :pay_date
            WHERE order_id = :order_id
        ');

        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':ket_order_id', $ketOrderId);
        $stmt->bindParam(':send', $send);
        $stmt->bindParam(':status_kz', $statusKz);
        $stmt->bindParam(':pay_date', $payDate);

        return $stmt->execute();
    }

    /**
     * @param $orderId
     * @param $statusKz
     * @param $sendStatus
     * @param $glStatus
     * @param $glStatusLs
     * @param string $message
     * @return bool
     */
    public function writeLog($orderId, $statusKz, $sendStatus, $glStatus, $glStatusLs, $message = '')
    {
        $stmt = $this->db->prepare('
            INSERT INTO ket_status_log (order_id, status_kz, send_status, gl_status, gl_status_ls, message)
            VALUES (:order_id, :status_kz, :send_status, :gl_status, :gl_status_ls, :message)
        ');

        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':status_kz', $statusKz);
        $stmt->bindParam(':send_status', $sendStatus);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':gl_status', $glStatus);
        $stmt->bindParam(':gl_status_ls', $glStatusLs);

        return $this->exec($stmt);
    }
}
