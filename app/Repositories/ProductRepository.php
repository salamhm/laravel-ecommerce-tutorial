<?php

namespace App\Repositories;

use App\Contracts\ProductContract;
use App\Models\Product;
use App\Traits\UploadAble;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class ProductRepository extends BaseRepository implements ProductContract
{
    use UploadAble;

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function listProducts(string $order = 'id', string $sort = 'asc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    public function findProductById(int $id)
    {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ModelNotFoundException($e);
        }
    }

    public function createProduct(array $params)
    {
        try {
            $collection = collect($params);
            $featured = $collection->has('featured') ? 1 : 0;
            $status = $collection->has('status') ? 1 : 0;

            $merge = $collection->merge(compact('featured', 'status'));
            $product = new Product($merge->all());
            $product->save();

            if ($collection->has('categories')) {
                $product->categories()->sync($params['categories']);
            }

            return $product;
        } catch (QueryException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function updateProduct(array $params)
    {
        $product = $this->findProductById($params['id']);

        try {
            $collection = collect($params)->except('_token');
            $featured = $collection->has('featured') ? 1 : 0;
            $status = $collection->has('status') ? 1 : 0;

            $merge = $collection->merge(compact('featured', 'status'));
            $product->update($merge->all());

            if ($collection->has('categories')) {
                $product->categories()->sync($params['categories']);
            }

            return $product;
        } catch (QueryException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function deleteProduct(int $id)
    {
        $product = $this->findProductById($id);
        $product->delete();
        return $product;
    }
}
