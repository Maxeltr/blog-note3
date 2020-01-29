<?php

/*
 * The MIT License
 *
 * Copyright 2020 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MxmBlog\Model;

use Laminas\Db\TableGateway\TableGateway;
use MxmBlog\Exception\RecordNotFoundBlogException;
use MxmBlog\Exception\InvalidArgumentBlogException;
use Laminas\Validator\StaticValidator;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbTableGateway;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\ValidatorInterface;
use Laminas\Filter\StaticFilter;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Tag\ItemList;

class PostManager implements PostManagerInterface {

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $postTableGateway;

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $tagPostTableGateway;

    /**
     * @var Laminas\Validator\Db\NoRecordExists
     */
    protected $noRecordExists;

    /**
     * @param TableGateway $tableGateway
     */
    public function __construct(
            TableGateway $postTableGateway,
            TableGateway $tagPostTableGateway,
            ValidatorInterface $noRecordExists,
            HydratorInterface $postHydrator
    ) {
        $this->postTableGateway = $postTableGateway;
        $this->tagPostTableGateway = $tagPostTableGateway;
        $this->noRecordExists = $noRecordExists;
        $this->postHydrator = $postHydrator;
    }

    /**
     * {@see PostManagerInterface}
     */
    public function insertPost(PostInterface $post) {
        $postArray = $this->postHydrator->extract($post);
        unset($postArray['tags']);
        $this->postTableGateway->insert($postArray);
        $newId = $this->postTableGateway->getLastInsertValue();
        if (!$newId) {
            throw new DataBaseErrorBlogException("Insert operation failed");
        }

        $this->saveTagsAndTagPostAssociations($newId, $post->getTags());

        $resultSet = $this->postTableGateway->select(['id' => $newId]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorBlogException("Insert operation failed or did not result in new row.");
        }

        return $resultSet->current();
    }

    /**
     * Удаляет связи поста с тегами (article_id - tag_id)
     * @param string $id
     *
     * @return Affected Rows
     */
    private function deletePostAssociationWithTags($id) {
        return $this->tagPostTableGateway->$delete(['article_id' => $id]);
    }

    /**
     * Сохранить теги и связи тег-статья в базе.
     *
     * @param ItemList $tags
     *
     * @return $this
     */
    private function saveTagsAndTagPostAssociations(string $postId, ItemList $tags) {
        for ($offset = 0, $countTags = count($tags); $offset < $countTags; $offset++) {
            $tagId = $tags->offsetGet($offset)->getId();
            if ($this->noRecordExists->isValid($tagId)) {
                continue;
            }
            $this->saveTagPostAssociation($tagId, $postId);
        }

        return $this;
    }

    /**
     * Сохранить одну связь "пост-тег" в таблицу articles_tags.
     * @param int|string $tagId tag id
     * @param int|string $postId post id
     *
     * @return $lastInsertId
     * @throws InvalidArgumentBlogException
     * @throws DataBaseErrorBlogException
     */
    private function saveTagPostAssociation($tagId, $postId) {
        $tagId = StaticFilter::execute($tagId, 'Digits');
        $postId = StaticFilter::execute($postId, 'Digits');

        if (empty($tagId) or empty($postId)) {
            throw new InvalidArgumentBlogException("Cannot save tag-post association: Empty param given: tag ID:{$tagId} or post ID:{$postId}");
        }

        $this->tagPostTableGateway->insert([
            'article_id' => $postId,
            'tag_id' => $tagId,
        ]);

        return $this->tagPostTableGateway->getLastInsertValue();
    }

}
