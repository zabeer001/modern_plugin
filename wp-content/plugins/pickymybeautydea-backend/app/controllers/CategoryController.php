<?php
if (!defined('ABSPATH')) exit;

// Include the utils class
require_once __DIR__ . '/../helpers/Utils.php';
require_once __DIR__ . '/../helpers/RestHelper.php';



class CategoryController {

    /**
     * GET /categories
     * List all categories (paginated)
     */
    public function index( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kib_categories';

        $per_page = max(1, (int) $request->get_param('per_page') ?: 20);
        $page     = max(1, (int) $request->get_param('page') ?: 1);
        $offset   = ($page - 1) * $per_page;

        $items = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset),
            ARRAY_A
        );

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

        return new WP_REST_Response([
            'data'  => $items,
            'meta'  => [
                'total'     => $total,
                'page'      => $page,
                'per_page'  => $per_page,
            ],
        ], 200);
    }

    /**
     * POST /categories
     * Create new category
     */
    public function create( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kib_categories';

        $title       = sanitize_text_field( $request->get_param('title') );
        $description = sanitize_textarea_field( $request->get_param('description') );

        if ( empty($title) ) {
            return new WP_Error('kib_category_title_required', 'Category title is required.', ['status' => 400]);
        }

        $ok = $wpdb->insert($table, [
            'title'       => $title,
            'description' => $description,
            'created_at'  => current_time('mysql'),
            'updated_at'  => current_time('mysql'),
        ], ['%s', '%s', '%s', '%s']);

        if ( ! $ok ) {
            return new WP_Error('kib_category_create_failed', 'Failed to create category.', ['status' => 500]);
        }

        $id = (int) $wpdb->insert_id;
        return new WP_REST_Response([
            'id'    => $id,
            'title' => $title,
        ], 201);
    }

    /**
     * GET /categories/{id}
     * Retrieve single category
     */
    public function show( WP_REST_Request $request ) {

        // return 0;
        global $wpdb;
        $table = $wpdb->prefix . 'kib_categories';
        $id    = (int) $request['id'];

        $row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id), ARRAY_A );
        if ( ! $row ) {
            return new WP_Error('kib_category_not_found', 'Category not found.', ['status' => 404]);
        }

        return new WP_REST_Response($row, 200);
    }

    /**
     * PATCH /categories/{id}
     * Update existing category
     */
    public function update( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kib_categories';
        $id    = (int) $request['id'];

        $data  = [];
        $types = [];

        $map = [
            'title'       => '%s',
            'description' => '%s',
        ];

        foreach ($map as $key => $type) {
            if ( null !== $request->get_param($key) ) {
                $val = $request->get_param($key);
                $val = $key === 'description' ? sanitize_textarea_field($val) : sanitize_text_field($val);
                $data[$key] = $val;
                $types[] = $type;
            }
        }

        if ( empty($data) ) {
            return new WP_Error('kib_category_no_changes', 'No fields to update.', ['status' => 400]);
        }

        // Always update timestamp
        $data['updated_at'] = current_time('mysql');
        $types[] = '%s';

        $ok = $wpdb->update($table, $data, ['id' => $id], $types, ['%d']);
        if ( $ok === false ) {
            return new WP_Error('kib_category_update_failed', 'Failed to update category.', ['status' => 500]);
        }

        return new WP_REST_Response(['updated' => (int) $ok], 200);
    }

    /**
     * DELETE /categories/{id}
     * Delete a category
     */
    public function delete( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kib_categories';
        $id    = (int) $request['id'];

        $ok = $wpdb->delete($table, ['id' => $id], ['%d']);
        if ( ! $ok ) {
            return new WP_Error('kib_category_delete_failed', 'Failed to delete category.', ['status' => 500]);
        }

        return new WP_REST_Response(['deleted' => (int)$ok], 200);
    }
}