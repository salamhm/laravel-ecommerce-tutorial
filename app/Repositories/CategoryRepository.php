<?php

namespace App\Repositories;

use App\Contracts\CategoryContract;
use App\Models\Category;
use App\Traits\UploadAble;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;

class CategoryRepository extends BaseRepository implements CategoryContract
{
    use UploadAble;

    public function __construct(Category $model)
    {
        parent::__construct($model);
        $this->model = $model;
    }

    public function listCategories(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    public function findCategoryById(int $id)
    {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException($e);
        }
    }

    public function createCategory(array $params)
    {
        try {
            $collection = collect($params);

            $image = null;

            if ($collection->has('image') && $params['image'] instanceof UploadedFile) {
                $image = $this->uploadOne($params['image'], 'categories');
            }

            $featured = $collection->has('featured') ? 1 : 0;

            $menu = $collection->has('menu') ? 1 : 0;

            $merge = $collection->merge(compact('image', 'featured', 'menu'));

            $category = new Category($merge->all());

            $category->save();

            return $category;
        } catch (QueryException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function updateCategory(array $params)
    {
        $category = $this->findCategoryById($params['id']);
        $collection = collect($params)->except('_token');
        $image = null;
        if ($collection->has('image') && $params['image'] instanceof UploadedFile) {
            if ($category->image != null) {
                $this->deleteOne($category->image);
            }

            $image = $this->uploadOne($params['image'], 'categories');
        }

        $featured = $collection->has('featured') ? 1 : 0;

        $menu = $collection->has('menu') ? 1 : 0;

        $merge = $collection->merge(compact('image', 'featured', 'menu'));

        $category->update($merge->all());

        return $category;
    }

    public function deleteCategory($id)
    {
        $category = $this->findCategoryById($id);

        if ($category->image != null) {
            $this->deleteOne($category->image);
        }

        $category->delete();

        return $category;
    }
    
}
