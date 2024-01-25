<?php

/**
 * Memuat interface PSR-7 untuk objek request dan response
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductController
{
    /**
     * Koneksi database dan instance aplikasi Slim
     */
    private $database;
    private $app;

    /**
     * Membuat method construct untuk menginisialisasi properti
     */
    public function __construct($app, $database)
    {
        $this->app = $app;
        $this->database = $database;
    }

    public function index(Request $request, Response $response)
    {
        /**
         * Mendapatkan nomor halaman dari query parameter 
         * dan menetapkan nilai default 1 jika tidak ada.
         */
        $page = $request->getQueryParams()['page'] ?? 1;

        /** Menentukan jumlah produk yang ditampilkan per halaman */
        $perPage = 1;

        /** Menghitung offset untuk query SQL berdasarkan nomor halaman dan jumlah per halaman */
        $offset = ($page - 1) * $perPage;

        /** menyiapkan query SQL untuk mengambil produk dengan limit dan offset untuk pagination. */
        $query = "SELECT * FROM products LIMIT :perPage OFFSET :offset";

        /** menyiapkan statement/pernyataan SQL. */
        $stmt = $this->database->prepare($query);

        /**
         * mengaitkan nilai parameter ke variabel $perPage dan $offset untuk mencegah SQL injection
         */
        $stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        /** mengeksekusi query SQL. */
        $stmt->execute();

        /** mengambil semua hasil query sebagai array asosiatif */
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /** menyiapkan query untuk menghitung jumlah total produk. */
        $countQuery = "SELECT COUNT(id) as total FROM products";

        $countStmt = $this->database->prepare($countQuery);
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];


        $lastPage = ceil($total / $perPage);
        /** menghitung jumlah halaman terakhir. */

        /**
         * menghitung halaman sebelumnya dan berikutnya.
         */
        $prevPage = max(1, $page - 1);
        $nextPage = min($lastPage, $page + 1);

        /** 
         * membuat representasi JSON dari data dan
         * informasi paging dan mentapkan formatnya dalam bentuk
         * JSON 
         */
        $response->getBody()->write(json_encode([
            'data' => $products,
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $page,
            'lastPage' => $lastPage,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function store(Request $request, Response $response)
    {
        /** Validasi Content-Type header yang dikirim */
        $contentType = $request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === false) {

            /** Jika Content-Type bukan JSON, kembalikan error berikut */
            $response->getBody()->write(json_encode(['error' => 'Invalid content type, expected application/json']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        /**
         * Mendapatkan data produk dari request body dan men-decode JSON
         */
        $data = json_decode($request->getBody(), true);

        /** Validasi kelengkapan data */
        if (empty($data['name']) || empty($data['description']) || empty($data['price'])) {

            /** Jika data tidak lengkap, kembalikan error */
            $response->getBody()->write(json_encode(['error' => 'Incomplete data ']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(302);
        }

        /** Menyiapkan query SQL untuk menyimpan produk */
        $query = "INSERT INTO products (name,description,price) VALUES (:name,:description,:price)";
        $stmt = $this->database->prepare($query);

        /**
         * Mengaitkan nilai parameter :name,:description, dan :price
         * dalam query SQL dengan variabel $data
         */
        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(':price', $data['price'], PDO::PARAM_INT);

        /**
         * Mengeksekusi query dan menangani kesalahan yang mungkin terjadi
         */
        try {
            $stmt->execute();

            /** Jika berhasil, kembalikan pesan sukses */
            $response->getBody()->write(json_encode(['message' => 'Product added']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (PDOException $e) {

            /** Jika tidak, kembalikan pesan error */
            $response->getBody()->write(json_encode(['error' => 'Error stored product : ' . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function show(Request $request, Response $response, $args)
    {
        /** Mendapatkan ID produk dari argumen yang dikirimkan oleh user */
        $productId = $args['id'];

        /** Menyiapkan query untuk mendapatkan data yang sesuai dengan id produk yang dikirimkan */
        $query = "SELECT * FROM  products WHERE id = :id";
        $stmt = $this->database->prepare($query);
        $stmt->bindParam(":id", $productId, PDO::PARAM_INT);

        /** Mengeksekusi query */
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        /** 
         * Jika data produk tidak ditemukan kembalikan response error
         * Jika data produk ditemukan kembalikan response beserta datanya
         */
        if (!$product) {
            $response->getBody()->write(json_encode(['error' => 'Product not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode(['data' => $product]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public  function update(Request $request, Response $response, $args)
    {
        /** Mendapatkan ID produk dari argumen yang dikirimkan oleh user */
        $id = $args['id'];

        /** Cek content-type yang dikirimkan */
        $contentType = $request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === false) {

            /** Jika Content-Type bukan JSON, kembalikan error berikut */
            $response->getBody()->write(json_encode(['error' => 'Invalid content type, expected application/json']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }


        $data = json_decode($request->getBody(), true);

          /** Validasi kelengkapan data */
        if (empty($data['name']) || empty($data['description']) || empty($data['price'])) {

            /** Jika data tidak lengkap, kembalikan error */
            $response->getBody()->write(json_encode(['error' => 'Incomplete data ']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(302);
        }

        /** Menyiapkan query untuk proses update data */
        $query = "UPDATE products SET name = :name, description = :description, price = :price WHERE id = :id";
        $stmt = $this->database->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindParam('description', $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(':price', $data['price'], PDO::PARAM_INT);

        try {
            $stmt->execute();
            $response->getBody()->write(json_encode(['message' => 'Update data complete ']));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['error' => 'Error updating data ' . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function destroy(Request $request,Response $response,$args)
    {
        /** 
         * Mendapatkan id produk dari argumen yang dikirimkan saat endpoint
         * di akses oleh user
         */
        $id = $args['id'];

        /** 
         * Menyiapkan query SQL untuk menghapus produk
         * Menyiapkan object yang berisi query untuk di eksekusi,
         * menambahkan bindParam id untuk mencegah SQL injection
         */
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $this->database->prepare($query);
        $stmt->bindParam(':id',$id,PDO::PARAM_INT);

        try {
            /** Menjalankan query untuk hapus data produk */
            $stmt->execute();
            /**
             * Jika proses hapus data berhasil
             * Tampilkan response dan pesan dalam format JSON
             */
            $response->getBody()->write(json_encode(['message' => "Data deleted"]));
            return $response->withHeader('Content-Type','application/json');
        } catch (PDOException $e) {
            /**
             * Jika proses hapus data gagal
             * Tampilkan response dan pesan error dalam format JSON
             */
            $response->getBody()->write(json_encode(['error' => "Error Delete data".$e->getMessage()]));
            return $response->withHeader('Content-Type','application/json')->withStatus(500);
        }
    }
}
