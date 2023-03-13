<?php

return [

    /**
     * If you want to disable the route for the log view page
     * 
     * available options: true, false
     * 
     * default: true
     */

    'is_route_enabled' => true, 
    
    
    /**
     * If you want to change the route prefix
     * 
     */
    'route_prefix' => 'onex',
    

    /**
     * If you want to change the route name
     * 
     * default: log-viewer
     */
    'route_name' => 'log-viewer',

    /**
     * If you want to add any middleware (s) to restrict the access
     * 
     * default: web
     */
    'route_middleware' => ['web'],

    /**
     * If you want to change the page heading
     *
     */
    'page_heading' => 'Logs Viewer',


    /**
     * If you want to use a authentication process to access the system log information view page
     */
    'authentication' => [
        'is_enabled' => env('LOGVIEWER_AUTH_ENABLED', false),
        'login_id' => env('LOGVIEWER_LOGIN_ID', 'onexadmin'),
        'password' => env('LOGVIEWER_LOGIN_PASSWORD', 'onexpassword')
    ]
];