<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/** @var \Laravel\Lumen\Routing\Router $router */
$router->get('/', function () {
    return app()->version();
});

$router->get('/_ah/health', 'HealthController@index');

/** @deprecated  */
$router->get('/v1/oauth2/service', 'Oauth2AuthenticationController@getOauth2Url');

$router->get('/v1/oauth2/test', 'Oauth2AuthenticationController@checkLogin');

$router->get('/v1/licenses', 'LicenseInformationController@getLicenses');
$router->get('/v1/licenses/{licenseName}/copyable', 'LicenseInformationController@copyable');

$router->get('/v1/content', 'ContentController@getContent');
$router->get('/v1/content/changed', 'ContentController@getChangedContent');
$router->post('/v1/content', 'ContentController@addContent');
$router->get('/v1/content/{id}', 'ContentController@getContentById');
$router->delete('/v1/content/{id}', 'ContentController@deleteContent');

$router->post('/v1/content/{id}/licenses', 'ContentController@setLicenses');
$router->put('/v1/content/{id}/licenses/{license_id}', 'ContentController@addLicense');
$router->delete('/v1/content/{id}/licenses/{license_id}', 'ContentController@removeLicense');

$router->get('/v1/license/{license}/content', 'LicenseContentController@index');
$router->post('/v1/site/{site_id}/content', 'SiteContentController@store'); // Add Content based on site
$router->post('/v1/site/{site_id}/content-by-id', 'SiteContentController@getMultipleContent'); // Add Content based on site
$router->put('/v1/site/{site_id}/content/{content_id}', 'SiteContentController@update'); // Update Content License based on site and sites id
$router->get('/v1/site/{site_id}/content/{content_id}', 'SiteContentController@show'); // Return Content license based on site and sites id
$router->delete('/v1/site/{site_id}/content/{content_id}', 'SiteContentController@delete'); // Delete Content license based on site and sites id
