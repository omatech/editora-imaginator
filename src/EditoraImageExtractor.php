<?php

namespace Omatech\Editora\Imaginator;

use Illuminate\Support\Facades\DB;
use League\Glide\Filesystem\FileNotFoundException;
use Omatech\Imaginator\Contracts\GetImageInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EditoraImageExtractor implements GetImageInterface
{
    /** Serves "<md5 of the stored path>_<omp_values id>"; any other id is left to the next loader. */
    public function extract(string $hash) : string
    {
        if (!preg_match('/^([0-9a-f]{32})_(\d+)$/', $hash, $match)) {
            throw new FileNotFoundException('The image belongs to another source.');
        }

        $path = DB::table('omp_values')->where('id', $match[2])->value('text_val');
        if (!is_string($path) || $path === '') {
            throw new NotFoundHttpException('The image value no longer exists.');
        }
        if (!hash_equals(md5($path), $match[1])) {
            throw new InvalidHashException('The hash is invalid');
        }

        return $path;
    }
}
