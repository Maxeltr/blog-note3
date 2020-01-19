<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmFile\Mapper;

use MxmUser\Model\UserInterface;
use MxmFile\Model\FileInterface;

interface MapperInterface
{
    /**
     * @param MxmFile\Model\File $file
     *
     * @return Array
     *
     * @throw DataBaseErrorException
     */
    public function insertFile($file);

    /**
     * @return Laminas\Paginator\Paginator
     */
    public function findAllFiles();

    /**
     * @param UserInterface $user
     *
     * @return Laminas\Paginator\Paginator
     */
    public function findAllFilesByOwner(UserInterface $user = null);

    /**
     * @param String $fileId
     *
     * @return MxmFile\Model\File
     * @throw RecordNotFoundException
     */
    public function findFileById($fileId);

    /**
     * @param MxmFile\Model\FileInterface $file
     *
     * @return Laminas\Http\Response
     *
     * @throw RuntimeException
     */
    public function downloadFile(FileInterface $file);

    /**
     * @param MxmFile\Model\FileInterface $file
     *
     * @return bool
     */
    public function deleteFile(FileInterface $file);

    /**
     * @param Paginator|Array $files
     *
     * @return int
     *
     *@throw InvalidArgumentException
     */
    public function deleteFiles($files);
}