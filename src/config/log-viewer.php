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
     * If you want to change the page heading
     *
     */
    'page_heading' => 'Logs Viewer',


    /**
     * If you want to use a authentication process access the system information view page
     */
    'authentication' => [
        'is_enabled' => env('LOGVIEWER_AUTH_ENABLED', false),
        'login_id' => env('LOGVIEWER_LOGIN_ID', 'onexadmin'),
        'password' => env('LOGVIEWER_LOGIN_PASSWORD', 'onexpassword')
    ]
];