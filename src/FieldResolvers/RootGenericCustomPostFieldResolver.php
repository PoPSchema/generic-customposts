<?php

declare(strict_types=1);

namespace PoP\GenericCustomPosts\FieldResolvers;

use PoP\CustomPosts\Types\Status;
use PoP\Engine\TypeResolvers\RootTypeResolver;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\Schema\TypeCastingHelpers;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\GenericCustomPosts\ComponentConfiguration;
use PoP\CustomPosts\Facades\CustomPostTypeAPIFacade;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\ComponentModel\FieldResolvers\AbstractQueryableFieldResolver;
use PoP\GenericCustomPosts\TypeResolvers\GenericCustomPostTypeResolver;
use PoP\GenericCustomPosts\ModuleProcessors\GenericCustomPostRelationalFieldDataloadModuleProcessor;

/**
 * Add fields to the Root for querying for generic custom posts
 *
 * @author Leonardo Losoviz <leo@getpop.org>
 */
class RootGenericCustomPostFieldResolver extends AbstractQueryableFieldResolver
{
    public static function getClassesToAttachTo(): array
    {
        return array(RootTypeResolver::class);
    }

    public static function getFieldNamesToResolve(): array
    {
        return [
            'genericCustomPost',
            'genericCustomPosts',
            'genericCustomPostCount',
        ];
    }

    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        $descriptions = [
            'genericCustomPost' => $translationAPI->__('Custom post with a specific ID', 'generic-customposts'),
            'genericCustomPosts' => $translationAPI->__('Custom posts', 'generic-customposts'),
            'genericCustomPostCount' => $translationAPI->__('Number of custom posts', 'generic-customposts'),
        ];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }

    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $types = [
            'genericCustomPost' => SchemaDefinition::TYPE_ID,
            'genericCustomPosts' => TypeCastingHelpers::makeArray(SchemaDefinition::TYPE_ID),
            'genericCustomPostCount' => SchemaDefinition::TYPE_INT,
        ];
        return $types[$fieldName] ?? parent::getSchemaFieldType($typeResolver, $fieldName);
    }

    public function isSchemaFieldResponseNonNullable(TypeResolverInterface $typeResolver, string $fieldName): bool
    {
        $nonNullableFieldNames = [
            'genericCustomPosts',
            'genericCustomPostCount',
        ];
        if (in_array($fieldName, $nonNullableFieldNames)) {
            return true;
        }
        return parent::isSchemaFieldResponseNonNullable($typeResolver, $fieldName);
    }

    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName): array
    {
        $schemaFieldArgs = parent::getSchemaFieldArgs($typeResolver, $fieldName);
        $translationAPI = TranslationAPIFacade::getInstance();
        switch ($fieldName) {
            case 'genericCustomPost':
                return array_merge(
                    $schemaFieldArgs,
                    [
                        [
                            SchemaDefinition::ARGNAME_NAME => 'id',
                            SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_ID,
                            SchemaDefinition::ARGNAME_DESCRIPTION => $translationAPI->__('The page ID', 'generic-customposts'),
                            SchemaDefinition::ARGNAME_MANDATORY => true,
                        ],
                    ]
                );
            case 'genericCustomPosts':
            case 'genericCustomPostCount':
                return array_merge(
                    $schemaFieldArgs,
                    $this->getFieldArgumentsSchemaDefinitions($typeResolver, $fieldName)
                );
        }
        return $schemaFieldArgs;
    }

    public function enableOrderedSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName): bool
    {
        switch ($fieldName) {
            case 'genericCustomPosts':
            case 'genericCustomPostCount':
                return false;
        }
        return parent::enableOrderedSchemaFieldArgs($typeResolver, $fieldName);
    }

    protected function getFieldDefaultFilterDataloadingModule(TypeResolverInterface $typeResolver, string $fieldName, array $fieldArgs = []): ?array
    {
        switch ($fieldName) {
            case 'genericCustomPostCount':
                return [
                    GenericCustomPostRelationalFieldDataloadModuleProcessor::class,
                    GenericCustomPostRelationalFieldDataloadModuleProcessor::MODULE_DATALOAD_RELATIONALFIELDS_GENERICCUSTOMPOSTCOUNT
                ];
        }
        return parent::getFieldDefaultFilterDataloadingModule($typeResolver, $fieldName, $fieldArgs);
    }

    protected function getQuery(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = []): array
    {
        $query = [
            'custom-post-types' => ComponentConfiguration::getGenericCustomPostTypes(),
            'custom-post-status' => [
                Status::PUBLISHED,
            ],
        ];
        switch ($fieldName) {
            case 'genericCustomPost':
                return array_merge(
                    $query,
                    [
                        'include' => [$fieldArgs['id']],
                    ]
                );
            case 'genericCustomPosts':
                return array_merge(
                    $query,
                    [
                        'limit' => ComponentConfiguration::getGenericCustomPostListDefaultLimit(),
                    ]
                );
            case 'genericCustomPostCount':
                return $query;
        }
        return [];
    }

    public function resolveValue(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = [], ?array $variables = null, ?array $expressions = null, array $options = [])
    {
        $customPostTypeAPI = CustomPostTypeAPIFacade::getInstance();
        switch ($fieldName) {
            case 'genericCustomPost':
                $query = $this->getQuery($typeResolver, $resultItem, $fieldName, $fieldArgs);
                $options = [
                    'return-type' => \POP_RETURNTYPE_IDS,
                ];
                if ($customPosts = $customPostTypeAPI->getCustomPosts($query, $options)) {
                    return $customPosts[0];
                }
                return null;
            case 'genericCustomPosts':
                $query = $this->getQuery($typeResolver, $resultItem, $fieldName, $fieldArgs);
                $options = [
                    'return-type' => \POP_RETURNTYPE_IDS,
                ];
                $this->addFilterDataloadQueryArgs($options, $typeResolver, $fieldName, $fieldArgs);
                return $customPostTypeAPI->getCustomPosts($query, $options);
            case 'genericCustomPostCount':
                $query = $this->getQuery($typeResolver, $resultItem, $fieldName, $fieldArgs);
                $options = [];
                $this->addFilterDataloadQueryArgs($options, $typeResolver, $fieldName, $fieldArgs);
                return $customPostTypeAPI->getCustomPostCount($query, $options);
        }

        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }

    public function resolveFieldTypeResolverClass(TypeResolverInterface $typeResolver, string $fieldName, array $fieldArgs = []): ?string
    {
        switch ($fieldName) {
            case 'genericCustomPost':
            case 'genericCustomPosts':
                return GenericCustomPostTypeResolver::class;
        }

        return parent::resolveFieldTypeResolverClass($typeResolver, $fieldName, $fieldArgs);
    }
}
