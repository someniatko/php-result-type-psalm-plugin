<?php

declare(strict_types=1);

namespace Someniatko\ResultTypePsalmPlugin;

use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type;
use Psalm\Type\Union;
use Someniatko\ResultType\Result;
use Someniatko\ResultType\ResultInterface;

final class ResultAllTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [ Result::class ];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Union
    {
        $source = $event->getSource();
        $callArgs = $event->getCallArgs();
        $methodNameLowercase = $event->getMethodNameLowercase();

        if ($methodNameLowercase !== 'all') {
            return null;
        }

        if (!$source instanceof StatementsAnalyzer) {
            return null;
        }

        if (count($callArgs) !== 1) {
            return null;
        }

        $argTypeUnion = $source->getNodeTypeProvider()->getType($callArgs[0]->value);

        if ($argTypeUnion === null) {
            return null;
        }

        $argType = $argTypeUnion->getSingleAtomic();

        if (! $argType instanceof Type\Atomic\TKeyedArray) {
            return null;
        }

        if (! $argType->is_list) {
            return null;
        }

        foreach ($argType->properties as $prop) {
            $type = $prop->getSingleAtomic();
            if (! $type instanceof Type\Atomic\TGenericObject) {
                return null;
            }
            if (count($type->type_params) !== 2) {
                return null;
            }
        }

        $successTypes = self::extractResultNestedTypes(0, $argType->properties);
        $errorTypes = self::extractResultNestedTypes(1, $argType->properties);

        return new Union([ new Type\Atomic\TGenericObject(
            ResultInterface::class,
            [
                new Union([ new Type\Atomic\TKeyedArray($successTypes, null, null, true) ]),
                Type::getList(Type::combineUnionTypeArray(array_values($errorTypes), $source->getCodebase())),
            ]
        ) ]);
    }

    /**
     * @param non-empty-array<string|int, Union> $arrayProps
     * @return non-empty-array<string|int, Union>
     */
    private static function extractResultNestedTypes(int $templateIndex, array $arrayProps): array
    {
        return array_map(
            function (Union $prop) use ($templateIndex): Union {
                $type = $prop->getSingleAtomic();
                assert($type instanceof Type\Atomic\TGenericObject);
                assert(count($type->type_params) === 2);

                return $type->type_params[$templateIndex];
            },
            $arrayProps,
        );
    }
}
