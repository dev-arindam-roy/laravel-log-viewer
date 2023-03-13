<?php

use Illuminate\Support\Facades\Route;

$logViewerRouteEnabled = true;
$logViewerRoutePrefix = 'onex';
$logViewerRouteName = 'log-viewer';
$logViewerMiddleware = ['web'];

$publishedConfigFilePath = config_path('log-viewer.php');
if (file_exists($publishedConfigFilePath)) {
    $logViewerRouteEnabled = !empty(config('log-viewer.is_route_enabled')) ? config('log-viewer.is_route_enabled') : true;
    $logViewerRoutePrefix = !empty(config('log-viewer.route_prefix')) ? config('log-viewer.route_prefix') : '';
    $logViewerRouteName = !empty(config('log-viewer.route_name')) ? config('log-viewer.route_name') : $logViewerRouteName; 
}

if ($logViewerRouteEnabled) {
    Route::group(['namespace' => 'CreativeSyntax\LogViewer\Http\Controllers', 'prefix' => $logViewerRoutePrefix, 'middleware' => $logViewerMiddleware], function() use($logViewerRouteName) {
        Route::get($logViewerRouteName, 'LogViewerController@index')->name('cssLogViewer.index');
        Route::get($logViewerRouteName . '/logs/{file}', 'LogViewerController@viewLogs')->name('cssLogViewer.viewlogs');
        Route::get($logViewerRouteName . '/download/{file}', 'LogViewerController@downloadLogs')->name('cssLogViewer.downloadlogs');
        Route::get($logViewerRouteName . '/clear/{file}', 'LogViewerController@clearLogs')->name('cssLogViewer.clearlogs');
        Route::get($logViewerRouteName . '/delete/{file}', 'LogViewerController@deleteLogs')->name('cssLogViewer.deletelogs');
        Route::post($logViewerRouteName . '/bulk-action', 'LogViewerController@bulkAction')->name('cssLogViewer.bulkaction');
        Route::post('onexloginfo-adminaccess', 'LogViewerController@adminAccess')->name('onexloginfoAdminaccess');
        Route::get('onexloginfo-adminaccess/logout', 'LogViewerController@logout')->name('onexloginfoAdminLogout');
    });
}