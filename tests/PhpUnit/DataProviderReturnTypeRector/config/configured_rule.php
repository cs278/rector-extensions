<?php

declare(strict_types=1);

use Cs278\RectorExtensions\PhpUnit\DataProviderReturnTypeVisitor;
use Rector\Config\RectorConfig;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;

return RectorConfig::configure()
    ->registerService(DataProviderReturnTypeVisitor::class, null, ScopeResolverNodeVisitorInterface::class)
    ->withRules([
        \Cs278\RectorExtensions\PhpUnit\DataProviderReturnTypeRector::class,
    ]);
