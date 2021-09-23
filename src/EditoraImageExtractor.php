<?php

namespace Omatech\Editora\Imaginator;

use Illuminate\Support\Facades\DB;
use Omatech\Imaginator\Contracts\GetImageInterface;

class EditoraImageExtractor implements GetImageInterface
{
    public function extract(string $hash) : string
    {
        list($hash, $id) = explode('_', $hash);

        $path = DB::table('omp_values')->where('id', $id)->select('text_val')->first();
        if (!$path) {
            return null;
        }
        $path = $path->text_val;
        if ($hash !== md5($path)) {
            throw new InvalidHashException('The hash is invalid');
        }

        return $path;
    }
}
