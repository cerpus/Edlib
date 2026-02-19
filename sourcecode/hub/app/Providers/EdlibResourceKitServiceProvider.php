<?php

declare(strict_types=1);

namespace App\Providers;

use App\EdlibResourceKit\Internal\NullCredentialStore;
use App\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibContentItemMapper;
use App\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibContentItemsSerializer;
use App\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItemSerializer;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemMapper;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemMapperInterface;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapper;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapperInterface;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ImageMapper;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ImageMapperInterface;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\PlacementAdviceMapper;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\PlacementAdviceMapperInterface;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemPlacementSerializer;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemPlacementSerializerInterface;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemSerializer;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemSerializerInterface;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializer;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializerInterface;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\FileItemSerializer;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\FileItemSerializerInterface;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ImageSerializer;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ImageSerializerInterface;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\LtiLinkItemSerializer;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\LtiLinkItemSerializerInterface;
use App\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use App\EdlibResourceKit\Oauth1\MemoizedValidator;
use App\EdlibResourceKit\Oauth1\Signer;
use App\EdlibResourceKit\Oauth1\SignerInterface;
use App\EdlibResourceKit\Oauth1\Validator;
use App\EdlibResourceKit\Oauth1\ValidatorInterface;
use Illuminate\Support\ServiceProvider;

class EdlibResourceKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // LTI 1.1 mappers
        $this->app->singleton(ContentItemsMapperInterface::class, ContentItemsMapper::class);
        $this->app->singleton(ContentItemMapperInterface::class, $this->createContentItemMapper(...));
        $this->app->singleton(ImageMapperInterface::class, ImageMapper::class);
        $this->app->singleton(PlacementAdviceMapperInterface::class, PlacementAdviceMapper::class);

        // LTI 1.1 serializers
        $this->app->singleton(ContentItemsSerializerInterface::class, $this->createContentItemsSerializer(...));
        $this->app->singleton(ContentItemPlacementSerializerInterface::class, ContentItemPlacementSerializer::class);
        $this->app->singleton(ContentItemSerializerInterface::class, ContentItemSerializer::class);
        $this->app->singleton(FileItemSerializerInterface::class, FileItemSerializer::class);
        $this->app->singleton(ImageSerializerInterface::class, ImageSerializer::class);
        $this->app->singleton(LtiLinkItemSerializerInterface::class, $this->createLtiLinkItemSerializer(...));

        // OAuth 1.0 services
        $this->app->singleton(SignerInterface::class, Signer::class);
        $this->app->singleton(ValidatorInterface::class, MemoizedValidator::class);
        $this->app->singletonIf(CredentialStoreInterface::class, NullCredentialStore::class);
        $this->app->when(MemoizedValidator::class)
            ->needs(ValidatorInterface::class)
            ->give(Validator::class);

        $this->app->when(EdlibContentItemMapper::class)
            ->needs(ContentItemMapperInterface::class)
            ->give(ContentItemMapper::class);

        $this->app->when(EdlibContentItemsSerializer::class)
            ->needs(ContentItemsSerializerInterface::class)
            ->give(ContentItemsSerializer::class);

        $this->app->when(EdlibLtiLinkItemSerializer::class)
            ->needs(LtiLinkItemSerializerInterface::class)
            ->give(LtiLinkItemSerializer::class);
    }

    private function createContentItemMapper(): ContentItemMapperInterface
    {
        if ($this->app->make('config')->get('edlib-resource-kit.use-edlib-extensions')) {
            return $this->app->make(EdlibContentItemMapper::class);
        }

        return $this->app->make(ContentItemMapper::class);
    }

    private function createContentItemsSerializer(): ContentItemsSerializerInterface
    {
        if ($this->app->make('config')->get('edlib-resource-kit.use-edlib-extensions')) {
            return $this->app->make(EdlibContentItemsSerializer::class);
        }

        return $this->app->make(ContentItemsSerializer::class);
    }

    private function createLtiLinkItemSerializer(): LtiLinkItemSerializerInterface
    {
        if ($this->app->make('config')->get('edlib-resource-kit.use-edlib-extensions')) {
            return $this->app->make(EdlibLtiLinkItemSerializer::class);
        }

        return $this->app->make(LtiLinkItemSerializer::class);
    }
}
