<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\TagFactory;
use phpDocumentor\Reflection\DocBlockFactory as PhpDocumentorDocBlockFactory;
use phpDocumentor\Reflection\TypeResolver;
use PhpParser\Node;
use SplObjectStorage;

final class DocBlockFactory
{
    /**
     * @var PhpDocumentorDocBlockFactory
     */
    private $phpDocumentorDocBlockFactory;

    /**
     * @var DocBlock[]|SplObjectStorage
     */
    private $docBlocksPerNode;

    public function __construct(
        TagFactory $tagFactory,
        PhpDocumentorDocBlockFactory $phpDocumentorDocBlockFactory,
        DescriptionFactory $descriptionFactory,
        TypeResolver $typeResolver
    ) {
        $this->docBlocksPerNode = new SplObjectStorage;
        $this->phpDocumentorDocBlockFactory = $phpDocumentorDocBlockFactory;

        // cannot move to services.yml, because it would cause circular dependency exception
        $tagFactory->addService($descriptionFactory);
        $tagFactory->addService($typeResolver);
    }

    public function createFromNode(Node $node): DocBlock
    {
        if (isset($this->docBlocksPerNode[$node])) {
            return $this->docBlocksPerNode[$node];
        }

        $docBlockContent = $node->getDocComment() ? $node->getDocComment()->getText() : ' ';

        $docBlock = $this->phpDocumentorDocBlockFactory->create($docBlockContent);

        return $this->docBlocksPerNode[$node] = $docBlock;
    }
}