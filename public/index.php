<?php

require '../vendor/autoload.php'; /** Memuat pustaka Slim Framework dan dependensinya */

/** Memuat controller untuk menangani request produk yang dilakkukan oleh user */
require '../controllers/ProductController.php'; 

require '../routes/ProductRoutes.php'; /** Memuat definisi rute API */
require '../models/ProductModel.php';
require '../routes/SearchRoutes.php';

/** Mengimport kelas AppFactory dari Slim */
use Slim\Factory\AppFactory;

/** Membuat instance baru dari aplikasi Slim */
$app = AppFactory::create();

/** Melakukan koneksi ke database */
$database = new PDO('mysql:host=localhost;dbname=restapi-php','samplekoding','Password123@');

/** Menambahkan middleware error untuk menangani kesalahan yang terjadi selama proses request */
$app->addErrorMiddleware(true,true,false);

/** Membuat instance productModel untuk menangani pencarian data */
$productModel =new ProductModel($database);

/** Membuat instance ProductController untuk menangani logika bisnis terkait produk */
$productController = new ProductController($app,$database);

/** Mengatur rute untuk API produk pencarian data produk */
ProductRoutes::setup($app,$database);
SearchRoutes::setup($app,$database,$productModel);

/** Menjalankan aplikasi slim */
$app->run();