<?php


namespace Ambta\DoctrineEncryptBundle\Tests\Functional;


use Ambta\DoctrineEncryptBundle\Encryptors\EncryptorInterface;
use Ambta\DoctrineEncryptBundle\Encryptors\HaliteEncryptor;
use Ambta\DoctrineEncryptBundle\Mapping\AttributeAnnotationReader;
use Ambta\DoctrineEncryptBundle\Mapping\AttributeReader;
use Ambta\DoctrineEncryptBundle\Subscribers\DoctrineEncryptSubscriber;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\InvalidArgumentHelper;

abstract class AbstractFunctionalTestCase extends TestCase
{
    /** @var DoctrineEncryptSubscriber */
    protected $subscriber;
    /** @var EncryptorInterface */
    protected $encryptor;
    /** @var false|string */
    protected $dbFile;
    /** @var EntityManager */
    protected $entityManager;
    /** @var DebugStack */
    protected $sqlLoggerStack;

    abstract protected function getEncryptor(): EncryptorInterface;

    public function setUp(): void
    {
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $isDevMode                 = true;
        $proxyDir                  = null;
        $cache                     = null;
        $useSimpleAnnotationReader = false;

        $config = Setup::createAnnotationMetadataConfiguration(
            array(__DIR__ . "/fixtures/Entity"),
            $isDevMode,
            $proxyDir,
            $cache,
            $useSimpleAnnotationReader
        );

        // database configuration parameters
        $this->dbFile = tempnam(sys_get_temp_dir(), 'amb_db');
        $conn = array(
            'driver' => 'pdo_sqlite',
            'path'   => $this->dbFile,
        );

        // obtaining the entity manager
        $this->entityManager = EntityManager::create($conn, $config);

        $schemaTool = new SchemaTool($this->entityManager);
        $classes    = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        $this->sqlLoggerStack = new DebugStack();
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger($this->sqlLoggerStack);

        $this->encryptor = $this->getEncryptor();
        $annotationCacheDirectory = __DIR__.'/cache';
        $this->createNewCacheDirectory ($annotationCacheDirectory);
        $annotationReader = new AttributeAnnotationReader (new AttributeReader(), new AnnotationReader(), $annotationCacheDirectory);
        $this->subscriber = new DoctrineEncryptSubscriber($annotationReader, $this->encryptor);
        $this->entityManager->getEventManager()->addEventSubscriber($this->subscriber);

        error_reporting(E_ALL);
    }

    public function tearDown(): void
    {
        $this->entityManager->getConnection()->close();
        unlink($this->dbFile);
    }

    protected function createNewCacheDirectory (string $annotationCacheDirectory): void
    {
        $this->recurseRmdir ($annotationCacheDirectory);
        mkdir ($annotationCacheDirectory);
    }

    protected function recurseRmdir ($dir): bool
    {
        $contents = scandir($dir);
        if (is_array ($contents))
        {
            $files = array_diff ($contents, array('.','..'));
            foreach ($files as $file)
            {
                (is_dir("$dir/$file") && !is_link("$dir/$file")) ? $this->recurseRmdir("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }

        return false;
    }

    protected function getLatestInsertQuery(): ?array
    {
        $insertQueries = array_values(array_filter($this->sqlLoggerStack->queries, static function ($queryData) {
            return stripos($queryData['sql'], 'INSERT ') === 0;
        }));

        return current(array_reverse($insertQueries)) ?: null;
    }

    protected function getLatestUpdateQuery(): ?array
    {
        $updateQueries = array_values(array_filter($this->sqlLoggerStack->queries,static function ($queryData) {
            return stripos($queryData['sql'], 'UPDATE ') === 0;
        }));

        return current(array_reverse($updateQueries)) ?: null;
    }

    /**
     * Using the SQL Logger Stack this method retrieves the current query count executed in this test.
     */
    protected function getCurrentQueryCount(): int
    {
        return count($this->sqlLoggerStack->queries);
    }

    protected function resetQueryStack(): void
    {
        $this->sqlLoggerStack->queries = [];
    }

    /**
     * Asserts that a string starts with a given prefix.
     *
     * @param string $stringn
     * @param string $string
     * @param string $message
     */
    public function assertStringDoesNotContain($needle, $string, $ignoreCase = false, $message = ''): void
    {
        $this->assertIsString($needle,$message);
        $this->assertIsString($string,$message);
        $this->assertIsBool($ignoreCase,$message);

        $constraint = new LogicalNot(new StringContains(
            $needle,
            $ignoreCase
        ));

        static::assertThat($string, $constraint, $message);
    }
}
