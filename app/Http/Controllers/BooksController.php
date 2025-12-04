<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Book;
use App\Http\Resources\BookResource;

class BooksController extends Controller
{
    /**
     * ==========1===========
     * GET - Tampilkan daftar semua buku
     */
    public function index()
    {
        $books = Book::all();
        
        return response()->json([
            'message' => 'Books retrieved successfully',
            'data' => BookResource::collection($books)
        ], 200);
    }

    /**
     * ==========2===========
     * POST - Simpan buku baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'published_year' => 'required|integer|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'published_year' => $request->published_year,
            'is_available' => true,
        ]);

        return response()->json([
            'message' => 'Book created successfully',
            'data' => new BookResource($book)
        ], 201);
    }

    /**
     * =========3===========
     * GET - Tampilkan detail buku tertentu
     */
    public function show(string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }
        
        return response()->json([
            'message' => 'Book retrieved successfully',
            'data' => new BookResource($book)
        ], 200);
    }

    /**
     * =========4===========
     * PUT - Perbarui data buku tertentu
     */
    public function update(Request $request, string $id)
    {
        $book = Book::find($id);
        
        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'published_year' => 'sometimes|required|integer|digits:4',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $book->update($request->only(['title', 'author', 'published_year']));

        return response()->json([
            'message' => 'Book updated successfully',
            'data' => new BookResource($book)
        ], 200);
    }

    /**
     * =========5===========
     * DELETE - Hapus buku tertentu
     */
    public function destroy(string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully'
        ], 200);
    }

    /**
     * =========6===========
     * PUT - Pinjam atau kembalikan buku
     */
    public function borrowReturn(string $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }

        // Toggle is_available
        $book->is_available = !$book->is_available;
        $book->save();

        $action = $book->is_available ? 'returned' : 'borrowed';

        return response()->json([
            'message' => "Book {$action} successfully",
            'data' => new BookResource($book)
        ], 200);
    }
}