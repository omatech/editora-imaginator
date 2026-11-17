<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use League\Glide\Filesystem\FileNotFoundException;
use Omatech\Editora\Imaginator\EditoraImageExtractor;
use Omatech\Editora\Imaginator\InvalidHashException;
use Orchestra\Testbench\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ImageIdentityTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
    }
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('omp_values', function (Blueprint $t): void { $t->integer('id'); $t->string('text_val')->nullable(); });
        DB::table('omp_values')->insert(['id' => 7, 'text_val' => 'uploads/à.jpg']);
    }
    public function test_a_matching_stored_identity_resolves_and_query_syntax_is_not_an_id(): void
    {
        $extractor = new EditoraImageExtractor();
        self::assertSame('uploads/à.jpg', $extractor->extract(md5('uploads/à.jpg').'_7'));
        $this->expectException(FileNotFoundException::class);
        $extractor->extract(md5('uploads/à.jpg').'_7 OR 1=1');
    }
    public function test_a_replaced_image_rejects_the_previous_hash(): void
    {
        DB::table('omp_values')->where('id', 7)->update(['text_val' => 'uploads/replaced.jpg']);
        $this->expectException(InvalidHashException::class);
        (new EditoraImageExtractor())->extract(md5('uploads/à.jpg').'_7');
    }
    public function test_a_removed_value_is_a_404(): void
    {
        $this->expectException(NotFoundHttpException::class);
        (new EditoraImageExtractor())->extract(str_repeat('a', 32).'_999');
    }
}
