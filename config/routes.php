<?php

declare(strict_types=1);

/**
 * Application route definitions.
 *
 * Routes are registered on the $router instance provided by public/index.php.
 *
 * Signature:
 *   $router->get(path, ControllerClass, action, [middleware])
 *   $router->post(path, ControllerClass, action, [middleware])
 *
 * Middleware tags: 'auth' | 'csrf' | 'admin'
 */

// -----------------------------------------------------------------------------
// Authentication
// -----------------------------------------------------------------------------
$router->get( '/login',  'AuthController', 'showLogin');
$router->post('/login',  'AuthController', 'login',   ['csrf']);
$router->post('/logout', 'AuthController', 'logout',  ['auth', 'csrf']);

// -----------------------------------------------------------------------------
// Dashboard — Milestone 1.7
// -----------------------------------------------------------------------------
// $router->get('/', 'DashboardController', 'index', ['auth']);

// -----------------------------------------------------------------------------
// User Management — Milestone 1.3
// -----------------------------------------------------------------------------
// $router->get( '/users',             'UserController', 'index',   ['auth', 'admin']);
// $router->get( '/users/create',      'UserController', 'create',  ['auth', 'admin']);
// $router->post('/users',             'UserController', 'store',   ['auth', 'admin', 'csrf']);
// $router->get( '/users/{id}/edit',   'UserController', 'edit',    ['auth', 'admin']);
// $router->post('/users/{id}/edit',   'UserController', 'update',  ['auth', 'admin', 'csrf']);
// $router->post('/users/{id}/delete', 'UserController', 'destroy', ['auth', 'admin', 'csrf']);

// -----------------------------------------------------------------------------
// Leads — Milestone 1.4
// -----------------------------------------------------------------------------
// $router->get( '/leads',              'LeadController', 'index',   ['auth']);
// $router->get( '/leads/create',       'LeadController', 'create',  ['auth']);
// $router->post('/leads',              'LeadController', 'store',   ['auth', 'csrf']);
// $router->get( '/leads/{id}',         'LeadController', 'show',    ['auth']);
// $router->get( '/leads/{id}/edit',    'LeadController', 'edit',    ['auth']);
// $router->post('/leads/{id}/edit',    'LeadController', 'update',  ['auth', 'csrf']);
// $router->post('/leads/{id}/delete',  'LeadController', 'destroy', ['auth', 'csrf']);
// $router->post('/leads/{id}/convert', 'LeadController', 'convert', ['auth', 'csrf']);

// -----------------------------------------------------------------------------
// Customers — Milestone 1.5
// -----------------------------------------------------------------------------
// $router->get( '/customers',             'CustomerController', 'index',   ['auth']);
// $router->get( '/customers/create',      'CustomerController', 'create',  ['auth']);
// $router->post('/customers',             'CustomerController', 'store',   ['auth', 'csrf']);
// $router->get( '/customers/{id}',        'CustomerController', 'show',    ['auth']);
// $router->get( '/customers/{id}/edit',   'CustomerController', 'edit',    ['auth']);
// $router->post('/customers/{id}/edit',   'CustomerController', 'update',  ['auth', 'csrf']);
// $router->post('/customers/{id}/delete', 'CustomerController', 'destroy', ['auth', 'csrf']);

// -----------------------------------------------------------------------------
// Follow-Ups — Milestone 1.6
// -----------------------------------------------------------------------------
// $router->get( '/follow-ups',             'FollowUpController', 'index',         ['auth']);
// $router->get( '/follow-ups/create',      'FollowUpController', 'create',        ['auth']);
// $router->post('/follow-ups',             'FollowUpController', 'store',         ['auth', 'csrf']);
// $router->get( '/follow-ups/{id}/edit',   'FollowUpController', 'edit',          ['auth']);
// $router->post('/follow-ups/{id}/edit',   'FollowUpController', 'update',        ['auth', 'csrf']);
// $router->post('/follow-ups/{id}/done',   'FollowUpController', 'markDone',      ['auth', 'csrf']);
// $router->post('/follow-ups/{id}/cancel', 'FollowUpController', 'markCancelled', ['auth', 'csrf']);
// $router->post('/follow-ups/{id}/delete', 'FollowUpController', 'destroy',       ['auth', 'csrf']);
