<?php

namespace DoctrineEncryptBundle\DoctrineEncryptBundle\Mapping;

use DoctrineEncryptBundle\DoctrineEncryptBundle\Configuration\Annotation;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * @author Flavien Bucheton <leflav45@gmail.com>
 *
 * @internal
 */
final class AttributeAnnotationReader implements Reader
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var AttributeReader
     */
    private $attributeReader;

    public function __construct(AttributeReader $attributeReader, Reader $annotationReader, string $cacheDir)
    {
        $this->attributeReader  = $attributeReader;
        $annotationsCache       = new FilesystemAdapter('', 0, $cacheDir.'/doctrine_encrypt');
        $this->annotationReader = new PsrCachedReader($annotationReader, $annotationsCache);
    }

    /**
     * @return Annotation[]
     */
    public function getClassAnnotations(\ReflectionClass $class): array
    {
        $annotations = $this->attributeReader->getClassAnnotations($class);

        if ([] !== $annotations) {
            return $annotations;
        }

        return $this->annotationReader->getClassAnnotations($class);
    }

    /**
     * @param class-string<T> $annotationName the name of the annotation
     *
     * @return T|null the Annotation or NULL, if the requested annotation does not exist
     *
     * @template T
     */
    public function getClassAnnotation(\ReflectionClass $class, $annotationName)
    {
        $annotation = $this->attributeReader->getClassAnnotation($class, $annotationName);

        if (null !== $annotation) {
            return $annotation;
        }

        return $this->annotationReader->getClassAnnotation($class, $annotationName);
    }

    /**
     * @return Annotation[]
     */
    public function getPropertyAnnotations(\ReflectionProperty $property): array
    {
        $propertyAnnotations = $this->attributeReader->getPropertyAnnotations($property);

        if ([] !== $propertyAnnotations) {
            return $propertyAnnotations;
        }

        return $this->annotationReader->getPropertyAnnotations($property);
    }

    /**
     * @param class-string<T> $annotationName the name of the annotation
     *
     * @return T|null the Annotation or NULL, if the requested annotation does not exist
     *
     * @template T
     */
    public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName)
    {
        $annotation = $this->attributeReader->getPropertyAnnotation($property, $annotationName);

        if (null !== $annotation) {
            return $annotation;
        }

        return $this->annotationReader->getPropertyAnnotation($property, $annotationName);
    }

    public function getMethodAnnotations(\ReflectionMethod $method): array
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getMethodAnnotation(\ReflectionMethod $method, $annotationName): mixed
    {
        throw new \BadMethodCallException('Not implemented');
    }
}
