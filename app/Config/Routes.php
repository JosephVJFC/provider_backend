<?php

use CodeIgniter\Router\RouteCollection;
/**
 * @var RouteCollection $routes
 */

// $routes->group("api", function ($routes) {
//     $routes->post("signup", "Signup::signup",['filter' => 'authFilter']);
//     $routes->post("otpverify", "Signup::otp_verification",['filter' => 'authFilter']);
//     $routes->post("signin", "Signup::login", ['filter' => 'authFilter']);
//     $routes->post("resendotp", "Signup::resendotp", ['filter' => 'authFilter']);

// });
$routes->post('api/signup', 'Signup::signup',['namespace' => 'App\Controllers\Registration']);
$routes->post('api/otpverify', 'Signup::otp_verification',['namespace' => 'App\Controllers\Registration']);
$routes->post('api/signin', 'Signup::login',['namespace' => 'App\Controllers\Registration']);
$routes->post('api/resendotp', 'Signup::resendotp',['namespace' => 'App\Controllers\Registration']);
$routes->post('api/getuser', 'Signup::getuserbytoken',['namespace' => 'App\Controllers\Registration']);
$routes->post('api/logout', 'Signup::logout',['namespace' => 'App\Controllers\Registration']);






