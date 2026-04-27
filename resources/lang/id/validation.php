<?php
return [
    'required'  => 'Kolom :attribute wajib diisi.',
    'string'    => 'Kolom :attribute harus berupa teks.',
    'unique'    => ':attribute sudah digunakan.',
    'exists'    => ':attribute tidak valid.',
    'min'       => ['numeric' => ':attribute minimal :min.', 'string' => ':attribute minimal :min karakter.'],
    'max'       => ['numeric' => ':attribute maksimal :max.', 'string' => ':attribute maksimal :max karakter.'],
    'numeric'   => ':attribute harus berupa angka.',
    'integer'   => ':attribute harus berupa bilangan bulat.',
    'boolean'   => ':attribute harus true atau false.',
    'date'      => ':attribute harus berupa tanggal yang valid.',
    'email'     => ':attribute harus berupa alamat email yang valid.',
    'in'        => ':attribute tidak valid.',
    'confirmed' => ':attribute konfirmasi tidak sesuai.',
    'after_or_equal' => ':attribute harus tanggal setelah atau sama dengan :date.',
    'attributes' => [],
];
