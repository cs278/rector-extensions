<?php

declare(strict_types=1);

namespace Cs278\RectorExtensions\PhpUnit;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;

final class DataProviderReturnTypeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    public const HELLO_ATTRIBUTE = 'rector_comment';

    /** @var array<non-empty-string,ClassMethod> */
    private array $methods = [];

    /** @var array<non-empty-string,list<non-empty-string>> */
    private array $dataProviders = [];

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Class_) {
            if ($node->extends?->name === TestCase::class) {
                return null;
            }

            return self::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof ClassMethod) {
            $this->methods[$node->name->name] = $node;

            foreach (self::findAttributesByName($node, DataProvider::class) as $attribute) {
                if ($attribute->args[0]->value instanceof String_ && $attribute->args[0]->value->value !== '') {
                    $this->dataProviders[$attribute->args[0]->value->value] ??= [];
                    $this->dataProviders[$attribute->args[0]->value->value][] = $node->name->name;
                }
            }

            return self::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Class_) {
            if ($node->extends?->name === TestCase::class) {
                foreach ($this->dataProviders as $dataProvider => $testMethods) {
                    $this->methods[$dataProvider]->setAttribute('phpunit_test_methods', $testMethods);
                }

                $this->methods = [];
                $this->dataProviders = [];
            }

            \assert($this->methods === []);
            \assert($this->dataProviders === []);
        }

        return null;
    }

    public function afterTraverse(array $nodes)
    {
        return null;
    }

    /** @return iterable<int,Attribute> */
    private static function findAttributesByName(FunctionLike $node, string $name): iterable
    {
        foreach ($node->getAttrGroups() as $group) {
            foreach ($group->attrs as $attribute) {
                if ($attribute->name->toString() === $name) {
                    yield $attribute;
                }
            }
        }
    }
}
