<?php

class And_Load_Api extends WP_REST_Controller {
    public function __construct() {
        $this->version = '2';
        $this->namespace = 'and_load/v' . $this->version;

        add_action('rest_api_init', array($this, 'register_routes' ));
    }

    /**
     * Register all the REST API endpoints
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/files', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_files' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' )
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_file' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( true ),
            )
        ) );

        register_rest_route( $this->namespace, '/files/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_file' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( true ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( false ),
            )
        ) );


        register_rest_route( $this->namespace, '/', array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'get_public_item_schema' ),
        ) );
    }

    /**
     * Get all files
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_files( $request ) {
        $items = array(); //do a query, call another class, etc
        $data = 'works!';

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Get a single file
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_file( $request ) {
        $items = array(); //do a query, call another class, etc
        $data = 'works!';

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Create a file
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function create_file( $request ) {
        $items = array(); //do a query, call another class, etc
        $data = 'works!';

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Update a file
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function update_file( $request ) {
        $items = array(); //do a query, call another class, etc
        $data = 'works!';

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_items_permissions_check( $request ) {
        //return true; <--use to make readable by all
        return current_user_can( 'edit_something' );
    }

    /**
     * Check if a given request has access to create items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function create_item_permissions_check( $request ) {
        return current_user_can( 'edit_something' );
    }

    /**
     * Check if a given request has access to update a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function update_item_permissions_check( $request ) {
        return $this->create_item_permissions_check( $request );
    }
}
