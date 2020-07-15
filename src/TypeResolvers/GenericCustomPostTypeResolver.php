<?php

declare(strict_types=1);

namespace PoP\GenericCustomPosts\TypeResolvers;

use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\CustomPosts\TypeResolvers\CustomPostTypeResolver;

class GenericCustomPostTypeResolver extends CustomPostTypeResolver
{
    public const NAME = 'GenericCustomPost';

    public function getTypeName(): string
    {
        return self::NAME;
    }

    public function getSchemaTypeDescription(): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        return $translationAPI->__('Any custom post, with or without its own type for the schema', 'customposts');
    }
}
