<?php

declare(strict_types=1);

namespace PoP\GenericCustomPosts\TypeDataLoaders;

use PoP\CustomPosts\TypeDataLoaders\AbstractCustomPostTypeDataLoader;
use PoP\CustomPosts\ModuleProcessors\CustomPostRelationalFieldDataloadModuleProcessor;

class GenericCustomPostTypeDataLoader extends AbstractCustomPostTypeDataLoader
{
    /**
     * Override the custom-post-types from the parent
     *
     * @param array $ids
     * @return array
     */
    public function getObjectQuery(array $ids): array
    {
        return array_merge(
            parent::getObjectQuery($ids),
            [
                'custom-post-types' => ['post', 'page'],
            ]
        );
    }
    // public function getFilterDataloadingModule(): ?array
    // {
    //     return [
    //         CustomPostRelationalFieldDataloadModuleProcessor::class,
    //         CustomPostRelationalFieldDataloadModuleProcessor::MODULE_DATALOAD_RELATIONALFIELDS_UNIONCUSTOMPOSTLIST
    //     ];
    // }
}
