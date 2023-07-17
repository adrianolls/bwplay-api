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

$app->group(['middleware' => 'api'], function () use ($app) {

    /**
     * Game Routes
     */
    $app->post('game/broadcast', 'GameController@broadcastRequest');
    $app->post('game/onlines', 'GameController@onlineListRequest');
    $app->post('game/email', 'GameController@emailRequest');
    $app->post('game/setdoublerate', 'GameController@setDoubleRate');

    /**
     * User Routes
     */
    $app->post('user/roles', 'UserController@rolesRequest');
    $app->post('user/removelock', 'UserController@removelockRequest');

    /**
     * Roles Routes
     */
    $app->post('role/character', 'RoleController@characterRequest');
    $app->put('role/character', 'RoleController@characterResponse');
    $app->post('role/faction', 'RoleController@factionRequest');
    $app->post('role/userfaction', 'RoleController@userfactionRequest');
    $app->post('role/charactername', 'RoleController@characternameRequest');
    $app->post('role/resetbank', 'RoleController@resetBankRequest');
    $app->post('role/rename', 'RoleController@renameRequest');
    $app->post('role/banrole', 'RoleController@banRole');
    $app->post('role/muterole', 'RoleController@muteRole');
    $app->post('role/banaccount', 'RoleController@banAccount');
    $app->post('role/meridianfull', 'RoleController@meridianFull');
    $app->post('role/titlefull', 'RoleController@titleFull');
});
