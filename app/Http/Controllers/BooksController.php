<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Book;
use App\Http\Resources\BookResource;

class BooksController extends Controller
{
    /**
     * ==========1===========
     * Tampilkan daftar semua buku
     */
    public function index()
    {
        $books = Book::all();
        // Menambahkan pesan sukses untuk konsistensi
        return response()->json([
            'message' => 'Books retrieved successfully',
            'data' => BookResource::collection($books)
        ], Response::HTTP_OK);
    }

    /**
     * ==========2===========
     * Simpan buku baru ke dalam penyimpanan.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'author' => 'required|string',
            'published_year' => 'required|integer',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $book = Book::create($request->all());

        return response()->json([
            'message' => 'Book created successfully',
            'data' => new BookResource($book)
        ], Response::HTTP_CREATED); // Menggunakan 201 Created
    }

    /**
     * =========3===========
     * Tampilkan detail buku tertentu.
     */
    public function show(string $id)
    {
        $book = Book::findOrFail($id);
        return response()->json([
            'message' => 'Book retrieved successfully',
            'data' => new BookResource($book)
        ], Response::HTTP_OK);
    }

    /**
     * =========4===========
     * Fungsi untuk memperbarui data buku tertentu
     */
    public function update(Request $request, string $id)
    {
        $book = Book::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'author' => 'string',
            'published_year' => 'integer',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $book->update($request->all());

        return response()->json([
            'message' => 'Book updated successfully',
            'data' => new BookResource($book)
        ], Response::HTTP_OK);
    }

    /**
     * =========5===========
     * Hapus buku tertentu dari penyimpanan.
     */
    public function destroy(string $id)
    {
        $book = Book::findOrFail($id);
        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully'
        ], Response::HTTP_OK);
    }

    /**
     * =========6===========
     * Ubah status ketersediaan buku (ubah field is_available).
     * MENGGUNAKAN ROUTE MODEL BINDING: $book sekarang adalah object Book.
     */
    public function borrowReturn(Book $book) // Mengganti string $id menjadi Book $book
    {
        // Menyimpan status awal sebelum perubahan
        $wasAvailable = $book->is_available; 
        
        // Membalik status (0 menjadi 1, atau 1 menjadi 0)
        $book->is_available = !$wasAvailable;
        $book->save();

        // Menentukan pesan berdasarkan status baru
        $message = $book->is_available 
            ? 'Book returned successfully' // Jika sekarang 1 (Tersedia)
            : 'Book borrowed successfully'; // Jika sekarang 0 (Dipinjam)

        return response()->json([
            'message' => $message,
            'data' => new BookResource($book)
        ], Response::HTTP_OK);
    }
}