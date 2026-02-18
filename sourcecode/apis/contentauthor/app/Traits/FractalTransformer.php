<?php

namespace App\Traits;

use App\Transformers\Serializers\ArraySerializer;
use Illuminate\Http\Response;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\TransformerAbstract;

trait FractalTransformer
{
    /** @var Manager */
    protected $fractalManager;

    protected $arraySerializer;

    private $fractalInclude = [];

    private function init()
    {
        $this->fractalManager = app(Manager::class);
        $this->arraySerializer = app(ArraySerializer::class);
    }

    protected function setFractalManager(Manager $manager)
    {
        $this->fractalManager = $manager;
        return $this;
    }

    protected function setArraySerializer(ArraySerializer $serializer)
    {
        $this->arraySerializer = $serializer;
        return $this;
    }

    protected function addIncludeParse($include)
    {
        $includeArray = explode(",", $include);
        foreach ($includeArray as $item) {
            $this->fractalInclude[] = $item;
        }
    }

    /**
     * @return array
     */
    protected function buildItem($item, TransformerAbstract $transformer)
    {
        return $this->buildResource(new Item($item, $transformer));
    }

    /**
     * Create the response for a resource.
     *
     * @return array
     */
    protected function buildResource(ResourceAbstract $resource)
    {
        return $this->getManager()->createData($resource)->toArray();
    }

    /**
     * @return Manager
     */
    private function getManager()
    {
        if (is_null($this->fractalManager)) {
            $this->init();
        }
        return $this->fractalManager
            ->setSerializer($this->arraySerializer)
            ->parseIncludes($this->fractalInclude);
    }

    /**
     * @return array
     */
    protected function buildCollection($collection, TransformerAbstract $transformer)
    {
        return $this->buildResource(new Collection($collection, $transformer));
    }

    /**
     * Create the response for an item.
     *
     * @param  int $status
     * @return Response
     */
    protected function buildItemResponse($item, TransformerAbstract $transformer, $status = 200, array $headers = [])
    {
        $resource = new Item($item, $transformer);

        return $this->buildResourceResponse($resource, $status, $headers);
    }

    /**
     * Create the response for a resource.
     *
     * @param  int $status
     * @return Response
     */
    protected function buildResourceResponse(ResourceAbstract $resource, $status = 200, array $headers = [])
    {
        $manager = $this->getManager();

        return response()->json(
            $manager->createData($resource)->toArray(),
            $status,
            $headers,
        );
    }

    /**
     * Create the response for a collection.
     *
     * @param  int $status
     * @return Response
     */
    protected function buildCollectionResponse($collection, TransformerAbstract $transformer, $status = 200, array $headers = [])
    {
        $resource = new Collection($collection, $transformer);

        return $this->buildResourceResponse($resource, $status, $headers);
    }
}
