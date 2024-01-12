<?php

require '../vendor/autoload.php'; /** Memuat pustaka Slim Framework dan dependensinya */

/** Memuat controller untuk menangani request produk yang dilakkukan oleh user */
require '../controllers/ProductController.php'; 

require '../routes/ProductRoutes.php'; /** Memuat definisi rute API */

/** Mengimport kelas AppFactory dari Slim */
use Slim\Factory\AppFactory;

/** Membuat instance baru dari aplikasi Slim */
$app = AppFactory::create();

/** Melakukan koneksi ke database */
$database = new PDO('mysql:host=localhost;dbname=restapi-php','samplekoding','Password123@');

/** Menambahkan middleware error untuk menangani kesalahan yang terjadi selama proses request */
$app->addErrorMiddleware(false,true,false);

/** Membuat instance ProductController untuk menangani logika bisnis terkait produk */
$productController = new ProductController($app,$database);

/** Mengatur rute untuk API produk */
ProductRoutes::setup($app,$database);

/** Menjalankan alikasi slim */
$app->run();