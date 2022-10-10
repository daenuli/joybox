<?php

namespace App\Services;
use Carbon\Carbon;

class OpenLibrary {
    protected $json_filename = 'books.json';
    protected $open_library_json = 'https://openlibrary.org/subjects/programming.json';
    protected $refresh_ttl = 10; // in minutes
    protected $book_json;

    public function __construct()
    {
        $this->write_local_json();
        $this->fetch_local_json();
        $this->refresh_local_json();
    }

    protected function write_local_json()
    {
        if (!file_exists($this->json_filename)) {
            $this->fetch_openlibrary_json();
        }
    }

    protected function refresh_local_json()
    {
        $local_json = $this->book_json;
        if (Carbon::parse($local_json->created_at)->diffInMinutes(Carbon::now()) > $this->refresh_ttl) {
            $this->fetch_openlibrary_json();
        }
    }

    protected function fetch_openlibrary_json()
    {
        return file_put_contents("books.json", json_encode([
            'created_at' => Carbon::now(),
            'data' => json_encode(json_decode(file_get_contents($this->open_library_json))->works)
        ]));
    }

    protected function fetch_local_json()
    {
        $data = json_decode(file_get_contents($this->json_filename));
        $this->book_json = $data;
    }

    public function book_by_id($id)
    {
        $content = $this->book_json;
        $book_list = collect(json_decode($content->data));
        $data = $book_list->where('cover_id', $id)->map(function ($qry) {
            return [
                'cover_id' => $qry->cover_id,
                'title' => $qry->title,
                'edition' => $qry->edition_count, 
                'authors' => isset($qry->authors) ? collect($qry->authors)->map(function ($ath) {
                    return $ath->name;
                })->values()->all() : '',
                'isbn' => isset($qry->availability) ? $qry->availability->isbn : '',
            ];
        });
        return $data->values()->all();
    }

    public function get_book_json()
    {
        return $this->book_json;
    }
}