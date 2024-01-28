<?php

class SearchRoutes

{
    /** Mendaftarkan rute untuk menangani pencarian produk dalam API. */
    public static function setup($app,$database,$productModel)
    {
        /** Mendaftarkan rute API untuk pencarian produk */
        $app->get('/api/v1/search-products',function($request,$response) use($database,$productModel){
            /** Mengambil istilah pencarian dari parameter kueri 'search' */
            $searchTerm = $request->getQueryParams()['search'] ?? '';

            /** Melakukan pencarian produk di database */
            $searchResult = $productModel->searchProducts($database,$searchTerm);

            /** Memeriksa apakah produk ditemukan */
            if (empty($searchResult['data'])) {

                /** Mengembalikan respons JSON dengan pesan 'Product Not Found' */
                $response->getBody()->write(json_encode(['message' => 'Product Not found']));
                return $response->withHeader('Content-Type','application/json');
            }
            /** Mengembalikan hasil pencarian dalam format JSON */
            $response->getBody()->write(json_encode($searchResult));
            return $response->withHeader('Content-Type','application/json');
        });
    }
}