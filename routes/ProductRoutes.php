<?php

 class ProductRoutes
 {

    /**
     * Class ini berfungsi untuk mengatur rute untuk API produk.
     */
    public static function setup($app,$database)
    {
        /**
         * Metode ini dipanggil untuk mendaftarkan route produk ke aplikasi Slim.
         * Yang mana menerima dua argumen yaitu $app: Objek aplikasi Slim.
         * $database: Koneksi database.
         */

         /**Membuat instance baru dari ProductController */
        $productController = new ProductController($app,$database);

        /**
         * Membuat grup rute dengan route awal /api/v1/products.
           Ini artinya semua route yang didefinisikan di dalam grup ini akan memiliki route tersebut.
         */
        $app->group('/api/v1/products',function($group) use ($productController){

            /**
             * Mendefinisikan rute GET untuk menampilkan daftar produk.
               URL    : /api/v1/products
               Metode : GET
               Aksi   : Memanggil fungsi index() di ProductController.
             */
            $group->get('',[$productController,'index']);

            /**
             * Mendefinisikan rute GET untuk menampilkan data produk berdasarkan id produk.
               URL    : /api/v1/products/idProduk
               Metode : GET
               Aksi   : Memanggil fungsi show() di ProductController.
             */
            $group->get('/{id}',[$productController,'show']);

            /**
              Mendefinisikan rute POST untuk menyimpan produk baru.
              URL     : /api/v1/products/store
             Metode   : POST
             Aksi     : Memanggil fungsi store() di ProductController.
             */
            $group->post('/store',[$productController,'store']);

             /**
              Mendefinisikan dua rute PUT dan PATCH untuk update produk baru.
              URL     : /api/v1/products/update/{id}
             Metode   : PUT or PATCH
             Aksi     : Memanggil fungsi update() di ProductController.
             */
            $group->put('/update/{id}',[$productController,'update']);
            $group->patch('/update/{id}',[$productController,'update']);
        });
    }
 }