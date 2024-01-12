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

    public function index()
    {
        //
    }

    public function store(Request $request,Response $response)
    {
        /** Validasi Content-Type header yang dikirim */
        $contentType = $request->getHeaderLine('Content-Type');
        if (strpos($contentType,'application/json') === false) {

            /** Jika Content-Type bukan JSON, kembalikan error berikut */
            $response->getBody()->write(json_encode(['error' => 'Invalid content type, expected application/json']));
            return $response->withHeader('Content-Type','application/json')->withStatus(400);
        }

        /**
         * Mendapatkan data produk dari request body dan men-decode JSON
         */
        $data = json_decode($request->getBody(),true);

        /** Validasi kelengkapan data */
        if (empty($data['name']) || empty($data['description']) || empty($data['price'])) {

            /** Jika data tidak lengkap, kembalikan error */
            $response->getBody()->write(json_encode(['error' => 'Incomplete data ']));
            return $response->withHeader('Content-Type','application/json')->withStatus(302);
        }

        /** Menyiapkan query SQL untuk menyimpan produk */
        $query = "INSERT INTO products (name,description,price) VALUES (:name,:description,:price)";
        $stmt = $this->database->prepare($query);
        
        /**
         * Mengaitkan nilai parameter :name,:description, dan :price
         * dalam query SQL dengan variabel $data
         */
        $stmt->bindParam(':name',$data['name'],PDO::PARAM_STR);
        $stmt->bindParam(':description',$data['description'],PDO::PARAM_STR);
        $stmt->bindParam(':price',$data['price'],PDO::PARAM_INT);

        /**
         * Mengeksekusi query dan menangani kesalahan yang mungkin terjadi
         */
        try {
            $stmt->execute();

            /** Jika berhasil, kembalikan pesan sukses */
            $response->getBody()->write(json_encode(['message' => 'Product added']));
            return $response->withHeader('Content-Type','application/json')->withStatus(200);
        } catch (PDOException $e) {

            /** Jika tidak, kembalikan pesan error */
            $response->getBody()->write(json_encode(['error' => 'Error stored product : '. $e->getMessage()]));
            return $response->withHeader('Content-Type','application/json')->withStatus(500);
        }
    }
}
