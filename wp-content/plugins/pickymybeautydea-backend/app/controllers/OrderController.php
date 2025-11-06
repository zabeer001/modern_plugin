<?php
if (!defined('ABSPATH')) exit;
require_once __DIR__ . '/../helpers/Utils.php';
require_once __DIR__ . '/../helpers/RestHelper.php';

class OrderController
{

    /* ============================
     * Helpers (Shipping)
     * ============================ */

    private function sanitizeShippingInput($payload)
    {
        if (!is_array($payload)) return null;
        return [
            'email'    => isset($payload['email'])    ? sanitize_text_field($payload['email'])    : null,
            'phone'    => isset($payload['phone'])    ? sanitize_text_field($payload['phone'])    : null,
            'name'     => isset($payload['name'])     ? sanitize_text_field($payload['name'])     : null,
            'country'  => isset($payload['country'])  ? sanitize_text_field($payload['country'])  : null,
            'city'     => isset($payload['city'])     ? sanitize_text_field($payload['city'])     : null,
            'district' => isset($payload['district']) ? sanitize_text_field($payload['district']) : null,
            'zip_code' => isset($payload['zip_code']) ? sanitize_text_field($payload['zip_code']) : null,
        ];
    }

    private function insertShipping(array $shipping)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'kib_shipping_addresses';

        $ok = $wpdb->insert($table, [
            'email'      => $shipping['email'],
            'phone'      => $shipping['phone'],
            'name'       => $shipping['name'],
            'country'    => $shipping['country'],
            'city'       => $shipping['city'],
            'district'   => $shipping['district'],
            'zip_code'   => $shipping['zip_code'],
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ], ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);

        if (!$ok) return new WP_Error('kib_shipping_create_failed', 'Failed to create shipping address', ['status' => 500]);
        return (int)$wpdb->insert_id;
    }

    private function updateShipping(int $shipping_id, array $shipping)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'kib_shipping_addresses';

        $data = [];
        $types = [];
        foreach (['email', 'phone', 'name', 'country', 'city', 'district', 'zip_code'] as $f) {
            if (array_key_exists($f, $shipping) && $shipping[$f] !== null) {
                $data[$f] = $shipping[$f];
                $types[] = '%s';
            }
        }
        if (empty($data)) return 0;

        $data['updated_at'] = current_time('mysql');
        $types[] = '%s';

        $ok = $wpdb->update($table, $data, ['id' => $shipping_id], $types, ['%d']);
        if ($ok === false) return new WP_Error('kib_shipping_update_failed', 'Failed to update shipping address', ['status' => 500]);
        return (int)$ok;
    }
    private function mapRowToOrder($row)
    {
        return [
            'id'              => (int) $row['id'],
            'order_unique_id' => $row['order_unique_id'],
            'user_id'         => $row['user_id'] ? (int) $row['user_id'] : null,
            'vendor_id'       => $row['vendor_id'] ? (int) $row['vendor_id'] : null,
            'price'           => (float) $row['price'],
            'shipping_id'     => $row['shipping_id'] ? (int) $row['shipping_id'] : null,
            'budget'          => (float) $row['budget'],
            'order_title'     => $row['order_title'],
            'sharing_status'  => $row['sharing_status'],
            'payment_status'  => $row['payment_status'],
            'created_at'      => $row['created_at'],
            'updated_at'      => $row['updated_at'],

            // ✅ Include shipping info
            'shipping' => [
                'id'         => $row['s_id'] ?? null,
                'name'       => $row['s_name'] ?? null,
                'email'      => $row['s_email'] ?? null,
                'phone'      => $row['s_phone'] ?? null,
                'country'    => $row['s_country'] ?? null,
                'city'       => $row['s_city'] ?? null,
                'district'   => $row['s_district'] ?? null,
                'zip_code'   => $row['s_zip_code'] ?? null,
                'created_at' => $row['s_created_at'] ?? null,
                'updated_at' => $row['s_updated_at'] ?? null,
            ],

            // ✅ Include category info
            'category' => [
                'id'    => isset($row['c_id']) ? (int) $row['c_id'] : null,
                'title' => isset($row['c_title']) ? $row['c_title'] : null,
            ],

            'vendor' => [
                'id'    => $row['u_id'] ?? null,
                'name'  => $row['u_name'] ?? null,
                'email' => $row['u_email'] ?? null,
            ],
        ];
    }



    public function index(\WP_REST_Request $request)
    {
        global $wpdb;

        $orders_table   = $wpdb->prefix . 'kib_orders';
        $shipping_table = $wpdb->prefix . 'kib_shipping_addresses';

        // 🔹 Pagination
        $page      = max(1, intval($request->get_param('page') ?? 1));
        $per_page  = min(max(1, intval($request->get_param('per_page') ?? 10)), 100);
        $offset    = ($page - 1) * $per_page;

        // 🔹 Filters
        $search          = sanitize_text_field($request->get_param('search'));
        $vendor_id       = intval($request->get_param('vendor_id'));
        $category_id     = intval($request->get_param('category_id'));
        $sharing_status  = sanitize_text_field($request->get_param('sharing_status')); // ✅ filter only
        $payment_status  = sanitize_text_field($request->get_param('payment_status')); // ✅ filter only



        $where_clauses = [];
        $params        = [];

        // 🔍 Search (NO sharing_status here)
        if (!empty($search)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(
            o.order_unique_id LIKE %s
            OR s.email LIKE %s
            OR s.phone LIKE %s
            OR s.zip_code LIKE %s
        )";
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        // 🧩 Vendor filter
        if (!empty($vendor_id)) {
            $where_clauses[] = "o.vendor_id = %d";
            $params[] = $vendor_id;
        }

        // 🧩 Category filter
        if (!empty($category_id)) {
            $where_clauses[] = "o.category_id = %d";
            $params[] = $category_id;
        }

        // 🧩 Optional sharing_status filter (not part of search)
        if (!empty($sharing_status)) {
            $where_clauses[] = "o.sharing_status = %s";
            $params[] = $sharing_status;
        }

        if (!empty($payment_status)) {
            $where_clauses[] = "o.payment_status = %s";
            $params[] = $payment_status;
        }

        // Combine WHERE SQL
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        // 🔸 Count total
        $count_sql = "
        SELECT COUNT(*)
        FROM {$orders_table} AS o
        LEFT JOIN {$shipping_table} AS s ON o.shipping_id = s.id
        {$where_sql}
    ";

        $total_orders = !empty($params)
            ? (int) $wpdb->get_var($wpdb->prepare($count_sql, $params))
            : (int) $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$orders_table} AS o
            LEFT JOIN {$shipping_table} AS s ON o.shipping_id = s.id
        ");

        // 🔸 Main query
        $query_sql = "
        SELECT o.*, s.zip_code, s.email, s.phone
        FROM {$orders_table} AS o
        LEFT JOIN {$shipping_table} AS s ON o.shipping_id = s.id
        {$where_sql}
        ORDER BY o.id DESC
        LIMIT %d OFFSET %d
    ";

        $query_params = array_merge($params, [$per_page, $offset]);
        $orders = $wpdb->get_results($wpdb->prepare($query_sql, $query_params), ARRAY_A);

        // 🔹 Pagination calc
        $total_pages = (int) ceil($total_orders / $per_page);

        // ✅ Response
        return new \WP_REST_Response([
            'status'         => true,
            'message'        => 'Orders fetched successfully.',

            'pagination'     => [
                'current_page'  => $page,
                'per_page'      => $per_page,
                'total_orders'  => $total_orders,
                'total_pages'   => $total_pages,
            ],
            'orders'         => $orders,
        ], 200);
    }






    // POST /orders — shipping first, then order
    public function create(WP_REST_Request $request)
    {
        global $wpdb;
        $orders = $wpdb->prefix . 'kib_orders';
        //  return 0;

        $user_id     = get_current_user_id();
        $vendor_id   = ($request->get_param('vendor_id') !== null) ? (int)$request->get_param('vendor_id') : null;
        $price       = (float)$request->get_param('price');
        $budget      = ($request->get_param('budget') !== null) ? (float)$request->get_param('budget') : null;
        $order_title = isset($request) ? sanitize_text_field($request->get_param('order_title')) : null;

        $sharing     = sanitize_text_field($request->get_param('sharing_status') ?: 'not accepted');
        $category_id   = sanitize_text_field($request->get_param('category_id'));





        // return (int) $category_id;
        // Shipping part (priority to shipping object)
        $shipping_id = ($request->get_param('shipping_id') !== null) ? (int)$request->get_param('shipping_id') : null;
        $shippingObj = $this->sanitizeShippingInput($request->get_param('shipping'));


        // If shipping object provided → create shipping first
        if ($shippingObj) {
            $newShipId = $this->insertShipping($shippingObj);
            if (is_wp_error($newShipId)) return $newShipId;
            $shipping_id = (int)$newShipId;
        }

        $order_unique_id = wp_generate_uuid4();

        $ok = $wpdb->insert($orders, [
            'user_id'         => $user_id ?: null,
            'vendor_id'       => $vendor_id ?: null,
            'price'           => $price,
            'shipping_id'     => $shipping_id ?: null,
            'budget'          => $budget,
            'order_title'     => $order_title ?: null,
            'order_unique_id' => $order_unique_id,
            'sharing_status'  => $sharing,
            'category_id'     => (int) $category_id,
            'created_at'      => current_time('mysql'),
            'updated_at'      => current_time('mysql'),
        ], ['%d', '%d', '%f', '%d', '%f', '%s', '%s', '%s', '%d', '%s', '%s']);

        if (!$ok) {
            return new WP_Error('kib_order_create_failed', 'Failed to create order', ['status' => 500]);
        }

        $id = (int)$wpdb->insert_id;
        return new WP_REST_Response([
            'status' => true,
            'message' => 'Order placed successfully!',
            'data' => [
                'id' => $id,
                'order_unique_id' => $order_unique_id,
                'shipping_id' => $shipping_id,
            ],
        ], 201);
    }

    // GET /orders/{id}


    public function show(WP_REST_Request $request)
    {
        global $wpdb;

        $orders     = $wpdb->prefix . 'kib_orders';
        $shipping   = $wpdb->prefix . 'kib_shipping_addresses';
        $categories = $wpdb->prefix . 'kib_categories';
        $users      = $wpdb->prefix . 'users';
        $usermeta   = $wpdb->prefix . 'usermeta'; // For user roles

        // ✅ Get uniq_id from request (string)
        $uniq_id = sanitize_text_field($request['uniq_id']);

        // ✅ Build query safely
        $sql = "
        SELECT
            o.*,
            s.id         AS s_id,
            s.email      AS s_email,
            s.phone      AS s_phone,
            s.name       AS s_name,
            s.country    AS s_country,
            s.city       AS s_city,
            s.district   AS s_district,
            s.zip_code   AS s_zip_code,
            s.created_at AS s_created_at,
            s.updated_at AS s_updated_at,
            c.id         AS c_id,
            c.title      AS c_title,
            u.ID         AS u_id,
            u.user_email AS u_email,
            u.display_name AS u_name
        FROM {$orders} o
        LEFT JOIN {$shipping} s ON s.id = o.shipping_id
        LEFT JOIN {$categories} c ON c.id = o.category_id
        LEFT JOIN {$users} u ON u.ID = o.vendor_id
        LEFT JOIN {$usermeta} um ON um.user_id = u.ID AND um.meta_key = '{$wpdb->prefix}capabilities'
        WHERE o.order_unique_id = %s
          AND (um.meta_value LIKE '%vendor%' OR u.ID IS NULL)
        LIMIT 1
    ";

        $row = $wpdb->get_row($wpdb->prepare($sql, $uniq_id), ARRAY_A);

        if (!$row) {
            return new WP_REST_Response([
                'status'  => false,
                'message' => 'Order not found',
                'data'    => null,
            ], 404);
        }

        // ✅ Map the row (shipping + category + vendor)
        $orderData = $this->mapRowToOrder($row);

        return new WP_REST_Response([
            'status'  => true,
            'message' => 'Order retrieved successfully',
            'data'    => $orderData,
        ], 200);
    }



    // PUT/PATCH /orders/{id} — can update order + shipping
    public function update(WP_REST_Request $request)
    {
        global $wpdb;
        $orders = $wpdb->prefix . 'kib_orders';
        $id     = (int) $request['id'];

        $data  = [];
        $types = [];

        $map = [
            'vendor_id'      => '%d',
            'price'          => '%f',
            'shipping_id'    => '%d',
            'budget'         => '%f',
            'order_title'    => '%s',
            'sharing_status' => '%s',
              'payment_status' => '%s',
        ];

        foreach ($map as $key => $type) {
            if (null !== $request->get_param($key)) {
                $val = $request->get_param($key);
                if ($type === '%s') {
                    $val = sanitize_text_field($val);
                }
                $data[$key] = $val;
                $types[]    = $type;
            }
        }

        // Handle shipping object
        $shippingObj = $this->sanitizeShippingInput($request->get_param('shipping'));
        if ($shippingObj) {
            $current_shipping_id = null;

            if (!empty($data['shipping_id'])) {
                $current_shipping_id = (int) $data['shipping_id'];
            } else {
                $current_shipping_id = (int) $wpdb->get_var(
                    $wpdb->prepare("SELECT shipping_id FROM {$orders} WHERE id = %d", $id)
                );
            }

            if ($current_shipping_id) {
                $res = $this->updateShipping($current_shipping_id, $shippingObj);
                if (is_wp_error($res)) return $res;
            } else {
                $newShipId = $this->insertShipping($shippingObj);
                if (is_wp_error($newShipId)) return $newShipId;
                $data['shipping_id'] = (int) $newShipId;
                $types[] = '%d';
            }
        }

        if (empty($data)) {
            return new WP_Error('kib_order_no_changes', 'No fields to update', ['status' => 400]);
        }

        $data['updated_at'] = current_time('mysql');
        $types[] = '%s';

        $ok = $wpdb->update($orders, $data, ['id' => $id], $types, ['%d']);

        if ($ok === false) {
            return new WP_Error('kib_order_update_failed', 'Failed to update order', ['status' => 500]);
        }

        // ✅ SUCCESS RESPONSE HERE
        return new WP_REST_Response([
            'status'  => true,
            'message' => 'Order updated successfully',
            'updated' => (int) $ok,
            'order_id' => $id
        ], 200);
    }

    //working on update vendor confirm order
    public function acceptOrder(\WP_REST_Request $request)
    {
        global $wpdb;

        $orders_table   = $wpdb->prefix . 'kib_orders';
        $shipping_table = $wpdb->prefix . 'kib_shipping_addresses';

        $order_unique_id = sanitize_text_field($request['order_unique_id']);
        $status          = sanitize_text_field($request['sharing_status']);

        // ✅ Get current vendor info
        $current_user = wp_get_current_user();
        if (!$current_user || !in_array('vendor', (array) $current_user->roles, true)) {
            return new \WP_REST_Response([
                'status'  => false,
                'message' => 'Unauthorized. Only vendors can accept orders.',
            ], 403);
        }

        $user_id = $current_user->ID;
        $user_zipcode = get_user_meta($user_id, 'zipcode', true);

        if (empty($user_zipcode)) {
            return new \WP_REST_Response([
                'status'  => false,
                'message' => 'No zipcode found in your profile. Please contact admin.',
            ], 400);
        }

        // ✅ Fetch order using order_unique_id
        $order = $wpdb->get_row($wpdb->prepare("
        SELECT o.id, o.order_unique_id, o.vendor_id, o.sharing_status, s.zip_code
        FROM {$orders_table} AS o
        LEFT JOIN {$shipping_table} AS s ON s.id = o.shipping_id
        WHERE o.order_unique_id = %s
        LIMIT 1
    ", $order_unique_id), ARRAY_A);

        if (!$order) {
            return new \WP_REST_Response([
                'status'  => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // ✅ Verify zipcode match
        if (trim($order['zip_code']) !== trim($user_zipcode)) {
            return new \WP_REST_Response([
                'status'  => false,
                'message' => 'You can only accept orders in your zipcode area.',
                'user_zipcode'  => $user_zipcode,
                'order_zipcode' => $order['zip_code'],
            ], 403);
        }

        // ✅ Prevent double-acceptance
        if (strtolower($order['sharing_status']) === 'accepted') {
            return new \WP_REST_Response([
                'status'  => false,
                'message' => 'This order has already been accepted by another vendor.',
            ], 400);
        }

        // ✅ Update order to mark accepted
        $updated = $wpdb->update(
            $orders_table,
            [
                'sharing_status' => strtolower($status),
                'vendor_id'      => $user_id,
                'updated_at'     => current_time('mysql'),
            ],
            ['order_unique_id' => $order_unique_id],
            ['%s', '%d', '%s'],
            ['%s']
        );

        if ($updated === false) {
            return new \WP_REST_Response([
                'status'  => false,
                'message' => 'Database error while updating order status.',
            ], 500);
        }

        return new \WP_REST_Response([
            'status'  => true,
            'message' => 'Order accepted successfully.',
            'data'    => [
                'order_unique_id' => $order_unique_id,
                'vendor_id'       => $user_id,
                'sharing_status'  => strtolower($status),
                'zip_code'        => $user_zipcode,
            ],
        ], 200);
    }

    //admin can update payment status
    public function update_payment_status(WP_REST_Request $request)
    {
        global $wpdb;

        $orders_table = $wpdb->prefix . 'kib_orders';
        $order_id     = (int) $request['id'];  // ID from URL parameter
        $status       = sanitize_text_field($request['payment_status']);

        // 1️⃣ Check if order exists
        $order_exists = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$orders_table} WHERE id = %d", $order_id)
        );

        if (!$order_exists) {
            return new WP_Error('kib_order_not_found', 'payment not found.', ['status' => 404]);
        }

        // 2️⃣ Update the order’s sharing_status
        $updated = $wpdb->update(
            $orders_table,
            ['payment_status' => $status],
            ['id' => $order_id],
            ['%s'],
            ['%d']
        );

        if ($updated === false) {
            return new WP_Error('kib_payment_update_failed', 'Failed to update payment status.', ['status' => 500]);
        }

        // 3️⃣ Return response
        return new WP_REST_Response([
            'id'             => $order_id,
            'payment_status' => $status,
            'message'        => $updated ? 'Order status updated successfully.' : 'No change — status already set.',
        ], 200);
    }

    // DELETE /orders/{id}
    public function delete(WP_REST_Request $request)
    {
        global $wpdb;
        $orders = $wpdb->prefix . 'kib_orders';
        $shippings = $wpdb->prefix . 'kib_shipping_addresses';
        $id     = (int)$request['id'];
        $order_id = $id;


        $shipping_id = $wpdb->get_var(
            $wpdb->prepare("SELECT shipping_id FROM {$orders} WHERE id = %d", $order_id)
        );

        $shipping_id = (int)$shipping_id;

        // return  $shipping_id;



        if ($shipping_id) {

            $shipping = $wpdb->delete($shippings, ['id' => $shipping_id], ['%d']);


            if (!$shipping) {
                return new WP_Error('kib_shipping_delete_failed', 'Failed to delete order', ['status' => 500]);
            }
        }



        $order = $wpdb->delete($orders, ['id' => $id], ['%d']);
        // return $order;



        if (!$order) {
            return new WP_Error('kib_order_delete_failed', 'Failed to delete order', ['status' => 500]);
        }


        // We DO NOT delete shipping row; it may be reused by other orders.
        return new WP_REST_Response(['deleted' => (int)$order], 200);
    }

    public function myOrder(\WP_REST_Request $request)
    {
        global $wpdb;

        // Table names
        $orders_table   = $wpdb->prefix . 'kib_orders';
        $shipping_table = $wpdb->prefix . 'kib_shipping_addresses';

        // ✅ Get current logged-in user
        $current_user = wp_get_current_user();
        if (!$current_user || empty($current_user->ID)) {
            return new \WP_REST_Response([
                'status'  => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        $user_id = $current_user->ID;

        // ✅ Get vendor’s zipcode
        $user_zipcode = get_user_meta($user_id, 'zipcode', true);
        if (empty($user_zipcode)) {
            return new \WP_REST_Response([
                'status'  => false,
                'message' => 'No zipcode found for this vendor.',
                'orders'  => [],
            ], 200);
        }

        // ✅ Query params
        $status_param = sanitize_text_field($request->get_param('status'));
        $page         = max(1, intval($request->get_param('page') ?? 1));
        $per_page     = min(max(1, intval($request->get_param('per_page') ?? 10)), 100);
        $offset       = ($page - 1) * $per_page;

        // ✅ Default to "unaccepted"
        if (empty($status_param)) {
            $status_param = 'unaccepted';
        }

        // ✅ Base WHERE clause (zipcode match)
        $where_sql = "WHERE s.zip_code = %s";
        $params    = [$user_zipcode];

        // ✅ Apply status filter
        if ($status_param === 'unaccepted') {
            $where_sql .= " AND (o.sharing_status IS NULL OR o.sharing_status != 'accepted')";
        } elseif ($status_param === 'accepted') {
            // 🔒 Only show accepted orders belonging to this vendor
            $where_sql .= " AND o.sharing_status = 'accepted' AND o.vendor_id = %d";
            $params[] = $user_id;
        }

        // ✅ Count total filtered orders
        $count_sql = "
        SELECT COUNT(*)
        FROM {$orders_table} AS o
        LEFT JOIN {$shipping_table} AS s ON o.shipping_id = s.id
        {$where_sql}
    ";
        $total_orders = (int) $wpdb->get_var($wpdb->prepare($count_sql, $params));

        // ✅ Fetch paginated results
        $query_sql = "
        SELECT 
            o.*, 
            s.zip_code   AS shipping_zip,
            s.email      AS shipping_email,
            s.phone      AS shipping_phone,
            s.name       AS shipping_name,
            s.city       AS shipping_city,
            s.country    AS shipping_country,
            s.district   AS shipping_district
        FROM {$orders_table} AS o
        LEFT JOIN {$shipping_table} AS s ON s.id = o.shipping_id
        {$where_sql}
        ORDER BY o.id DESC
        LIMIT %d OFFSET %d
    ";

        $params[] = $per_page;
        $params[] = $offset;

        $orders = $wpdb->get_results($wpdb->prepare($query_sql, $params), ARRAY_A);

        // ✅ Pagination
        $total_pages = (int) ceil($total_orders / $per_page);

        // ✅ Map clean data
        $ordersData = [];
        foreach ($orders as $row) {
            if ($status_param === 'unaccepted') {
                $ordersData[] = [
                    'order_unique_id' => $row['order_unique_id'] ?? null,
                    'sharing_status'  => $row['sharing_status'] ?? null,
                    'zip_code'        => $row['shipping_zip'] ?? null,
                    'budget'          => $row['budget'] ?? null,
                    'created_at'      => $row['created_at'] ?? null,
                    'updated_at'      => $row['updated_at'] ?? null,
                ];
            } else {
                $ordersData[] = [
                    'order_unique_id' => $row['order_unique_id'] ?? null,
                    'sharing_status'  => $row['sharing_status'] ?? null,
                    'total_amount'    => $row['total_amount'] ?? null,
                    'zip_code'        => $row['shipping_zip'] ?? null,
                    'budget'          => $row['budget'] ?? null,
                    'email'           => $row['shipping_email'] ?? null,
                    'phone'           => $row['shipping_phone'] ?? null,
                    'name'            => $row['shipping_name'] ?? null,
                    'city'            => $row['shipping_city'] ?? null,
                    'country'         => $row['shipping_country'] ?? null,
                    'district'        => $row['shipping_district'] ?? null,
                    'created_at'      => $row['created_at'] ?? null,
                    'updated_at'      => $row['updated_at'] ?? null,
                ];
            }
        }

        // ✅ Final Response
        return new \WP_REST_Response([
            'status'        => true,
            'message'       => 'Orders fetched successfully.',
            'filter'        => $status_param,
            'user_zipcode'  => $user_zipcode,
            'current_page'  => $page,
            'per_page'      => $per_page,
            'total_orders'  => $total_orders,
            'total_pages'   => $total_pages,
            'orders'        => $ordersData,
        ], 200);
    }
}
