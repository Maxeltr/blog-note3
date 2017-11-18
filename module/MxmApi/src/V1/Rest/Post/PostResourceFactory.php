<?php
namespace MxmApi\V1\Rest\Post;

use MxmBlog\Mapper\MapperInterface as PostMapperInterface;

class PostResourceFactory
{
    public function __invoke($services)
    {
        $mapper = $services->get(PostMapperInterface::class);

        return new PostResource($mapper);
    }
}
