<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(
    [
        'prefix' => 'backoffice',
        'namespace' => 'App\Http\Controllers\Api\Backoffice',
        'middleware' => ['log.request']
    ],
    function () {

        Route::group(
            [
                'prefix' => 'auth',
            ],
            function () {
                Route::post('/login', [
                    'uses' => 'AuthController@login'
                ]);

                Route::group(
                    [
                        'middleware' => ['auth:sanctum'],
                    ],
                    function () {

                        Route::post('/logout', [
                            'uses' => 'AuthController@logout'
                        ]);

                        Route::get('/me', [
                            'uses' => 'AuthController@me'
                        ]);
                    }
                );
            }
        );

        Route::group(
            [
                'middleware' => ['auth:sanctum'],
            ],
            function () {
                Route::group(
                    [
                        'middleware' => ['role:viewer|admin'],
                    ],
                    function () {
                        Route::group(
                            [
                                'prefix' => 'category',
                            ],
                            function () {
                                Route::get('', [
                                    'uses' => 'CategoryController@index'
                                ]);

                                Route::get('/{id}', [
                                    'uses' => 'CategoryController@findById'
                                ])->where('id', '[1-9][0-9]*');
                            }
                        );


                        Route::group(
                            [
                                'prefix' => 'product',
                            ],
                            function () {
                                Route::get('', [
                                    'uses' => 'ProductController@index'
                                ]);

                                Route::get('/{id}', [
                                    'uses' => 'ProductController@findById'
                                ])->where('id', '[1-9][0-9]*');
                            }
                        );
                    }
                );

                Route::group(
                    [
                        'middleware' => ['role:editor|admin'],
                    ],
                    function () {
                        Route::group(
                            [
                                'prefix' => 'category',
                            ],
                            function () {
                                Route::patch('/{id}', [
                                    'uses' => 'CategoryController@update'
                                ])->where('id', '[1-9][0-9]*');
                            }
                        );


                        Route::group(
                            [
                                'prefix' => 'product',
                            ],
                            function () {
                                Route::patch('/{id}', [
                                    'uses' => 'ProductController@update'
                                ])->where('id', '[1-9][0-9]*');
                            }
                        );
                    }
                );
            }
        );
    }
);

Route::group([
    'prefix' => 'public',
    'namespace' => 'App\Http\Controllers\Api\Public',
    'middleware' => ['log.request'],
], function () {
    Route::group(
        [
            'prefix' => 'auth'
        ],
        function () {
            Route::get('/login/{provider}', [
                'uses' => 'AuthController@redirectToProvider'
            ]);

            Route::get('/login/{provider}/callback', [
                'uses' => 'AuthController@handleProviderCallback'
            ]);
        }
    );

    Route::group(
        [
            'prefix' => 'product',
        ],
        function () {
            Route::get('/', [
                'uses' => 'ProductController@index'
            ]);

            Route::get('/{id}', [
                'uses' => 'ProductController@show'
            ])->where('id', '[1-9][0-9]*');
        }
    );

    Route::group(
        [
            'prefix' => 'order',
        ],
        function () {
            Route::get('/', [
                'uses' => 'OrderController@index'
            ])->name('api.public.order.index');

            Route::get('/{order_number}', [
                'uses' => 'OrderController@show'
            ])->name('api.public.order.show');

            Route::post('/', [
                'uses' => 'OrderController@store'
            ])->name('api.public.order.store');
        }
    );
});
