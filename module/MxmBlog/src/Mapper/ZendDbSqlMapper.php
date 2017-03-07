<?php

/* 
 * The MIT License
 *
 * Copyright 2016 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmBlog\Mapper;
 
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Filter\StaticFilter;
use Zend\Config\Config;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Hydrator\HydratorInterface;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Validator\StaticValidator;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Tag\ItemList;
use MxmBlog\Exception\RecordNotFoundBlogException;
use MxmBlog\Exception\InvalidArgumentBlogException;
use MxmBlog\Exception\DataBaseErrorBlogException;
use MxmBlog\Model\TagInterface;
use MxmBlog\Service\DateTimeInterface;
use MxmBlog\Model\PostInterface;
use MxmBlog\Model\CategoryInterface;

class ZendDbSqlMapper implements MapperInterface
{
    /**
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected $dbAdapter;

    /**
     * @var \Zend\Hydrator\HydratorInterface
     */
    protected $postHydrator;
    
    /**
     * @var Blog\Hydrator\Tag\TagHydrator
     */
    protected $tagHydrator;
    
    /**
     * @var \Zend\Hydrator\HydratorInterface
     */
    protected $classMethodsHydrator;
    
    /**
     * @var Blog\Hydrator\Tag\TagHydrator
     */
    protected $createdHydrator;

    /**
     * @var \Blog\Model\PostInterface
     */
    protected $postPrototype;
    
    /**
     * @var \Blog\Model\CategoryInterface
     */
    protected $categoryPrototype;
    
    /**
     * @var \Blog\Model\TagInterface
     */
    protected $tagPrototype;
    
    /**
     * @var Zend\Config\Config;
     */
    protected $config;
    
    /**
     * @param AdapterInterface $dbAdapter
     * @param HydratorInterface $postHydrator
     * @param PostInterface $postPrototype
     * @param CategoryInterface $categoryPrototype
     * @param TagInterface $tagPrototype
     * @param Config $config
     */
    public function __construct(
        AdapterInterface $dbAdapter,
        HydratorInterface $postHydrator,
        HydratorInterface $tagHydrator,
        HydratorInterface $classMethodsHydrator,
        PostInterface $postPrototype,
        CategoryInterface $categoryPrototype,
        TagInterface $tagPrototype,
        Config $config
    ) {
        $this->dbAdapter = $dbAdapter;
        $this->postHydrator = $postHydrator;
        $this->tagHydrator = $tagHydrator;
        $this->classMethodsHydrator = $classMethodsHydrator;
        $this->postPrototype = $postPrototype;
        $this->categoryPrototype = $categoryPrototype;
        $this->tagPrototype = $tagPrototype;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function findPostById($id, $hideUnpublished = true)
    {
        
        $parameters['where']['id'] = $id;
        
        if($hideUnpublished === true) {
            $parameters['where']['isPublished'] = true;
        }

        $select = $this->createPostSelectQuery($parameters);

        return $this->createObject($select, $this->postHydrator, $this->postPrototype);
    }
    
    /**
     * 
     * @param Select $select
     * @param HydratorInterface $hydrator
     * @param type $objectPrototype
     * 
     * @return Object
     * 
     * @throws InvalidArgumentBlogException
     * @throws RecordNotFoundBlogException
     */
    private function createObject(Select $select, HydratorInterface $hydrator, $objectPrototype)
    {
        if(!is_object($objectPrototype)) {
            throw new InvalidArgumentBlogException("ZendDbSqlMapper. createObject. No object param given.");
        }
        
        $sql = new Sql($this->dbAdapter);
        $stmt = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if($result instanceof ResultInterface && $result->isQueryResult() && $result->getAffectedRows()) {
            return $hydrator->hydrate($result->current(), $objectPrototype);
        }

        throw new RecordNotFoundBlogException("ZendDbSqlMapper. createObject. Record with given ID not found.");
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPostsByName($name, $hideUnpublished = true)
    {
        $parameters = array(
            'like' => array(
                'title' => $name
            ),
            'group' => array(
                'id'
            ),
            'order' => array(
                'id' => 'DESC'
            )
        );
        
        if($hideUnpublished === true) {
            $parameters['where']['isPublished'] = true;
        }
        
        $select = $this->createPostSelectQuery($parameters);

        return $this->createPaginator($select, $this->postHydrator, $this->postPrototype);
        
    }
    
    /**
     * {@inheritDoc}
     */
    public function findAllPosts($hideUnpublished = true)
    {
        $parameters = array(
            'group' => array(
                'id'
            ),
            'order' => array(
                'id' => 'DESC'
            )
        );

        if($hideUnpublished === true) {
            $parameters['where']['isPublished'] = true;
        }
        
        $select = $this->createPostSelectQuery($parameters);

        return $this->createPaginator($select, $this->postHydrator, $this->postPrototype);
    }

   /**
    * @param Select $select
    * @param HydratorInterface $hydrator
    * @param object $objectPrototype
    * 
    * @return Paginator
    * 
    * @throws InvalidArgumentBlogException
    */
    private function createPaginator(Select $select, HydratorInterface $hydrator, $objectPrototype)
    {
        if(!is_object($objectPrototype)) {
            throw new InvalidArgumentBlogException("ZendDbSqlMapper. createPaginator. No object param given.");
        }
        
        $resultSetPrototype = new HydratingResultSet($hydrator, $objectPrototype);
        
        // Create a new pagination adapter object:
        $paginatorAdapter = new DbSelect(
            // our configured select object:
            $select,
            // the adapter to run it against:
            $this->dbAdapter,
            // the result set to hydrate:
            $resultSetPrototype
        );
        $paginator = new Paginator($paginatorAdapter);
        
        return $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function insertPost(PostInterface $postObject)
    {
        $data = $this->postHydrator->extract($postObject);

        $data['categoryId'] = $data['category']['id'];
        unset($data['category']);
        unset($data['id']); // Neither Insert nor Update needs the ID in the array
        unset($data['tags']);   // Теги сохраняются в двух других таблицах
        
        $action = new Insert('articles');
        $action->values($data);

        $this->saveInDb($postObject, $action);
        $this->saveTagsAndTagPostAssociations($postObject);
        
        return $postObject;
    }

    /**
     * {@inheritDoc}
     */
    public function updatePost(PostInterface $postObject)
    {
        $data = $this->postHydrator->extract($postObject);

        $data['categoryId'] = $data['category']['id'];
        unset($data['category']);
        unset($data['id']);
        unset($data['tags']);

        $action = new Update('articles');	
        $action->set($data);
        $action->where(array('id = ?' => $postObject->getId()));
        
        $this->saveInDb($postObject, $action);
        $this->saveTagsAndTagPostAssociations($postObject);
        
        return $postObject;
    }

    /**
     * {@inheritDoc}
     */
    public function deletePost(PostInterface $postObject)
    {
        $action = new Delete('articles');
        $action->where(array('id = ?' => $postObject->getId()));

        $sql = new Sql($this->dbAdapter);
        $stmt = $sql->prepareStatementForSqlObject($action);
        $result = $stmt->execute();

        return (bool)$result->getAffectedRows();
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPostsByCategory(CategoryInterface $category, $hideUnpublished = true)
    {
        $parameters = array(
            'where' => array(
                'categoryId' => $category->getId()
            ),
            'group' => array(
                'id'
            ),
            'order' => array(
                'id' => 'DESC'
            )
        );
        
        if($hideUnpublished === true) {
            $parameters['where']['isPublished'] = true;
        }
        
        $select = $this->createPostSelectQuery($parameters);

        return $this->createPaginator($select, $this->postHydrator, $this->postPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPostsByTag(TagInterface $tag, $hideUnpublished = true)
    {
        $select = new Select('articles'); 	
        $select->where(array('articles_tags.article_id = articles.id'));

        $select->join(
            'category', 'articles.categoryId = category.id',
            array(), 
            $select::JOIN_LEFT
        );

        $select->join(
            'tags', 
            new Expression('tags.title = ?' , $tag->getTitle()),
            array(), 
            $select::JOIN_LEFT
        );

        $select->join(
            'articles_tags', 'tags.id = articles_tags.tag_id',
            array(), 
            $select::JOIN_LEFT
        );
        
        $subSelect = new Select('articles_tags');
        $subSelect->columns(array(
            'tag_id',
            'tag_weight' => new Expression('COUNT(`article_id`)')
        ));
        $subSelect->group('tag_id');

        $select->join(
            array('tag_weights' => $subSelect), 
            'articles_tags.tag_id = tag_weights.tag_id',
            array(), 
            $select::JOIN_LEFT
        );

        $select->columns(array(
            'id' => 'id',
            'title' => 'title',
            'text' => 'text',
            'summary' => 'summary',
            'categoryId' => 'categoryId',
            'author' => 'author',
            'created' => 'created',
            'updated' => 'updated',
            'published' => 'published',
            'isPublished' => 'isPublished',
            'version' => 'version',
            'categoryTitle' => new Expression('category.title'),    //из группирующих функций возвращается объект с null свойствами и в других методах та же фигня (findPostById findAllPosts
            'tagIds' => new Expression('GROUP_CONCAT(tags.id)'),
            'tagTitles' => new Expression('GROUP_CONCAT(tags.title)'),
            'tagWeights' => new Expression('GROUP_CONCAT(tag_weights.tag_weight)')
        ));
        $select->group('id');

        return $this->createPaginator($select, $this->postHydrator, $this->postPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPostsByUser($user, $hideUnpublished = true)
    {
        $parameters = array(
            'where' => array(
                'author' => $user
            ),
            'group' => array(
                'id'
            ),
            'order' => array(
                'id' => 'DESC'
            )
        );
        
        if($hideUnpublished === true) {
            $parameters['where']['isPublished'] = true;
        }
        
        $select = $this->createPostSelectQuery($parameters);

        return $this->createPaginator($select, $this->postHydrator, $this->postPrototype);
    }
        
    /**
     * {@inheritDoc}
     */
    public function findPostsByCreateDate(DateTimeInterface $since = null, DateTimeInterface $to = null, $hideUnpublished = true)
    {
        $parameters = array(
            'group' => array(
                'id'
            ),
            'order' => array(
                'id' => 'DESC'
            )
        );
                
        if($since !== null) {
            $parameters['greaterThanOrEqualTo'] = array(
                'created' => $since->format($this->config->dateTime->dateTimeFormat)
            );
        }
        
        if($to !== null) {
            $parameters['lessThanOrEqualTo'] = array(
                'created' => $to->format($this->config->dateTime->dateTimeFormat)
            );
        }
        
        if($hideUnpublished === true) {
            $parameters['where']['isPublished'] = true;
        }
        
        $select = $this->createPostSelectQuery($parameters);

        return $this->createPaginator($select, $this->postHydrator, $this->postPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPostsByUpdateDate(DateTimeInterface $since = null, DateTimeInterface $to = null, $hideUnpublished = true)
    {
        $parameters = array(
            'group' => array(
                'id'
            ),
            'order' => array(
                'id' => 'DESC'
            )
        );
                
        if($since !== null) {
            $parameters['greaterThanOrEqualTo'] = array(
                'updated' => $since->format($this->config->dateTime->dateTimeFormat)
            );
        }
        
        if($to !== null) {
            $parameters['lessThanOrEqualTo'] = array(
                'updated' => $to->format($this->config->dateTime->dateTimeFormat)
            );
        }
        
        if($hideUnpublished === true) {
            $parameters['where']['isPublished'] = true;
        }
        
        $select = $this->createPostSelectQuery($parameters);

        return $this->createPaginator($select, $this->postHydrator, $this->postPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPostsByPublishDate(\DateTimeInterface $since = null, \DateTimeInterface $to = null)
    {
        $parameters = array(
            'group' => array(
                'id'
            ),
            'order' => array(
                'id' => 'DESC'
            )
        );
                
        if($since !== null) {
            $parameters['greaterThanOrEqualTo'] = array(
                'published' => $since->format($this->config->dateTime->dateTimeFormat)
            );
        }
        
        if($to !== null) {
            $parameters['lessThanOrEqualTo'] = array(
                'published' => $to->format($this->config->dateTime->dateTimeFormat)
            );
        }
        
        $parameters['where']['isPublished'] = true;
        
        $select = $this->createPostSelectQuery($parameters);

        return $this->createPaginator($select, $this->postHydrator, $this->postPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPublishDates($group = 'year', $limit = null)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select('articles');
        
        $group = (string) $group;
        switch ($group) {
            case "day":
                $select->columns(array(
                    'year' => new Expression('YEAR(published)'),
                    'month' => new Expression('MONTH(published)'),
                    'day' => new Expression('DAY(published)'),
                    'total' => new Expression('COUNT(*)')
                ));
                $select->group('day');
                $select->group('month');
                $select->group('year');
                break;
            
            case "month":
                $select->columns(array(
                    'year' => new Expression('YEAR(published)'),
                    'month' => new Expression('MONTH(published)'),
                    'total' => new Expression('COUNT(*)')
                ));
                $select->group('month');
                $select->group('year');
                break;
            
            case "year":
                $select->columns(array(
                    'year' => new Expression('YEAR(published)'),
                    'total' => new Expression('COUNT(*)')
                ));
                $select->group('year');
                break;
            
            default:
                throw new InvalidArgumentBlogException("ZendDbSqlMapper. findPublishDates. Invalid param: group.");
        }
        
        $select->where(['articles.isPublished' => true]);
        
        $select->order('published DESC');
        
        if (StaticValidator::execute($limit, 'Digits')) {
            $select->limit($limit);
        }
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        
        return $resultSet;
    }
    
    /**
     * {@inheritDoc}
     */
    public function findCategoryById($id)
    {
        $select = new Select('category');
        $select->where(array('category.id = ?' => $id));
        $select->columns(array(
            'id' => 'id',
            'title' => 'title',
            'description' => 'description',
        ));
        
        return $this->createObject($select, $this->classMethodsHydrator, $this->categoryPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findCategoriesByName($title)
    {
        $select = new Select('category');
        $select->where(array('category.title = ?' => $title));
        $select->columns(array(
            'id' => 'id',
            'title' => 'title',
            'description' => 'description',
        ));
        $select->order('title');
                
        return $this->createPaginator($select, $this->classMethodsHydrator, $this->categoryPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findAllCategories()
    {
        $select = new Select('category'); 	
        $select->columns(array(
            'id' => 'id',
            'title' => 'title',
            'description' => 'description',
            
        ));
        $select->group('id');
        $select->order('title');

        return $this->createPaginator($select, $this->classMethodsHydrator, $this->categoryPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function insertCategory(CategoryInterface $category)
    {
        $data = $this->classMethodsHydrator->extract($category);
        
        $action = new Insert('category');	
        $action->values($data);
        
        return $this->saveInDb($category, $action);
    }
    
    /**
     * {@inheritDoc}
     */
    public function updateCategory(CategoryInterface $category)
    {
        $data = $this->classMethodsHydrator->extract($category);
        
        $action = new Update('category');
        $action->set($data);
        $action->where(array('id = ?' => $category->getId()));
        
        return $this->saveInDb($category, $action);
    }
    
    /**
     * {@inheritDoc}
     */
    public function deleteCategory(CategoryInterface $category)
    {
        $action = new Delete('category');
        $action->where(array('id = ?' => $category->getId()));

        $sql = new Sql($this->dbAdapter);
        $stmt = $sql->prepareStatementForSqlObject($action);
        $result = $stmt->execute();
        
        return (bool)$result->getAffectedRows();
    }
    
    /**
     * {@inheritDoc}
     */
    public function findTagById($id)
    {
        $select = new Select('articles_tags');
        $select->join('tags', 'articles_tags.tag_id = tags.id', array(), $select::JOIN_LEFT);
        $select->where(array('tags.id = ?' => $id));
        $select->columns(array(
            'id' => new Expression('tags.id'),
            'title' => new Expression('tags.title'),
            'weight' => new Expression('COUNT(articles_tags.tag_id)'),
        ));

        return $this->createObject($select, $this->tagHydrator, $this->tagPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findTagsByName($title)
    {
        $select = new Select('tags');
        $select->where(array('tags.title = ?' => $title));
        
        $subSelect = new Select('articles_tags');
        $subSelect->columns(array(
            'tag_id',
            'tag_weight' => new Expression('COUNT(*)')
        ));
        $subSelect->group('tag_id');
        
        $select->join(
            'articles_tags', 'tags.id = articles_tags.tag_id',
            array(), 
            $select::JOIN_LEFT
        );
        
        $select->join(
            array('tag_weights' => $subSelect), 
            'articles_tags.tag_id = tag_weights.tag_id',
            array(), 
            $select::JOIN_LEFT
        );
        
        $select->columns(array(
            'id' => 'id',
            'title' => 'title',
            'weight' => new Expression('tag_weights.tag_weight')
        ));
        $select->group('id');
        
        return $this->createPaginator($select, $this->tagHydrator, $this->tagPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findAllTags()
    {    
        $select = new Select('tags');
        
        $subSelect = new Select('articles_tags');
        $subSelect->columns(array(
            'tag_id',
            'tag_weight' => new Expression('COUNT(*)')
        ));
        $subSelect->group('tag_id');
        
        $select->join(
            'articles_tags', 'tags.id = articles_tags.tag_id',
            array(), 
            $select::JOIN_LEFT
        );
        
        $select->join(
            array('tag_weights' => $subSelect), 
            'articles_tags.tag_id = tag_weights.tag_id',
            array(), 
            $select::JOIN_LEFT
        );
        
        $select->columns(array(
            'id' => 'id',
            'title' => 'title',
            'weight' => new Expression('tag_weights.tag_weight')
        ));
        $select->group('id');

        return $this->createPaginator($select, $this->tagHydrator, $this->tagPrototype);
    }
    
    /**
     * {@inheritDoc}
     */
    public function insertTag(TagInterface $tag)
    {
        $data = $this->tagHydrator->extract($tag);

        unset($data['weight']);

        $action = new Insert('tags');
        $action->values(array(
            'title' => $data['title'],
        ));

        return $this->saveInDb($tag, $action);
    }

    /**
     * {@inheritDoc}
     */
    public function updateTag(TagInterface $tag)
    {
        $data = $this->tagHydrator->extract($tag);
        unset($data['weight']);

        $action = new Update('tags');
        $action->set($data);
        $action->where(array('id = ?' => $tag->getId()));

        return $this->saveInDb($tag, $action);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteTag(TagInterface $tag)
    {
        $action = new Delete('tags');
        $action->where(array('id = ?' => $tag->getId()));

        $sql = new Sql($this->dbAdapter);
        $stmt = $sql->prepareStatementForSqlObject($action);
        $result = $stmt->execute();
        
        $rows = (bool)$result->getAffectedRows();

        if (!$rows) {
            return false;
        }
        $this->deleteTagAssociationWithPosts($tag);

        return true;
    }

    /**
     * Удаляет связи тега с постами (article_id - tag_id)
     * @param TagInterface $object
     *
     * @return bool
     */
    private function deleteTagAssociationWithPosts(TagInterface $object)
    {
        $sql = new Sql($this->dbAdapter);
        $delete = $sql->delete('articles_tags');
        $delete->where(array(
            'tag_id' => $object->getId(),
        ));
        $statement = $sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        return (bool)$result->getAffectedRows();
    }

    /**
     * Сохранить теги и связи тег-статья в базе.
     *
     * @param PostInterface $post
     *
     * @return $this
     * @throw InvalidArgumentBlogException
     */
    private function saveTagsAndTagPostAssociations(PostInterface $post)
    {
        $tags = $post->getTags();
        if(!$tags instanceof ItemList) {
            throw new InvalidArgumentBlogException("ZendDbSqlMapper. saveTagsAndTagPostAssociations. No ItemList in Post given.");
        }

        $this->deletePostAssociationWithTags($post);

        $repetition = array();
        for($offset=0, $countTags = count($tags); $offset < $countTags; $offset++) {
            $tag = $tags->offsetGet($offset);
            $tagTitle = $tag->getTitle();
            if (!in_array($tagTitle, $repetition) && !empty($tagTitle)) {
                $repetition[] = $tagTitle;
                $tagId = $this->getTagIdByName($tagTitle);
                if ($tagId === null) {
                    $this->insertTag($tag);
                    $this->saveTagPostAssociation($tag->getId(), $post->getId());
                } else {
                    $this->saveTagPostAssociation($tagId, $post->getId());
                    $tagInDb = $this->findTagById($tagId);
                    $tag->setId($tagInDb->getId());
                    $tag->setTitle($tagInDb->getTitle());
                    $tag->setWeight($tagInDb->getWeight());
                }

                continue;
            }
            $tags->offsetUnset($offset);
        }

        return $this;
    }

    /**
     * Удаляет связи поста с тегами (article_id - tag_id)
     * @param PostInterface $object
     *
     * @return bool
     */
    private function deletePostAssociationWithTags(PostInterface $object)
    {
        $sql = new Sql($this->dbAdapter);
        $delete = $sql->delete('articles_tags');
        $delete->where(array(
            'article_id' => $object->getId(), 
        ));
        $statement = $sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        return (bool)$result->getAffectedRows();
    }

    /**
     * Получить id тега по названию. Для проверки есть ли тег в базе
     *
     * @param string $title title of tag
     *
     * @return string Id
     * @throw InvalidArgumentBlogException
     */
    private function getTagIdByName($title)
    {
        if(!is_string($title) or empty($title)) {
            throw new InvalidArgumentBlogException("ZendDbSqlMapper. getTagIdByName. Title is not string or empty.");
        }

        $select = new Select('tags');
        $select->where(array('tags.title = ?' => $title));

        $select->columns(array(
            'id' => 'id',
        ));
        $select->quantifier('DISTINCT');

        $sql = new Sql($this->dbAdapter);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if($result instanceof ResultInterface && $result->isQueryResult() && $result->getAffectedRows()) {
            $row = $result->current();

            return $row['id'];
        }

        return null;
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
    private function saveTagPostAssociation($tagId, $postId)
    {
        $tagId = StaticFilter::execute($tagId, 'Digits');
        $postId = StaticFilter::execute($postId, 'Digits');
        
        if(empty($tagId) or empty($postId)) {
            throw new InvalidArgumentBlogException("ZendDbSqlMapper. saveTagPostAssociation. Empty param given: tag ID:{$tagId} or post ID:{$postId}.");
        }

        $sql = new Sql($this->dbAdapter);
        $insert = $sql->insert('articles_tags');
        $insert->values(array(
            'article_id' => $postId,
            'tag_id' => $tagId,
        ));
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if ($result instanceof ResultInterface) {
            $lastInsertId = $result->getGeneratedValue();
            if ($lastInsertId) {
                return $lastInsertId;
            }

            return false;    
        }

        throw new DataBaseErrorBlogException("Database error. ZendDbSqlMapper. saveTagPostAssociation. No result returned.");
    }

    /**
     * Выполнить insert или update. Получить id и установить его в переданный объект.
     * @param object $object
     * @param PreparableSqlInterface $action
     * 
     * @return object
     * @throws InvalidArgumentBlogException
     * @throws DataBaseErrorBlogException
     */
    private function saveInDb($object, PreparableSqlInterface $action)
    {
        if(!is_object($object)) {
            throw new InvalidArgumentBlogException("ZendDbSqlMapper. saveInDb. No object param given.");
        }

        $sql = new Sql($this->dbAdapter);
        $stmt = $sql->prepareStatementForSqlObject($action);
        $result = $stmt->execute();

        if ($result instanceof ResultInterface) {
            $newId = $result->getGeneratedValue();
            if ($newId) {
                if(is_callable([$object, 'setId'])) {
                    $object->setId($newId);
                }
            }

            return $object;
        }

        throw new DataBaseErrorBlogException("Database error. ZendDbSqlMapper. saveInDb. No ResultInterface returned.");
    }
    
    /**
     * Сформировать запрос select.
     * @param array $parameters
     * 
     * @return Zend\Db\Sql\Select
     */
    private function createPostSelectQuery($parameters)
    {
        $select = new Select('articles'); 	

        if(array_key_exists('where', $parameters) && is_array($parameters['where'])) {
            foreach($parameters['where'] as $column => $value) {
                $select->where(array('articles.' . $column . ' = ?' => $value));
            }
        }
        
        if(array_key_exists('like', $parameters) && is_array($parameters['like'])) {
            foreach($parameters['like'] as $column => $value) {
                $select->where->like('articles.' . $column, '%' . $value . '%');
            }
        }
        
        if(array_key_exists('lessThanOrEqualTo', $parameters) && is_array($parameters['lessThanOrEqualTo'])) {
            foreach($parameters['lessThanOrEqualTo'] as $column => $value) {
                $select->where->lessThanOrEqualTo('articles.' . $column, $value);
            }
        }
        
        if(array_key_exists('greaterThanOrEqualTo', $parameters) && is_array($parameters['greaterThanOrEqualTo'])) {
            foreach($parameters['greaterThanOrEqualTo'] as $column => $value) {
                $select->where->greaterThanOrEqualTo('articles.' . $column, $value);
            }
        }

        $select->join(
            'category', 'articles.categoryId = category.id',
            array(), 
            $select::JOIN_LEFT
        );

        $select->join(
            'articles_tags', 'articles.id = articles_tags.article_id',
            array(), 
            $select::JOIN_LEFT
        );

        $select->join(
            'tags', 'articles_tags.tag_id = tags.id',
            array(), 
            $select::JOIN_LEFT
        );

        $subSelect = new Select('articles_tags');
        $subSelect->columns(array(
            'tag_id',
            'tag_weight' => new Expression('COUNT(`article_id`)')
        ));
        $subSelect->group('tag_id');

        $select->join(
            array('tag_weights' => $subSelect), 
            'articles_tags.tag_id = tag_weights.tag_id',
            array(), 
            $select::JOIN_LEFT
        );

        $select->columns(array(
            'id' => 'id',
            'title' => 'title',
            'text' => 'text',
            'summary' => 'summary',
            'categoryId' => 'categoryId',
            'author' => 'author',
            'created' => 'created',
            'updated' => 'updated',
            'published' => 'published',
            'isPublished' => 'isPublished',
            'version' => 'version',
            'categoryTitle' => new Expression('category.title'),
            'tagIds' => new Expression('GROUP_CONCAT(tags.id)'),
            'tagTitles' => new Expression('GROUP_CONCAT(tags.title)'),
            'tagWeights' => new Expression('GROUP_CONCAT(tag_weights.tag_weight)')
        ));
        
        if (array_key_exists('group', $parameters) && is_array($parameters['group'])) {
            foreach($parameters['group'] as $column) {
                $select->group('articles.' . $column);
            }
        } else {
            $select->group('articles.id');
        }
        
        if (array_key_exists('order', $parameters) && is_array($parameters['order'])) {
            foreach($parameters['order'] as $column => $value) {
                $select->order('articles.' . $column . ' ' . $value);
            }
        }
        
        return $select;
    }
}

    
       