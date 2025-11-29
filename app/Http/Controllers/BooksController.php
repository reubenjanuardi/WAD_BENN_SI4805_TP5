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
        return BookResource::collection($books);
    }

    /**
     * ==========2===========
     * Simpan buku baru ke dalam penyimpanan.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'published_year' => 'required|digits:4|integer',
            'is_available'   => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()
            ], 422);
        }

        $book = Book::create($request->all());

        return new BookResource($book);
    }

    /**
     * =========3===========
     * Tampilkan detail buku tertentu.
     */
    public function show(string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
            ], 404);
        }

        return new BookResource($book);
    }

    /**
     * =========4===========
     * Fungsi untuk memperbarui data buku tertentu
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'sometimes|string|max:255',
            'author'         => 'sometimes|string|max:255',
            'published_year' => 'sometimes|digits:4|integer',
            'is_available'   => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()
            ], 422);
        }

        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
            ], 404);
        }

        $book->update($request->all());

        return new BookResource($book);
    }

    /**
     * =========5===========
     * Hapus buku tertentu dari penyimpanan.
     */
    public function destroy(string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
            ], 404);
        }

        $book->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Book deleted successfully'
        ]);
    }

    /**
     * =========6===========
     * Ubah status ketersediaan buku (ubah field is_available)
     */
    public function borrowReturn(string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
            ], 404);
        }

        // Toggle: jika 1 (available), jadi 0 (borrowed). Jika 0 → 1.
        $book->is_available = !$book->is_available;
        $book->save();

        return response()->json([
            'status'  => true,
            'message' => $book->is_available ? 'Book returned' : 'Book borrowed',
            'data'    => new BookResource($book)
        ]);
    }
}
