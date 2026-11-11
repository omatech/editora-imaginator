<?php

namespace Omatech\Editora\Imaginator;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** A URL whose hash no longer matches the stored image: that image is not there any more, a 404. */
class InvalidHashException extends NotFoundHttpException
{

}
