<?php namespace WebEd\Base\Pages\Repositories\Contracts;

use WebEd\Base\Pages\Models\Page;

interface PageRepositoryContract
{
    /**
     * @param array $data
     * @return int
     */
    public function createPage(array $data);

    /**
     * @param Page|int $id
     * @param array $data
     * @return int
     */
    public function updatePage($id, array $data);

    /**
     * @param int|array $ids
     * @param bool $force
     * @return bool
     */
    public function deletePage($ids, $force = false);

    /**
     * @param array $params
     * @return mixed
     */
    public function getPages(array $params);
}
