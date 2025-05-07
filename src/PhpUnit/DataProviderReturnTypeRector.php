<?php

declare(strict_types=1);

namespace Cs278\RectorExtensions\PhpUnit;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use PHPStan\PhpDocParser\Ast\Type as PhpDocType;

final class DataProviderReturnTypeRector extends AbstractRector
{
    public function __construct(
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly PhpDocInfoPrinter $phpDocInfoPrinter,
    ) {}

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        \assert($scope->isInClass());

        if (!is_array($node->getAttribute('phpunit_test_methods'))) {
            return null;
        }

        $parameters = [];

        foreach ($node->getAttribute('phpunit_test_methods') as $testMethod) {
            \assert(\is_string($testMethod));

            foreach ($scope->getClassReflection()->getMethod($testMethod, $scope)->getVariants() as $variant) {
                foreach ($variant->getParameters() as $i => $parameter) {
                    // @todo Use type from PHPDoc if available.
                    $parameters[$i] = isset($parameters[$i])
                        ? TypeCombinator::union($parameter->getNativeType(), $parameters[$i])
                        : $parameter->getNativeType();
                }
            }
        }

        $returnType = new Type\Constant\ConstantArrayType(
            array_map(function (int $value) {
                return new Type\Constant\ConstantIntegerType($value);
            }, array_keys($parameters)),
            $parameters,
        );

        $docInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnDescription = $docInfo->getReturnTagValue()->description ?? '';
        $docInfo->removeByType(ReturnTagValueNode::class);
        $docInfo->addTagValueNode(new ReturnTagValueNode(new PhpDocType\GenericTypeNode(
            new PhpDocType\IdentifierTypeNode('iterable'),
            [
                new PhpDocType\IdentifierTypeNode('array-key'),
                $returnType->toPhpDocNode(),
            ],
        ), $returnDescription));

        $node->setDocComment(new Doc($this->phpDocInfoPrinter->printFormatPreserving($docInfo)));

        return $node;
    }
}
