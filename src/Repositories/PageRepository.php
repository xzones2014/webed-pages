<?php namespace WebEd\Base\Pages\Repositories;

use WebEd\Base\Caching\Services\Traits\Cacheable;
use WebEd\Base\Pages\Models\Page;
use WebEd\Base\Repositories\Eloquent\EloquentBaseRepository;

use WebEd\Base\Caching\Services\Contracts\CacheableContract;
use WebEd\Base\Pages\Repositories\Contracts\PageRepositoryContract;

class PageRepository extends EloquentBaseRepository implements PageRepositoryContract, CacheableContract
{
    use Cacheable;

    /**
     * @param array $data
     * @return int
     */
    public function createPage(array $data)
    {
        return $this->create($data, true);
    }

    /**
     * @param Page|int $id
     * @param array $data
     * @return int
     */
    public function updatePage($id, array $data)
    {
        return $this->update($id, $data);
    }

    /**
     * @param int|array $ids
     * @param bool $force
     * @return bool
     */
    public function deletePage($ids, $force = false)
    {
        return $this->delete((array)$ids, $force);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getPages(array $params)
    {
        $params = array_merge([
            'condition' => [
                'status' => 'activated',
            ],
            'order_by' => [
                'order' => 'ASC',
                'created_at' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => 10,
                'current_paged' => 1
            ],
            'select' => ['*'],
        ], $params);

        $model = $this->model
            ->where($params['condition']);

        $model = $model->select($params['select']);

        foreach ($params['order_by'] as $column => $direction) {
            $model = $model->orderBy($column, $direction);
        }

        if ($params['take'] == 1) {
            return $model->first();
        }

        if ($params['take']) {
            return $model->take($params['take'])->get();
        }

        if ($params['paginate']['per_page']) {
            return $model->paginate($params['paginate']['per_page'], ['*'], 'page', $params['paginate']['current_paged']);
        }

        return $model->get();
    }
}
