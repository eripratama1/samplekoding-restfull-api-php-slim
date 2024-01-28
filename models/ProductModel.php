<?php

class ProductModel

{
    /** Fungsi untuk melakukan pencarian produk di database berdasarkan keyword pencarian */
    public static function searchProducts($database, $search)
    {
        /** Menyiapkan query SQL untuk mencari produk yang namanya mengandung keyword pencarian */
        $query = "SELECT * FROM products WHERE name LIKE :search";

        /** Menyiapkan pernyataan database menggunakan query yang telah disiapkan */
        $stmt = $database->prepare($query);

        /** Mengikat nilai pencarian ke placeholder :search dalam query */
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);

        /** Mengeksekusi query database */
        $stmt->execute();

        /** Mengambil semua hasil pencarian sebagai array asosiatif */
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /** Mengembalikan hasil pencarian, termasuk data produk dan jumlah total produk yang ditemukan */
        return [
            'data' => $products,
            'total' => count($products)
        ];
    }
}
