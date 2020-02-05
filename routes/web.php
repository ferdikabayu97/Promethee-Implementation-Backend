<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
$router->get('/api/req/', 'RekomendasiController@index');
$router->get('/api/test/', 'BaruController@index');

$router->get('/api/pend/', 'PendudukController@index');
$router->get('/api/login/', 'LoginController@index');
$router->get('/api/pes/', 'PesaingUsahaController@index');
$router->get('/api/opt/', 'OptimalisationController@index');
$router->get('/api/gpass/', 'GantiPasswordController@index'); // maunya pake put mas:(
$router->get('/api/bakun/', 'BuatAkunController@index');
$router->get('/api/veri/', 'VerifikasiController@index');
$router->get('/api/lpass/', 'LupaPasswordController@index');
$router->get('/', function(){
    // url has been moved
    return redirect('http://user.rekusaha.com/');
});




$router->get('/key', function() {
    return str_random(32);
});
